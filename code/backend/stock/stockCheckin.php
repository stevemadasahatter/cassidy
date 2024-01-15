<?php
include '../config.php';
include '../functions/auth_func.php';
include '../functions/field_func.php';
include '../functions/stock_func.php';
include '../functions/print_func.php';

$action=$_REQUEST['action'];
$selected=$_REQUEST['id'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
# Stock arrives in boxes from suppliers, so it is outstandning stock per supplier that is important
$brands=getSelect('brands',$selected);

ob_start();

if($_REQUEST['sub']=='print')
{
    echo "<html><head><link rel=stylesheet type=\"text/css\" href=\"../style/site-1-3.css\" /></head><body>";
}
if ($action<>"accept" && $action<>"unaccept" && $action<>"clear")
{
	echo "<h2>Select a Brand</h2>";
	#Must be searching for a size then
	echo "<table>";
	echo "<tr><td><select onchange=\"javascript:select(this.value);\" name=brand>$brands</select></td>
			<td>Del Note</td><td><input onchange=\"javascript:changed();\" type=text id=note value=\"".$_REQUEST['note']."\" /></td>
			<td>Effective Del Date (YYYY/MM/DD)</td><td><input type=date onchange=\"javascript:changed();\" id=deldate value=\"".$_REQUEST['deldate']."\" />
            <input type=hidden name=setval value=\"0\" /><input type=hidden id=predelnote value=\"0\" /></td>
            <td><button onclick=\"javascript:print_checked();\">Print</button></td></tr>";
	echo "</table>";
	
	echo "<div id=unallocstock></div>";
	echo "<div id=message></div>";
}

if ($action=="select")
{
    if($_REQUEST['sub']<>'print')
    {
	   $sql_query="select style.sizekey, stock.Stockref, category.nicename
			, styleDetail.description, stock.costprice, stock.retailprice
			,stock.colour, stock.purchased1, stock.purchased2
			,stock.purchased3,stock.purchased4,stock.purchased5
			,stock.purchased6,stock.purchased7,stock.purchased8
			,stock.purchased9,stock.purchased10,stock.purchased11,stock.purchased12,stock.purchased13,stock.purchased14,stock.purchased15,stock.purchased16
            ,stock.purchased17
			from styleDetail, stock, style, category
			where styleDetail.sku=stock.Stockref
			and styleDetail.sku=style.sku
			and styleDetail.category=category.id
			and styleDetail.brand = $selected
			and stock.forsale=0
			order by stock.Stockref, stock.colour";
    }
    else 
    {
        $sql_query="select style.sizekey, stock.Stockref, category.nicename
			, styleDetail.description, stock.costprice, stock.retailprice
			,stock.colour, stock.physical1, stock.physical2
			,stock.physical3,stock.physical4,stock.physical5
			,stock.physical6,stock.physical7,stock.physical8
			,stock.physical9,stock.physical10,stock.physical11,stock.physical12,stock.physical13,stock.physical14,stock.physical15,stock.physical16
            ,stock.physical17
			from styleDetail, stock, style, category
			where styleDetail.sku=stock.Stockref
			and styleDetail.sku=style.sku
			and styleDetail.category=category.id
			and (concat(styleDetail.sku,'-',stock.colour)) in (".urldecode($_REQUEST['ids']).")
			order by stock.Stockref, stock.colour";
    }
	$results=$db_conn->query($sql_query);
	if (mysqli_affected_rows($db_conn)==0)
	{
		echo "<h2>There are no stock items pending checkin for this brand. Please select another </h2>";
		exit();
	}
	
	if ($_REQUEST['sub']<>'print')
	{
	   echo "<p>The table below shows all the stock for the selected brand which has yet to be received. Please check the quantities and correct where needed, and then press Accept</p>";
	}
	else
	{
	    echo "<p>The table below shows the checked in stock quantities</p>";
	}
	while ($result=mysqli_fetch_array($results))
	{
		$sizehtml="";
		$stockhtml="";
		$sizearray=getSizeArray($result['sizekey']);
		for ($i=1;$i<=17;$i++)
		{
			$sizehtml.="<li class=\"importtitle\" >".$sizearray['size'.$i]."</li>";
			if ($_REQUEST['sub']<>'print')
			{
			     $stockhtml.="<li class='importitem'><input align=center style=\"width:40px;text-align:center;\" type=text name=\"physical".$i."\" value=".$result['purchased'.$i]."></li>";
			}
			else
			{
			    $stockhtml.="<li class='importitem'><input align=center style=\"width:40px;text-align:center;\" type=text name=\"physical".$i."\" value=".$result['physical'.$i]."></li>";
			}
			
		}
		$sizehtml.="<li class=importtitle></li><li class=importtitle></li><li class=importtitle></li><li class=importtitle></li>";

		echo "<div id=\"".$result['Stockref']."-".$result['colour']."\">";
		echo "<ul style=\"display:table-row;width:80%;\"><li class=importtitle>Status</li><li class=importtitle >SKU/Colour</li>";
		echo "<li class=importtitle>Category</li>$sizehtml</ul>";
		echo "<ul style=\"display:table-row;width:80%'\"><li class=status></li><li class=\"importitem name\" name=sku>";
			echo "<table width=100%><tr><td colspan=2 class=\"importitem name\">".$result['Stockref']."-".$result['colour']."</td></tr>";
			echo "<tr><td class=\"importitem name\">".$result['description']."</td></tr>";
			echo "<tr><td class=\"importitem name\">".$result['costprice']."/".$result['retailprice']."</td></tr>";
			echo "</table>";
		echo "</li>";
		if ($_REQUEST['sub']=='print')
		{
		    echo "<li class=\"importitem category\">".$result['nicename']."$stockhtml";
		}
		else
		{
		    echo "<li class=\"importitem category\">".$result['nicename']."$stockhtml<input type=hidden name=active value=\"\" /></li>";
		}

		if ($_REQUEST['sub']<>'print')
		{
    		echo "<li class=importitem><button onclick=\"javascript:acceptStock('".$result['Stockref']."','".$result['colour']."');\">Accept<br>Stock</button></li>
    		<li  class=importitem><button disabled id=\"".$result['Stockref']."-".$result['colour']."prtbarcode\" onclick=\"javascript:printBarcodes('".$result['Stockref']."','".$result['colour']."');\">Print All<br>Barcodes</button></li>
    		<li class=importitem><button onclick=\"javascript:unacceptStock('".$result['Stockref']."','".$result['colour']."');\">Unaccept<br>Stock</button></li>
            <li class=importitem><button onclick=\"javascript:clearStock('".$result['Stockref']."','".$result['colour']."');\">Clear<br>Item</button></li></ul>";
    		//echo "<ul style=\"display:table-row;width:80%;\"><li></li></ul>";
		}
		echo "<p width=100%><hr></p>";
		echo "</div>";
	}
//	echo "</ul>";	
}

if ($action=="accept")
{
	#Need to move the purchased quantities (corrected) into the physical
	$sql_query="update stock set 
				physical1=".$_REQUEST['physical1'].",
				physical2=".$_REQUEST['physical2'].",
				physical3=".$_REQUEST['physical3'].",
				physical4=".$_REQUEST['physical4'].",
				physical5=".$_REQUEST['physical5'].",
				physical6=".$_REQUEST['physical6'].",
				physical7=".$_REQUEST['physical7'].",
				physical8=".$_REQUEST['physical8'].",
				physical9=".$_REQUEST['physical9'].",
				physical10=".$_REQUEST['physical10'].",
				physical11=".$_REQUEST['physical11'].",
				physical12=".$_REQUEST['physical12'].",
				physical13=".$_REQUEST['physical13'].",
				physical14=".$_REQUEST['physical14'].",
				physical15=".$_REQUEST['physical15'].",
				physical16=".$_REQUEST['physical16'].",
                physical17=".$_REQUEST['physical17'].",
				delnote='".$_REQUEST['note']."',
				deldate='".$_REQUEST['deldate']."',
				forsale=1													
				where Stockref='".$_REQUEST['sku']."' and colour='".$_REQUEST['colour']."'";
	$do_it=$db_conn->query($sql_query);
	$error=mysqli_errno($db_conn);
	
	if ($error==0)
	{
		echo "<img src=./images/ok.png>";
		
	}
	else
	{
		echo "<img src=./images/red-cross.jpg>";
	}
}

if ($action=="unaccept")
{
	#Need to move the purchased quantities (corrected) into the physical
	$sql_query="update stock set
				physical1=NULL,
				physical2=NULL,
				physical3=NULL,
				physical4=NULL,
				physical5=NULL,
				physical6=NULL,
				physical7=NULL,
				physical8=NULL,
				physical9=NULL,
				physical10=NULL,
				physical11=NULL,
				physical12=NULL,
				physical13=NULL,
				physical14=NULL,
				physical15=NULL,
				physical16=NULL,
                physical17=NULL,
				delnote='".$_REQUEST['note']."',
				deldate='".$_REQUEST['deldate']."',
				forsale=0
				where Stockref='".$_REQUEST['sku']."' and colour='".$_REQUEST['colour']."'";
	$do_it=$db_conn->query($sql_query);
	$error=mysqli_errno($db_conn);
	
	if ($error==0)
	{
		echo "";
	
	}
	else
	{
		echo "<img src=./images/red-cross.jpg>";
	}
}

if ($action=="clear")
{
    
    $sql_query="update stock set
				forsale=2
				where Stockref='".$_REQUEST['sku']."' and colour='".$_REQUEST['colour']."'";
    $do_it=$db_conn->query($sql_query);
    $error=mysqli_errno($db_conn);
    
    if ($error==0)
    {
        echo "<img height=32 width=32 src=./images/bin.jpg>";
        
    }
    else
    {
        echo "<img src=./images/red-cross.jpg>";
    }
}

echo "<div id=dialog-confirm></div>";
echo "</body></html>";
$html="";
$html=ob_get_clean();


    echo $html;
?>
<script type="text/javascript">
$(document).ready(function(){
	$('button').button();
});

	
$('#output').unload(function()
{
	changed();
});

function select(id)
{
	$('#output').load('./stock/stockCheckin.php?action=select&id='+id);

}

function acceptStock(sku,colour)
{
	var skuenc=encodeURIComponent(sku);
	var deldate=$('#deldate').val();
	var delnote=$('#note').val();


	if (delnote=="" || deldate=="")
	{
		$('#dialog-confirm').html('<div id=temp>Delivery note and date must be entered</div>');
	    $("#dialog-confirm" ).dialog({
	        resizable: false,
	        height:360,
	        autoOpen: true,
	        modal: true,
	        title: "Input Validation",
	        buttons: {
	          "OK": function() {
	            $( this ).dialog( "close" );
	            return;
	          },
	        }
	      });
	}
	else 
	{
		var getString="";
		$('div[id=\"'+sku+'-'+colour+'\"]').find('input[name^=physical]').each(function(){
			getString=getString+$(this).attr('name')+'='+$(this).val()+'&';
		});
		$('div[id=\"'+sku+'-'+colour+'\"]').find('input[name=active]').each(function(){
			$(this).val(sku+'-'+colour);
		});
		$('button[id=\"'+sku+'-'+colour+'prtbarcode\"]').button({
				disabled : false
		});
		delnote=encodeURI($('#note').val());
		getString=getString+'note='+delnote+'&deldate='+$('#deldate').val();
		$('input[name=setval]').val(1);
		$('div[id=\"'+sku+'-'+colour+'\"] li.status').load('./stock/stockCheckin.php?action=accept&sku='+skuenc+'&colour='+colour+'&'+getString);
	}
	
}

function unacceptStock(sku,colour)
{
	var skuenc=encodeURIComponent(sku);
	var getString="";
	$('div[id=\"'+sku+'-'+colour+'\"]').find('input[name^=physical]').each(function(){
		getString=getString+$(this).attr('name')+'='+$(this).val()+'&';
	});
	delnote=encodeURI($('#note').val());
	$('#'+sku+'-'+colour+'prtbarcode').button({
		disabled : true
	});
	getString=getString+'note='+delnote+'&deldate='+$('#deldate').val();
	$('div[id=\"'+sku+'-'+colour+'\"] li.status').load('./stock/stockCheckin.php?action=unaccept&sku='+skuenc+'&colour='+colour+'&'+getString);
}

function clearStock(sku,colour)
{
	var skuenc=encodeURIComponent(sku);
	var getString="";
	$('div[id=\"'+sku+'-'+colour+'\"]').find('input[name^=physical]').each(function(){
		getString=getString+$(this).attr('name')+'='+$(this).val()+'&';
	});
	$('div[id=\"'+sku+'-'+colour+'\"] li.status').load('./stock/stockCheckin.php?action=clear&sku='+skuenc+'&colour='+colour+'&'+getString);
}

function printBarcodes(sku,colour)
{
	$('#dialog').append('<div id=temp></div>');
	$('#dialog').css('top','30%');
	$('#dialog').css('left','50%');
	$('#dialog').css('margin-left','-10%');
	$('#dimmer').show();
	$('#temp').load('./stock/printBarcode.php?action=printall&sku='+sku+'&colour='+colour);
	$('#temp').remove();
	$('#dimmer').hide();
	
}

function changed()
{
	var delnote=encodeURIComponent($('#predelnote').val());
	var deldate=encodeURIComponent($('#deldate').val());
	if ($('input[name=setval]').val()==1)
	{
		$('#dialog').load('./report/output.php?delnote='+delnote+'&deldate='+deldate+'&action=print&datein=&dateout=&dataset=importdelnote.php&totals=0&totals=0&zeros=0&dupe=0&');
		$('input[name=setval]').val(0);
	}
	$('#predelnote').val($('#note').val());
}

function print_checked()
{
	var getString;
	getString='action=select&sub=print';
	getString=getString+'&note='+$('#note').val()+'&deldate='+$('#deldate').val()+'&id='+$('select[name=brand]').val()+'&ids=';
	var firsttime=0;
	$('div').find('input[name=active]').each(function(){
		if ($(this).val()!='')
		{
			if (firsttime==0)
			{
				getString=getString+"'"+($(this).val())+"'";
				firsttime=1;
			}
			else
			{
				getString=getString+",'"+($(this).val())+"'";
			}
		}
	});
	getString=encodeURI(getString);
	window.open('./stock/stockCheckin.php?'+getString);
}

</script>
