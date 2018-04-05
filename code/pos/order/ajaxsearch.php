<?php

include '../config.php';
include '../functions/auth_func.php';

$search=urldecode($_REQUEST['s']);


if ($search=="")
{
	exit();
}
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);


$sql_query="select style.sku
, brands.brand
, seasons.season
, styleDetail.description
, stock.colour
, sizes.size1
, sizes.size2
, sizes.size3
, sizes.size4
, sizes.size5
, sizes.size6
, sizes.size7
, sizes.size8
, sizes.size9
, sizes.size10
, sizes.size11
, sizes.size12
, sizes.size13
, sizes.size14
, sizes.size15
, sizes.size16
, sizes.size17
, sizes.size18
, sizes.size19
, sizes.size20				
, styleDetail.nonstock
, stock.retailprice
from stock, styleDetail, sizes, style, brands, seasons
where 1=1
and style.sku like '".$search."%'
and styleDetail.sku=stock.Stockref
and stock.company=style.company
and stock.company=styleDetail.company
and stock.Stockref=style.sku
and styleDetail.brand=brands.id
and styleDetail.season=seasons.id
and style.sizekey=sizes.sizekey
order by style.sku, stock.colour
LIMIT 15
";
//echo $sql_query;
$results=$db_conn->query($sql_query);
echo "<table class=itemsearchtable>";
echo "<tr class=itemheader><td class=itemheader>SKU</td><td class=itemheader>Brand</td><td class=itemheader>Season</td><td class=itemheader>Descripton</td><td class=itemheader>RRP</td>
		<td class=itemheader>Colour</td><td class=itemheader colspan=16>Sizes</td></tr>";
$g=0;
while ($item=mysqli_fetch_array($results))
{
	unset($stock);
	$stock=stockBalance($item['sku'], $item['colour']);
	echo "<tr class=even>";
	
	echo "<td rowspan=1 class=\"itemresult sku\">".$item['sku']."</td>";
	echo "<td rowspan=1 class=\"itemresult\">".$item['brand']."</td>";
	echo "<td rowspan=1 class=\"itemresult\">".$item['season']."</td>";
	echo "<td rowspan=1 class=\"itemresult descr\">".$item['description']."</td>";
	echo "<td rowspan=1 class=\"itemresult\">&pound;".$item['retailprice']."</td>";
	echo "<td rowspan=1 class=\"itemresult colour\">".$item['colour']."</td>";
	for ($i=1;$i<17;$i++)
	{
		if ($item['size'.$i]<>"")
		{
			echo "<td class=\"itemresult size\">".$item['size'.$i]."</td>";
		}
		else
		{
			echo "<td class=\"itemresult nosize\"></td>";
		}
	}
	echo "</tr>";
	
	echo "<tr class=odd>";
	
	echo "<td colspan=6 class=\"itemmeta\">In Stock</td>";
	for ($j=1;$j<17;$j++)
	{
		if ($item['size'.$j]<>"")
		{		
			if (is_numeric($stock['physical'.$j]))
			{
				echo "<td onclick=\"javascript:addItem('".$item['sku']."','".$item['colour']."',".$j.",'".$item['nonstock']."');\" class=\"itemresult size\">".$stock['physical'.$j]."</td>";
			}
			else 
			{
				echo "<td onclick=\"javascript:addItem('".$item['sku']."','".$item['colour']."',".$j.",'".$item['nonstock']."');\" class=\"itemresult nosize\"></td>";
			}
		}
		else
		{
			echo "<td class=\"itemresult nosize\"></td>";
		}
	}
	echo "<td rowspan=1 class=\"itemresult\"></td>";
	echo "<td rowspan=1 class=\"itemresult\"></td>";
	echo "<td rowspan=1 class=\"itemresult\"></td>";
	echo "</tr>";
	
	echo "<tr class=odd>";
	
	echo "<td colspan=6 class=\"itemmeta\">On Appro</td>";
	for ($j=1;$j<17;$j++)
	{
		if ($item['size'.$j]<>"")
		{
			echo "<td class=\"itemresult appro\">".$stock['appro'.$j]."</td>";
		}
		else
		{
			echo "<td class=\"itemresult nosize\"></td>";
		}	
	}
	echo "<td rowspan=1 class=\"itemresult\"></td>";
	echo "<td rowspan=1 class=\"itemresult\"></td>";
	echo "<td rowspan=1 class=\"itemresult\"></td>";
	echo "</tr>";
	echo "<tr><td colspan=36 class=itemsep></td></tr>";
	$g++;
}

echo "</table>";
?>

<script type="text/javascript">
function selectCust(cust)
{
	$('#custresult').slideUp();
	$('#custdetail').load('./customer/custDetail.php?cust='+cust);
	$('#custsearch').load('./customer/search.php');
}

function addItem(sku,colour,size, nonstock)
{
	$('#item').val('');
	$('#itemsearchresults').slideUp('fast');
	var price=$('#nonstockprice').val();
	var urlsku=encodeURIComponent(sku);
	var urlcol=encodeURIComponent(colour);
	if (nonstock==1 && !price)
	{
	    $('#dialog').append('<div id=temp></div>');
	    $('#dialog').css('top','0%');
	    $('#dialog').css('left','50%');
	    $('#dialog').css('margin-left','-5%');
		$('#temp').load('./order/bagContents.php?action=price&sku='+urlsku+'&colour='+urlcol+'&sizeindex='+size+'&nonstock='+nonstock);
		$('#dimmer').show();
		$('#dialog').show();
	}
	else if (nonstock==1 && price!="")
	{
		$('#temp').remove();
		$('#dialog').hide();
		$('#dimmer').hide();
		$('#bagitems').load('./order/bagContents.php?action=add&sku='+urlsku+'&colour='+urlcol+'&sizeindex='+size+'&nonstock='+nonstock+'&price='+price);
	}
	else
	{
		$('#bagitems').load('./order/bagContents.php?action=add&sku='+urlsku+'&colour='+urlcol+'&sizeindex='+size+'&nonstock='+nonstock);
		$('#dimmer').hide();
	}
}
</script>
