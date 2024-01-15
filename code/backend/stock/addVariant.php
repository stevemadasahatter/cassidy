<?php
include '../config.php';
include '../functions/auth_func.php';
include '../functions/field_func.php';
include '../functions/stock_func.php';

$sku=$_REQUEST['sku'];
$sizekey=$_REQUEST['sizekey'];
session_start();
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($_REQUEST['action']=="add")
{
	for ($i=1;$i<=10;$i++)
	{
		if ($_REQUEST['physical'.$i]=="")
		{
			$_REQUEST['physical'.$i]=0;
		}
	}
	if ($_REQUEST['saleprice']=="")
	{
		$_REQUEST['saleprice']=0.00;
	}
	$sql_query="insert into stock (Stockref, company, colour, forsale, physical1, physical2, physical3, physical4, physical5, physical6, physical7, physical8, physical9, physical10, costprice, retailprice, saleprice) 
			values (\"".$_REQUEST['sku']."\",".$_SESSION['CO'].",\"".$_REQUEST['col']."\",1,
			coalesce(".$_REQUEST['physical1'].",0),coalesce(".$_REQUEST['physical2'].",0),coalesce(".$_REQUEST['physical3'].",0),coalesce(".$_REQUEST['physical4'].",0),coalesce(".$_REQUEST['physical5'].",0),
			coalesce(".$_REQUEST['physical6'].",0),coalesce(".$_REQUEST['physical7'].",0),coalesce(".$_REQUEST['physical8'].",0),coalesce(".$_REQUEST['physical9'].",0),coalesce(".$_REQUEST['physical10'].",0)
			, ".$_REQUEST['costprice'].", ".$_REQUEST['retailprice'].", coalesce(".$_REQUEST['saleprice'].",0.00))";
	$do_it=$db_conn->query($sql_query);
	exit();
}

echo "<div id=variantadd>";
echo "<table width=80% align=left>";
echo "<tr><th>Colour</th><th>Cost</th><th>Retail</th><th>Sale</th><th colspan=20>Stock Levels per Size</th></tr>";
$j=0;
echo "<tr><th></th><th></th><th></th><th></th>";
$sizes=getSizeArray($sizekey);
for ($b=1;$b<21;$b++)
{
	if ($sizes['size'.($b+1)]=="")
	{
		echo "<th align=center>".$sizes['size'.$b]."</th>";
		$m=$b;
		$b=40;
	}
	else
	{
		echo "<th align=center>".$sizes['size'.$b]."</th>";
	}
}
echo "</tr>";

$sql_query="select costprice, retailprice, saleprice from stock where Stockref = '".$sku."'";

$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);

$colour=getSelect('colours2','');
echo "<tr><td><select name=col>".$colour."</select></td>";
echo "<td><input class=price name=costprice value=".$result['costprice']."></input></td>";
echo "<td><input class=price name=retailprice value=".$result['retailprice']."></input></td>";
echo "<td><input class=price name=saleprice value=".$result['saleprice']."></input></td>";

for ($h=1;$h<=$m;$h++)
{
	echo "<td align=center><input class=phy name=physical".$h." ></td>";
}
echo "<input type=hidden name='sku' value='$sku' ></tr>";


echo "</table>";
echo "<p width=100% align=right><button onclick=\"javascript:addVariant('$sku');\">Add</button><button onclick=\"javascript:pageClose('$sku');\">Close</button></p>";
echo "</div>";

?>

<script type="text/javascript">

$(document).ready(function(){
	$('button').button();
});

function pageClose(sku)
{
	$('#temp').remove();
	$('#dimmer').hide();
	$('#dialog').hide();
	$('#searchresults').load('./stock/editStockcard.php?action=select&term='+sku);
}

function addVariant(sku)
{
	var getString="";
	$('div[id=variantadd]').find('input').each(function(){
		getString=getString+$(this).attr('name')+'='+$(this).val()+'&';
	});
	$('div[id=variantadd]').find('select').each(function(){
		getString=getString+$(this).attr('name')+'='+$(this).val()+'&';
	});
	$('#temp').load('./stock/addVariant.php?action=add&'+getString);
	$('#temp').remove();
	$('#dimmer').hide();
	$('#dialog').hide();
	$('#output').load('./stock/editStockcard.php?action=select&term='+sku);
}
</script>
