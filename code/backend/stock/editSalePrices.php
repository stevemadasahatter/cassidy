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
	echo "<h3>Select brand and Season, or type an SKU to mark down</h3>";
	echo "<ul class=import><li class=importtitle>Brand</li><li class=importtitle>Season</li><li class=importtitle>SKU</li></ul>";
	echo "<ul class=import><li class=importtitle><select id=selbrand onchange=\"javascript:change();\" name=brand>".$brands."</select></li>";
	echo "<li class=importtitle><select id=selseason onchange=\"javascript:change();\" name=season>".$season."</select></li>";
	echo "<li class=importtitle><input id=selsku onkeyup=\"javascript:change();\" name=sku></li></ul>";
	echo "<div id=salepriceresults></div>";
}

if ($action=="select")
{
	$sql_query="select style.sizekey, style.sku, stock.colour, stock.costprice, stock.retailprice, stock.saleprice, 
			category.nicename category, styleDetail.description
			from stock, style, styleDetail, category
			where stock.Stockref=style.sku
			and styleDetail.category=category.id
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
	
	echo "<ul class=import><li class=importtitle></li><li class=importtitle>SKU</li>
							<li class=importtitle>Colour</li><li class=importtitle>Description</li>
							<li class=importtitle>Type</li><li class=importtitle>Cost</li><li class=importtitle>Retail</li>
							<li class=importtitle>Bought</li><li class=importtitle>Sold</li>
							<li class=importtitle>Markup/Discount</li></ul>";
	
	while ($result=mysqli_fetch_array($results))
	{
		$stockbalance=0;
		$stockbal=stockBalance($result['sku'], $result['colour'],'');
		for ($i=0;$i<=10;$i++)
		{
			$stockbalance+=$stockbal['physical'.$i];
		}
		
		
		$purchasedbal=getPurchasedStock($result['sku'], $result['colour']);
		$purchasedbalance=array_sum($purchasedbal)/2;
		$sold=$purchasedbalance-$stockbalance;
		echo "<ul id='".$result['sku']."-".$result['colour']."-row' class=import>";
		echo "<li id='".$result['sku']."-".$result['colour']."-tick' class=importitem></li>";
		echo "<li class=importitem>".$result['sku']."</li>";
		echo "<li class=importitem>".$result['colour']."</li>";
		echo "<li class=importitem>".$result['description']."</li>";
		echo "<li class=importitem>".$result['category']."</li>";
		echo "<li class=importitem>&pound;".$result['costprice']."</li>";
		echo "<li class=importitem>&pound;".$result['retailprice']."</li>";
		echo "<li class=importitem>".$purchasedbalance."</li>";
		echo "<li class=importitem>".$sold."</li>";
		echo "<li class=importitem><input  id='".$result['sku']."-".$result['colour']."' class=price 
			onfocus=\"javascript:highlight('".$result['sku']."','".$result['colour']."');\"
			onblur=\"javascript:unhighlight('".$result['sku']."','".$result['colour']."');\"
			onchange=\"javascript:commit('".$result['sku']."','".$result['colour']."');\" 
			onkeyup=\"javascript:update('".$result['sku']."','".$result['colour']."','".$result['costprice']."','".$result['retailprice']."');\" 
			type=text name=price value=".$result['saleprice']."></li>";
		echo "<li id='".$result['sku']."-".$result['colour']."-output' class=importitem ></li>";
		echo "</ul></div>";
	}
}

if ($action=="commit")
{
	if ($_REQUEST['saleprice']=='')
	{
		$sql_query="update stock set saleprice=NULL where Stockref ='".$_REQUEST['sku']."' and colour='".$_REQUEST['colour']."'";
	}
	else
	{
		$sql_query="update stock set saleprice=".$_REQUEST['saleprice']." where Stockref ='".$_REQUEST['sku']."' and colour='".$_REQUEST['colour']."'";
	}
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
	if ((brand!=1 && season!=1) || sku!="" )
	{
		if (brand==1)
		{
			brand="";
		}
		if (season==1)
		{
			season=""
		}
		$('#salepriceresults').load('./stock/editSalePrices.php?action=select&brand='+brand+'&season='+season+'&sku='+sku);
	}
}

function update(sku, colour, cost, retail)
{
	var perc=0;
	var mrkup=0;
	var output="";
	var current=$('[id=\"'+sku+'-'+colour+'\"]').val();
	perc=(retail-current)/retail*100;
	mrkup=((+current)/(+cost));
	output=Math.round(mrkup*10)/10+' / '+Math.round(perc)+'%'; 
	$('[id=\"'+sku+'-'+colour+'-output\"]').text(output);
}

function commit(sku,colour)
{
	var current=$('[id=\"'+sku+'-'+colour+'\"]').val();
        $('[id=\"'+sku+'-'+colour+'-row\"]').css('background-color','#fff');
     coloursafe=encodeURIComponent(colour);
	skusafe=encodeURIComponent(sku);
	$('[id=\"'+sku+'-'+colour+'-tick\"]').load('./stock/editSalePrices.php?action=commit&sku='+skusafe+'&colour='+coloursafe+'&saleprice='+current);
}

function highlight(sku,colour)
{
	$('[id=\"'+sku+'-'+colour+'-row\"]').css('background-color','#ffe');
}

function unhighlight(sku,colour)
{
        $('[id=\"'+sku+'-'+colour+'-row\"').css('background-color','#fff');
}


</script>
