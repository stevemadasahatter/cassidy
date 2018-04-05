<?php

include '../config.php';
include '../functions/auth_func.php';
include '../functions/field_func.php';
include '../functions/stock_func.php';
session_start();
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$action=$_REQUEST['action'];
$brand=$_REQUEST['brand'];
$season=$_REQUEST['season'];
$sku=$_REQUEST['sku'];
if ($action=="")
{
	$brands=getSelect('brands', '');
	$season=getSelect('seasons','');
	echo "<h3>Select brand and Season to mark items onsale</h3>";
	echo "<ul class=import><li class=importtitle>Brand</li><li class=importtitle>Season</li><li class=importtitle>SKU</li></ul>";
	echo "<ul class=import><li class=importtitle><select id=selbrand onchange=\"javascript:change();\" name=brand>".$brands."</select></li>";
	echo "<li class=importtitle><select id=selseason onchange=\"javascript:change();\" name=season>".$season."</select></li>";
	echo "<li class=importtitle><input id=selsku onkeyup=\"javascript:change();\" name=sku></li></ul>";
	echo "<div id=salepriceresults></div>";
}

if ($action=="select")
{
	$sql_query="select style.sku, stock.colour, stock.costprice, stock.retailprice, stock.saleprice, style.onsale
			from stock, style, styleDetail
			where stock.Stockref=style.sku
			and styleDetail.sku=style.sku";
	if ($brand<>"")
	{
		$sql_query.=" and brand=$brand";
	}
	if ($season<>"")
	{
		$sql_query.=" and season=$season";
	}
	if ($sku<>"")
	{
		$sql_query.=" and style.sku like '%".$sku."%'";
	}
	$results=$db_conn->query($sql_query);
	
	echo "<ul class=import><li class=importtitle></li><li class=importtitle>SKU</li><li class=importtitle>Colour</li><li class=importtitle>Cost</li><li class=importtitle>Retail</li><li class=importtitle>Sale Price</li><li class=importtitle>Markup/Discount</li></ul>";
	
	while ($result=mysqli_fetch_array($results))
	{
		echo "<ul id='".$result['sku']."-".$result['colour']."-row' class=import>";
		echo "<li id='".$result['sku']."-".$result['colour']."-tick' class=importitem></li>";
		echo "<li class=importitem>".$result['sku']."</li>";
		echo "<li class=importitem>".$result['colour']."</li>";
		echo "<li class=importitem>&pound;".$result['costprice']."</li>";
		echo "<li class=importitem>&pound;".$result['retailprice']."</li>";
		echo "<li class=importitem>&pound;".$result['saleprice']."<input type=hidden id='".$result['sku']."-".$result['colour']."-value' value=".$result['saleprice']." /></li>"; 
		echo "<li class=importitem><input id='".$result['sku']."-".$result['colour']."-check' onchange=\"javascript:commit('".$result['sku']."','".$result['colour']."');\" type=checkbox";
		if ($result['onsale']==1)
		{
			echo " checked";
		}
		echo "></li>";
		echo "<li id=".$result['sku']."-".$result['colour']."-output class=importitem ></li>";
		echo "</ul></div>";
	}
}

if ($action=="commit")
{
	if ($_REQUEST['onoff']=="true")
	{
		$onsale=1;
	}
	else 
	{
		$onsale=0;
	}
	$sql_query="update style set onsale=$onsale where sku ='".$_REQUEST['sku']."'";

	$doit=$db_conn->query($sql_query);
	
	$errno=mysqli_errno($db_conn);
	
	if ($errno>0)
	{
		echo "<img src=./images/red-cross.jpg>";
	}
	else
	{
		echo "<img src=./images/ok.png>";
	}

}
?>
<script type="text/javascript">

function change()
{
	var brand=$('#selbrand').val();
	var season=$('#selseason').val();
	var sku=$('#selsku').val();
	if ((brand!=1 && season!=1) || sku.length>1 )
	{
		if (brand==1)
		{
			brand="";
		}
		if (season==1)
		{
			season=""
		}
		$('#salepriceresults').load('./stock/editOnSale.php?action=select&brand='+brand+'&season='+season+'&sku='+sku);
	}
}


function commit(sku,colour)
{
	var current=$('[id=\"'+sku+'-'+colour+'-value\"]').val();
	var onoff=$('[id=\"'+sku+'-'+colour+'-check\"]').is(':checked');
    $('[id=\"'+sku+'-'+colour+'-row\"]').css('background-color','#fff');
    coloursafe=encodeURIComponent(colour);
	$('[id=\"'+sku+'-'+colour+'-tick\"]').load('./stock/editOnSale.php?action=commit&sku='+sku+'&colour='+coloursafe+'&saleprice='+current+'&onoff='+onoff);
}

function highlight(sku,colour)
{
	$('[id=\"'+sku+'-'+colour+'-row\"]').css('background-color','#ffe');
}

function unhighlight(sku,colour)
{
        $('[id=\"'+sku+'-'+colour+'-row\"]').css('background-color','#fff');
}


</script>
