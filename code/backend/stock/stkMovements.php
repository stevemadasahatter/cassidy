<?php
include '../config.php';
include '../functions/auth_func.php';
include '../functions/field_func.php';
include '../functions/stock_func.php';
include '../functions/barcode_func.php';

echo<<<EOF
<script type=text/javascript>
$(document).ready(function(){
	$('button').button();
	$('#stockholding').accordion();
});

function printBarcode(sku)
{
    $('#dialog').html('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-14%');
	$('#temp').load('./stock/printBarcode.php?launch=card&sku='+sku);
	$('#dimmer').show();
	$('#dialog').show();
}

	
function stkcard()
{
    var sku=$('#skuback').val();
    $('#output').load('./stock/editStockcard.php?action=select&term='+sku);
}

function select(value)
{
	$('#searchresults').hide();
	var search2=encodeURIComponent(value);
    var reference=encodeURIComponent($('input[name=reference]').val());
    var reason=encodeURIComponent($('select[name=reason]').val());
    var effdate=encodeURIComponent($('input[name=effdate]').val());
	$('#updater').load('./stock/stkMovements.php?action=load&sku='+search2+'&reference='+reference+'&reason='+reason+'&effdate='+effdate);
}

function save()
{
	if ($('input[name=effdate]').val()=="" || $('input[name=reference]').val()=="" )
	{
		alert('You must enter all fields!');
		$('input[name=effdate]').css('border', '1px solid #ff0000');
		$('input[name=reference]').css('border', '1px solid #ff0000');
		return;
	}
	var sku=encodeURIComponent($('input[name=sku]').val()); 
	var getString="action=save&";
    if ($('#type').val()=='card')
    {
        getString=getString+"type=card&";
    }
	$('input[type!=checkbox]').each(function(){
		if (this.value!="")
		{
		getString=getString+encodeURIComponent(this.name)+"="+encodeURIComponent(this.value);
		getString=getString+'&';
		}
	});
	$('select').each(function(){
		if (this.value!="")
		{
		getString=getString+encodeURIComponent(this.name)+"="+encodeURIComponent(this.value);
		getString=getString+'&';
		}
	});
	getString=getString+'&sku='+sku;
	$('#message').load('./stock/stkMovements.php?'+getString);
	
}

function addRow(colour)
{
	colTot=0;
	$("input[name*='ADJ-"+colour+"']").each(function(){
		colTot= +colTot + +(this.value);
	});
	$('#TOT-'+colour).text(colTot);
}

function srch(value)
{
	$('#searchresults').show();
	var search=encodeURIComponent(value);
	$('#searchresults').load('./stock/stkMovements.php?action=results&sku='+search);
}
</script>
EOF;
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($_REQUEST['effdate']=='')
{
    $now=date('Y-m-d');
}
else
{
    $now=$_REQUEST['effdate'];
}

if ($_REQUEST['header']==1)
{
	echo "<h2>Stock Movements</h2>";
	#Must be searching for a size then
	echo "<table>";
	echo "<tr><td><input id=itemsku autocomplete=off onkeyup=\"javascript:srch(this.value);\" name=name></td><td></tr>";
	echo "</table>";
	
	$reason=getSelect(stkadjReason, $_REQUEST['reason']);
	echo "<table>";
	echo "<tr><td>Reason code</td><td><select name=reason>".$reason."</select></td><td>Date</td><td><input name=effdate type=date value='$now'></td><td>Reference</td><td><input type=text name=reference value='";
	   if ($_REQUEST['reference']<>'undefined')
	   {
	       echo $_REQUEST['reference'];
	   }
	   echo "' /></td></tr>";
	echo "<table>";
	
	
	echo "<div id=searchresults></div>";
	echo "<div id=updater></div>";
	echo "<div id=message></div>";
	

	echo "<script>$('#updater').load('./stock/stkMovements.php?action=load&sku=".$_REQUEST['sku']."');</script>";
}

if ($action=="")
{
	echo "<h2>Stock Movements</h2>";
		#Must be searching for a size then
	echo "<table>";
	echo "<tr><td><input id=itemsku autocomplete=off onkeyup=\"javascript:srch(this.value);\" name=name></td><td></tr>";
	echo "</table>";

	
	echo "<div id=searchresults></div>";
	echo "<div id=updater></div>";
	echo "<div id=message></div>";
	exit();
}
elseif ($action=="results")
{
	$searchterm=$_REQUEST['sku'];
	if ($searchterm=="" || strlen($searchterm)<3)
	{
		exit();
	}
	$sql_query="select styleDetail.sku, seasons.season, brands.nicename from styleDetail, seasons, brands where
			styleDetail.season=seasons.id and styleDetail.brand=brands.id and styleDetail.sku like '".$searchterm."%' limit 30";
	$results=$db_conn->query($sql_query);
	echo "<table  width=100%>";
	echo "<tr><th align=left>SKU</th><th align=left>Season</th><th align=left>Brand</th></tr>";
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr onclick=\"javascript:select('".$result['sku']."');\" ><td>".$result['sku']."</td>";
		echo "<td>".$result['season']."</td>";
		echo "<td>".$result['nicename']."</td>";
		echo "</tr>";
	}
	exit();
}

elseif($action=="save")
{
	foreach ($_REQUEST as $key => $item)
	{
		if (substr($key, 0,4)=="ADJ-")
		{
			$spec=preg_split('/-/', $key);
			$output=createStkAdj($_REQUEST['sku'], $spec[1], $spec[2], $_REQUEST[$key], $_REQUEST['reason'], $_REQUEST['effdate'], $_REQUEST['reference']);
		}
	}
	echo "<h1>Saved</h1>";
	echo "<script type=text/javascript>$('#updater').load('./stock/stkMovements.php?action=load&type=".$_REQUEST['type']."&effdate=".$_REQUEST['effdate']."&sku=".$_REQUEST['sku']."&reference=".$_REQUEST['reference']."&reason=".$_REQUEST['reason']."');</script>";
	
}

elseif ($action=="load")
{
    if ($_REQUEST['effdate']=='' || $_REQUEST['effdate']=='undefined')
    {
        $now=date('Y-m-d');
    }
    else
    {
        $now=$_REQUEST['effdate'];
    }
    
	$term=$_REQUEST['sku'];
	$sql_query="select styleDetail.sku, styleDetail.description, styleDetail.season, styleDetail.brand, sea.nicename, styleDetail.Productgroup, styleDetail.category
			, s.vatkey, s.sizekey, s.onsale	, styleDetail.nonstock, sto.costprice, sto.retailprice
			from styleDetail, style s, stock sto, seasons sea
			where styleDetail.sku=s.sku
			and sea.id = styleDetail.season
			and sto.Stockref = styleDetail.sku
			and styleDetail.sku = '".$term."' and s.company = ".$_SESSION['CO'];
	
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);

	$reason=getSelect(stkadjReason, $_REQUEST['reason']);
	echo "<div id=searchresults></div>";
	echo "<div id=updater>";
	
	echo "<input type=hidden id=skuback value=\"".$_REQUEST['sku']."\" /><table>";
	echo "<tr><td>Reason code</td><td><select name=reason>".$reason."</select></td><td>Date</td><td><input name=effdate type=date value='$now'></td><td>Reference</td><td><input type=text name=reference value='";
	if ($_REQUEST['reference']<>'undefined')
	{
	    echo $_REQUEST['reference'];
	}
	echo "' /></td></tr>";
	echo "<tr><td>Product Code</td><td align=left colspan=3><input name=sku type=text disabled value='".$detail['sku']."' /></td><td>Season</td><td align=left colspan=3><input type=text disabled value='".$detail['nicename']."' /></td></tr>";
	echo "<tr><td>Description</td><td colspan=5><input disabled style=width:400px; type=text name=description value='".$detail['description']."' /></td></tr>";
	echo "</table>";
	

	$sql_query="select sizekey from style where sku = '".$detail['sku']."'";
	$results=$db_conn->query($sql_query);
	$sizekey=mysqli_fetch_array($results);
	
	$sql_query="select size1, size2, size3, size4, size5, size6, size7, size8, size9, size10, size11, size12, size13, size14, size15, size16, size17, size18, size19, size20 from sizes where sizekey = ".$sizekey['sizekey'];
	$results=$db_conn->query($sql_query);
	$sizearray=mysqli_fetch_array($results);
	
	echo "<table><tr><td></td>";
	for ($i=1;$i<=22;$i++)
	{
	if ($sizearray['size'.$i]<>"")
	{
		echo "<td>".$sizearray['size'.$i]."</td>";
				$sizes.="<td>".$sizearray['size'.$i]."</td>";
		}
				else
				{
				$num_sizes=$i-1;
				$i=100;
				}
	}
	echo "<td>Total</td>";
	echo "</tr>";
	
				$sql_query="select colour
		from stock
		where Stockref = '".$detail['sku']."'";
					$colours=$db_conn->query($sql_query);
					while ($colour=mysqli_fetch_array($colours))
					{
						echo "<tr><td>".$colour['colour']."</td>";
						for ($h=1;$h<=$num_sizes;$h++)
						{
							echo "<td><input style=\"width:30px;\" onkeyup=\"javascript:addRow('".$colour['colour']."');\" name='ADJ-".$colour['colour']."-".$h."' type=text /></td>";
					}
		echo "<td id='TOT-".$colour['colour']."'></td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "<p width=100% align=right>";
	
	echo "<button id=save onclick=\"javascript:save();\" >Save</button><button id=barcode onclick=\"javascript:printBarcode('".$detail['sku']."');\" >Barcodes</button>";
	if ($_REQUEST['type']=='card')
	{
	    echo "<button id=stkcard onclick=\"javascript:stkcard();\" >StkCard</button><input type=hidden id=type value=card />";
	}
	echo "</p>";
	echo "<div id=message></div>";
					#Output Stock Holding
echo "<div id=stockholding>";
echo "<h2>Stock Holding</h2>";
echo "<div>";
	$sql_query="select sizekey from style where sku = '".$detail['sku']."'";
	$sizekeys=$db_conn->query($sql_query);
	$sizekey=mysqli_fetch_array($sizekeys);
	
	$sizes=getSizeArray($sizekey['sizekey']);
	
echo "<ul style=\"display:table-row;width:80%;\"><li class=stocktitle>Colour</li>";
					for ($i=1;$i<=21;$i++)
{
	if ($sizes['size'.$i]<>'')
	{
		echo "<li class=stocktitle>".$sizes['size'.$i]."</li>";
					}
					else
					{
						$num_sizes=$i;
						$i=100;
	}
	}
	echo "</ul>";
	
$sql_query="select colour from stock where Stockref = '".$detail['sku']."'";
	$colours=$db_conn->query($sql_query);
	while ($colour=mysqli_fetch_array($colours))
{
	echo "<ul style=\"display:table-row;width:80%;\"><li class=stockitem>".$colour['colour']."</li>";
		$stock=stockBalance($detail['sku'], $colour['colour'],'');
		for ($j=1;$j<$num_sizes;$j++)
	{
		echo "<li class=stockitem><input align=center style=\"width:40px;text-align:center;\" type=text value=".$stock['physical'.$j]."></input></li>";
	
	}
	echo "</ul>";
}
    echo "</div>";
	//echo "<div id=message></div>";
	//echo "</div></div>";
	
}
	


?>

