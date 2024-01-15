<script>
	$('button').button();

	$('#nonstockprice').priceFormat({
		prefix: '',
		thousandsSeparator: ''
});	

</script>
<?php

include '../config.php';
include '../functions/auth_func.php';

session_start();
$auth=check_auth();

$till=$_COOKIE['tillIdent'];
$tillsession=getTillSession($till);

$sku=urldecode($_REQUEST['sku']);
$colour=$_REQUEST['colour'];
$sizeindex=$_REQUEST['sizeindex'];
$orderno=$_SESSION['orderno'];
$action=$_REQUEST['action'];
$lineno=$_REQUEST['lineno'];
$barcode=$_REQUEST['barcode'];
$custDiscount=getCustomer($orderno);

if ($barcode==1)
{
	#derive variables for add
	$barcode=$_REQUEST['value'];
	
	$itemdetails=decodeBarcode($barcode);
	$sku=$itemdetails['sku'];
	$colour=$itemdetails['colour'];
	$sizeindex=$itemdetails['sizeindex'];
	$sizeindex=(int)$sizeindex;
}

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="price")
{
	echo "<h2>Enter Price</h2>";
	echo "<table><tr><td>Price</td><td><input type=text id=nonstockprice value= /></td></tr>";
	$urlsku=urlencode($_REQUEST['sku']);
	echo "<tr><td colspan=2 style=\"text-align:right;\"><button onclick=javascript:addItem('".$urlsku."','".$_REQUEST['colour']."','".$_REQUEST['sizeindex']."',1); >Save</button></td></tr>";
	echo "<script type=text/javascript>$('#nonstockprice').focus();$('#nonstockprice').val('0.00');</script>";
	exit();
}


if ($action=="add")
{
	if ($_SESSION['custref']=="")
	{
		#Assume Walkin
		$_SESSION['custref']=4;
	}
	if ($_SESSION['orderno']=="")
	{
		$orderno=createOrder($till, $_SESSION['custref']);
	}	
	$nextline=getOrderLinesCnt()+1;
	$itemsize=getItemSize($sizeindex, $sku);
	
	if ($itemsize=="")
	{
	    #Bad scan
	    echo "<script>alert('Bad Scan. Please retry. Error code ".$barcode."');</script>";
	    
	    exit();
	}
	
	#Set item prices, and then override if price is passed
	$itemprice=getItemPrice($sku, $colour);
	
	if ($_REQUEST['price']<>'')
	{
		$itemprice['price']=$_REQUEST['price'];
		//$itemprice['sale']=$_REQUEST['price'];
	}
	$grandtot=$itemprice['price'];
	
	if ($itemprice['vatable']==1)
	{
		$nettot=($itemprice['price']/($itemprice['rate']+100)*100);
		$vattot=$grandtot-$nettot;
	}
	else
	{
		$nettot=$itemprice['price'];
		$vattot=0;
		
	}
	
	if ($itemprice['sale']=="")
	{
		createRollEntry($_SESSION['custref'], $sku."-".$colour."-".$itemsize, 1, $itemprice['price'], 'S');
		$itemprice['sale']="NULL";
	}
	else
	{
		createRollEntry($_SESSION['custref'], $sku."-".$colour."-".$itemsize, 1, $itemprice['sale'], 'S');
	} 
	$actualtot=$itemprice['sale'];
	$actualnet=($itemprice['sale']/($itemprice['rate']+100)*100);
	$actualvat=$actualtot-$actualnet;
	if ($itemprice['costprice']=="")
	{
	    $itemprice['costprice']=0;
	}
	$sql_query="insert into orderdetail (transno, StockRef, colour, size, grandtot, nettot, vattot, lineno, qty, status, sizeindex, onsale, actualgrand, actualnet, actualvat, costprice) 
	values ($orderno,'".$sku."','".$colour."','".$itemsize."'
	,".$grandtot.",".$nettot.",".$vattot.",$nextline,1, 'N', $sizeindex,".$itemprice['onsale'].",".$actualtot.",".$actualnet.",".$actualvat.",".$itemprice['costprice'].")";

	$result=$db_conn->query($sql_query);
	$update=updateReadout();
}

if ($action=="remove")
{
	$sql_query="select StockRef, colour, size from orderdetail where transno =$orderno and lineno=$lineno";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$itemsize=getItemSize($result['size'], $result['StockRef']);
	createRollEntry($_SESSION['custref'], $result['StockRef']."-".$result['colour']."-".$itemsize, 1, '', 'R');
	$sql_query="delete from orderdetail where transno=$orderno and lineno=$lineno";
	$result=$db_conn->query($sql_query);
	$update=updateReadout();
	
	
}

if ($action=="appro")
{
	$onAppro=appro($lineno);
	updateReadout();
}

if ($action=="negate")
{
                $sql_query="select transno, StockRef, colour, size, sizeindex, qty, netTot, vatTot, grandTot, costprice from orderdetail where transno = $orderno and lineno =$lineno";
                $newrecords=$db_conn->query($sql_query);
                $newrecord=mysqli_fetch_array($newrecords);
                $nextline=getOrderLinesCnt();
                $sql_query="insert into orderdetail (transno, StockRef, colour, size, sizeindex, lineno, qty, status, netTot, vatTot, grandTot, costprice) values ($orderno,'".$newrecord['StockRef']."','".$newrecord['colour']
                                ."','".$newrecord['size']."',".$newrecord['sizeindex'].",".($nextline+1).",".($newrecord['qty']*-1).",'X',".$newrecord['netTot'].",".$newrecord['vatTot'].",".$newrecord['grandTot'].",".$newrecord['costprice'].")";
                $negrecord=$db_conn->query($sql_query);
                #Take original line off appro
                $sql_query="update orderdetail set status = 'X' where transno = $orderno and lineno = $lineno";
                $doit=$db_conn->query($sql_query);
		updateReadout();

}

if ($action=="void")
{	
		if ($_REQUEST['type']=="new")
		{
			$sql_query="select transno, StockRef, colour, size, sizeindex, qty, netTot, vatTot, grandTot
				,actualnet, actualvat, actualgrand from orderdetail where transno = $orderno and lineno =$lineno";
			$newrecords=$db_conn->query($sql_query);
			$newrecord=mysqli_fetch_array($newrecords);
			
			#Refund without proof of purchase and no reference to previous transaction
			$sql_query="update orderdetail set status = 'J', netTot=".($newrecord['netTot']*-1).", vatTot=".($newrecord['vatTot']*-1).", grandTot=".($newrecord['grandTot']*-1);
			if ($newrecord['actualgrand']<>0)
			{
				$sql_query.=" , actualnet=".($newrecord['actualnet']*-1).", actualvat=".($newrecord['actualvat']*-1).", actualgrand=".($newrecord['actualgrand']*-1);
			}

			$sql_query.=" , qty=".($newrecord['qty']*-1)." where transno = $orderno and lineno = $lineno";
			$doit=$db_conn->query($sql_query);
			
			#Set to NULL if zero
			$sql_query="update orderdetail set actualnet=NULL, actualvat=NULL, actualgrand=NULL where transno = $orderno and lineno = $lineno and actualnet=0.00";
			$doit=$db_conn->query($sql_query);
		}
		else
		{
			$sql_query="select transno, StockRef, colour, size, sizeindex, qty, netTot, vatTot, grandTot
				, actualnet, actualvat, actualgrand, costprice from orderdetail where transno = $orderno and lineno =$lineno";
			$newrecords=$db_conn->query($sql_query);
			$newrecord=mysqli_fetch_array($newrecords);
			$nextline=getOrderLinesCnt();
			

			$sql_query="insert into orderdetail 
					(transno, StockRef, colour, size, sizeindex, lineno, qty, status, netTot, vatTot, grandTot
					, actualnet, actualvat, actualgrand, costprice) 
					values ($orderno,'".$newrecord['StockRef']."','".$newrecord['colour']
			."','".$newrecord['size']."',".$newrecord['sizeindex'].",".($nextline+1).",".($newrecord['qty']*-1).",'K',".($newrecord['netTot']*-1).",".($newrecord['vatTot']*-1).",".($newrecord['grandTot']*-1).","
			.($newrecord['actualnet']*-1).",".($newrecord['actualvat']*-1).",".($newrecord['actualgrand']*-1).",".($newrecord['costprice']*-1).")";
			$negrecord=$db_conn->query($sql_query);
			#Change original line to voided
			$sql_query="update orderdetail set status = 'K' where transno = $orderno and lineno = $lineno";
			$doit=$db_conn->query($sql_query);
			
			#Set to NULL if zero
			$sql_query="update orderdetail set actualnet=NULL, actualvat=NULL, actualgrand=NULL where transno = $orderno and lineno = ".($nextline+1)." and actualnet=0.00";
			$doit=$db_conn->query($sql_query);
		}
		createRollEntry($_SESSION['custref'], $newrecord['StockRef']."-".$newrecord['colour']."-".$newrecord['size'], 1, $newrecord['grandTot'], 'R');
		updateReadout();
		
}

if ($action=="unvoid")
{
	$sql_query="select transno, StockRef, colour, size, sizeindex, qty, netTot, vatTot, grandTot, status from orderdetail where transno = $orderno and lineno =$lineno";
	$newrecords=$db_conn->query($sql_query);
	$newrecord=mysqli_fetch_array($newrecords);
	$nextline=getOrderLinesCnt();
	if ($newrecord['status']=='K')
	{
	   $sql_query="delete from orderdetail where transno= $orderno and status in ('K') and qty <0";
	   $remrecord=$db_conn->query($sql_query);
	   $sql_query="update orderdetail set status = 'C' where transno = $orderno and status ='K' and qty >0";
	   $doit=$db_conn->query($sql_query);
	   updateReadout();
	}
	elseif ($newrecord['status']=='J')
	{
	    $sql_query="delete from orderdetail where transno= $orderno and status in ('J') and lineno = $lineno";
	    $remrecord=$db_conn->query($sql_query);
	}

}

#Get order lines
if ($orderno<>0)
{
	$sql_query="select StockRef,colour, size, qty, lineno, status, grandtot, actualgrand, onsale from orderdetail where transno=$orderno and status not in ('V') order by lineno";
	$results=$db_conn->query($sql_query);
}

echo "<table width=100%><tr class=bagtablehead><td class=bagtablehead></td><td class=bagtablehead>SKU</td><td class=bagtablehead>Price</td>
		<td class=bagtablehead>Discount</td><td class=bagtablehead>New Price</td>
		<td align=center class=bagtablehead>Qty</td>
		<td align=center class=bagtablehead><td></td></tr>";
while ($bagitem=mysqli_fetch_array($results))
{
	if ($bagitem['qty']>0)
	{
	    if ($bagitem['status']=="X")
	    {
	        $onapproline=1;
	    }
	    elseif ($bagitem['status']<>"J" && $bagitem['status']<>"K")
	    {
    		$photo=getWebImage($bagitem['StockRef'], $bagitem['colour']);
    		if ($photo[0]=="")
    		{
    			echo "<tr><td></td>";
    		}
    		else
    		{
    			echo "<tr><td><img width=50 src=\"".$pics_path."/".$photo[0]."\" /></td>";
    		}
    		echo "<td>".$bagitem['StockRef']."-".$bagitem['colour']."-".$bagitem['size']."</td>";
    		echo "<td id=price onclick=\"javascript:discount('".urlencode($bagitem['StockRef'])."','".$bagitem['lineno']."', 'full', ".$bagitem['grandtot'].");\" class=\"";
    		if ($bagitem['onsale']==1)
    		{
    			echo "sale";
    		}	
    				
    		echo " clickable\">&pound;".$bagitem['grandtot']."</td>";
    	
    		if ($bagitem['actualgrand']=="")
    		{

    			echo "<td id=\"price clickable\"></td><td></td><td onclick=\"changeQty('".$bagitem['StockRef']."','".$bagitem['colour']."');\" align=center class=clickable>"
    		              .$bagitem['qty']."</td>";
    			     
    		}
    		elseif ($bagitem['actualgrand']<>"" && $bagitem['actualgrand'] <> $bagitem['grandtot'])
    		{
    			echo "<td id=\"price clickable\">&pound;".(number_format(($bagitem['grandtot']-$bagitem['actualgrand']),2))."</td>
    				<td onclick=\"javascript:discount('".urlencode($bagitem['StockRef'])."','".$bagitem['lineno']."', 'discount',". $bagitem['actualgrand'].");\" id=\"price\" class=\"price clickable\">&pound;".$bagitem['actualgrand']."</td>
    				<td onclick=\"changeQty('".urlencode($bagitem['StockRef'])."');\" align=center class=clickable>".$bagitem['qty']."</td>";
    		}
    		
    		else 
    		{
    			echo "<td></td><td onclick=\"changeQty('".urlencode($bagitem['StockRef'])."');\" align=center class=clickable>".$bagitem['qty']."</td>";
    		}
    		
    		echo "<td align=center>";
    		
    		if ($bagitem['status']=='C')
    		{
    			echo "<button class=half onclick=\"javascript:voidLine(".$bagitem['lineno'].");\">Return</button>";
    		}
    		elseif ($bagitem['status']=='A')
    		{
    			 echo "<button  class=half  onclick=\"javascript:negateLine(".$bagitem['lineno'].");\">Remove</button>";
    			 echo "<button  class=half  onclick=\"javascript:onAppro(".$bagitem['lineno'].");\" >Buy</button>";
    		}
    		elseif ($bagitem['status']=='V' && $bagitem['qty']>0)
    		{
    			echo "Returned&nbsp;&nbsp;&nbsp;&nbsp;";
    			echo "<button  class=half  onclick=\"javascript:UNvoidLine(".$bagitem['lineno'].",'V');\">Undo</button>";
    		}
    		elseif ($bagitem['status']=='J')
    		{
    			echo "Returned&nbsp;&nbsp;&nbsp;&nbsp;";
    			echo "<button  class=half  onclick=\"javascript:UNvoidLine(".$bagitem['lineno'].",'J');\">Undo</button>";
    		}

    		else {
    		    if ($onapproline==1)
    		    {
    		       // echo "<button  class=half  onclick=\"javascript:onAppro(".$bagitem['lineno']."-2);\" >OnAppro</button>";
    		    }
    		    else
    		    {
    		        echo "<button  class=half  onclick=\"javascript:removeLine(".$bagitem['lineno'].");\" >Remove</button>";
    		        echo "<button  class=half  onclick=\"javascript:voidLine2(".$bagitem['lineno'].");\" >Return</button>";
    		    }
    			
    			unset($onapproline);
    		}
    		echo "</td></tr>";
	    }
	}
	else
	{
		if ($bagitem['status']=="J" || $bagitem['status']=="K")
		{
			                echo "<tr><td></td><td>".$bagitem['StockRef']."-".$bagitem['colour']."-".$bagitem['size']."</td>";
                echo "<td id=price onclick=\"javascript:discount('".$bagitem['StockRef']."','".$bagitem['lineno']."','full',".$bagitem['grandtot'].");\" class=\"";
                if ($bagitem['onsale']==1)
                {
                        echo "sale";
                }

                echo " clickable\">&pound;".$bagitem['grandtot']."</td>";

                if ($bagitem['actualgrand']=="")
                {
                        echo "<td id=\"price clickable\"></td><td></td><td onclick=\"changeQty('".$bagitem['StockRef']."');\" align=center class=clickable>".$bagitem['qty']."</td>";
                }
                elseif ($bagitem['actualgrand']<>"" && $bagitem['actualgrand'] <> $bagitem['grandtot'])
                {
                        echo "<td id=\"price clickable\">&pound;".(number_format(($bagitem['grandtot']-$bagitem['actualgrand']),2))."</td>
                        <td id=\"price clickable\">&pound;".$bagitem['actualgrand']."</td>
                		<td onclick=\"changeQty('".$bagitem['StockRef']."');\" align=center class=clickable>".$bagitem['qty']."</td>";
                }

                else
                {
                        echo "<td></td><td></td><td onclick=\"changeQty('".$bagitem['StockRef']."');\" align=center class=clickable>".$bagitem['qty']."</td>";
                }

                echo "<td align=center>";
		echo "Returned&nbsp;&nbsp;&nbsp;&nbsp;<button class=half style=\"font-size:12pt;\" onclick=\"javascript:UNvoidLine(".$bagitem['lineno'].",'J');\">Undo</button></td></tr>";

		}


	}
}
echo "</table>";



?>
<script type="text/javascript">
	$('#totals').load('./order/totalsCalc.php');
	$('#barcodeentry').val('');
	$('#barcodeentry').focus();

function removeLine(lineno)
{
	$('#bagitems').load('./order/bagContents.php?action=remove&lineno='+lineno);
}

function voidLine2(lineno)
{
	$('#bagitems').load('./order/bagContents.php?action=void&type=new&lineno='+lineno);
}

function negateLine(lineno)
{
	$('#bagitems').load('./order/bagContents.php?action=negate&lineno='+lineno);
}

function onAppro(lineno)
{
	$('#bagitems').load('./order/bagContents.php?action=appro&lineno='+lineno);
}

function voidLine(lineno)
{
	$('#bagitems').load('./order/bagContents.php?action=void&lineno='+lineno);
}

function UNvoidLine(lineno,status)
{
	$('#bagitems').load('./order/bagContents.php?action=unvoid&lineno='+lineno+'&status='+status);
}

$(document).ready(function(){
		$('button').button();
		});

function discount(sku, lineno, type, orig){
         $('#dialog').append('<div id=temp></div>');
                 $('#dialog').css('top','0%');
                 $('#dialog').css('left','50%');
                 $('#dialog').css('margin-left','-20%');
         $('#temp').load('./order/discounts.php?sku='+sku+'&lineno='+lineno+'&disctype='+type+'&orig='+orig);
	 	 $('#dimmer').show();
         $('#dialog').show();
}

function changeQty(sku)
{
	$('#dialog').append('<div id=temp></div>');
	$('#dialog').css('top','30%');
	$('#dialog').css('left','50%');
	$('#dialog').css('margin-left','-10%');
	$('#temp').load('./order/chgqty.php?sku='+sku);
	$('#dimmer').show();
	$('#dialog').show();
}

</script>
