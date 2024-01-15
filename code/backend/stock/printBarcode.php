<?php

include '../config.php';
include '../functions/auth_func.php';
include '../functions/field_func.php';
include '../functions/stock_func.php';
include '../functions/print_func.php';
include '../functions/barcode_func.php';
require_once '../../pos/functions/dompdf/dompdf_config.inc.php';

$db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);
$db_conn2=mysqli_connect($db_host,$db_username, $db_password, $db_name);

#Work out if I am called from my menu option or from the stockcard
$sku=$_REQUEST['sku'];
$action=$_REQUEST['action'];
$colour=$_REQUEST['colour'];
$brand=$_REQUEST['brand'];
$season=$_REQUEST['season'];


if ($sku=="" && $action=="")
{
    # Echo out the sku entry field
    echo "<h2>Product Search</h2>";
    echo "<p style=\"width:100%;text-align:right;\"></p>";
    echo "<script type=text/javascript>$('button').button();</script>";
    echo "<table><tr>";
    echo "<td>SKU</td><td>Brand</td><td>Season</td><td>Type</td></tr>";
    
    $brands=getSelect('brands',0);
    $seasons=getSelect('seasons',0);
    $colours=getSelect('colours',0);
    $category=getSelect('category',0);
    
    echo "<tr>
		<td><input  onkeyup=\"javascript:searchItem();\" autocomplete=\"off\" id=sku /></td>
		<td><select onchange=\"javascript:searchItem();\" id=brand >$brands</select></td>
		<td><select onchange=\"javascript:searchItem();\" id=season >$seasons</select></td>
		<td><select onchange=\"javascript:searchItem();\" id=category>$category</select></td>
		</tr>";
    echo "<tr style=\"height:10px;\"><td colspan=10></td></tr>";
    echo "</table>";
    
    
    echo "<div id=searchresultssc></div>";
    echo "<div id=message></div>";
    echo "<div id=temp2></div>";
    echo "<div id=dialog></div>";
    if ($_REQUEST['close']=="close")
    {
        echo "<p width=100% align=right><button onclick=\"location.reload();\">Close</button>";
    }
    
}
elseif ($action=="results")
{
    
    $sql_query="select style.sku
	, brands.nicename
	, seasons.season
	, style.description
    , stock.costprice
    , stock.retailprice
	, category.nicename category
	from stock, styleDetail, sizes, style, brands, seasons, category
	where 1=1
	and styleDetail.sku=stock.Stockref
	and stock.company=style.company
	and stock.company=styleDetail.company
	and styleDetail.category=category.id
	and stock.Stockref=style.sku
	and styleDetail.brand=brands.id
	and styleDetail.season=seasons.id
	and style.sizekey=sizes.sizekey
	and style.sku like '".$sku."%'";
    
    if ($brand<>"")
    {
        $sql_query.="and brands.id like ".$brand." ";
    }
    
    if ($season<>"")
    {
        $sql_query.=" and seasons.id =  ".$season." ";
    }
    
    
    if ($category<>"")
    {
        $sql_query.=" and category.id = ".$category." ";
    }
    
    $sql_query.=" group by style.sku
	, brands.nicename
	, seasons.season
	, style.description
	, category.nicename
    , stock.costprice
    , stock.retailprice
    order by seasons.season desc, style.sku asc";
 
    $results=$db_conn->query($sql_query);
    echo "<table class=itemsearchtable>";
    echo "<tr class=itemheader><td class=itemheader>SKU</td><td class=itemheader>Brand</td><td class=itemheader>Season</td><td class=itemheader>Type</td><td class=itemheader>Description</td><td>Retail</td></tr>";
    echo "<tr style=\"height:10px;\" class=itemheader><td style=\"height:10px;\" colspan=7></td></tr>";
    
    while ($result=mysqli_fetch_array($results))
    {
        if ($_REQUEST['launch']=="card")
        {
            echo "<tr onclick=\"javascript:select3('".$result['sku']."');\"><td>".$result['sku']."</td><td>".$result['nicename']."</td><td>".$result['season']."</td><td>".$result['category']."</td><td>".$result['description']."</td>
                <td>".$result['retailprice']."</td></tr>";
        }
        else 
        {
            echo "<tr onclick=\"javascript:select2('".$result['sku']."');\"><td>".$result['sku']."</td><td>".$result['nicename']."</td><td>".$result['season']."</td><td>".$result['category']."</td><td>".$result['description']."</td>
                <td>".$result['retailprice']."</td></tr>";
        }
        }
    echo "</table>";
}
elseif ($sku<>"" && $action=="")
{
    # echo out the variant
    $sql_query="select style.sku, stock.colour, size1, size2, size3, size4, size5, size6, size7, size8, size9, size10, size11, size12, size13, size14, size15, size16, size17, size18, size19, size20
		from sizes, style, stock
		where style.company =1
		and style.sizekey = sizes.sizekey
		and style.sku= stock.Stockref
		and style.sku = '".$sku."'";
    $results=$db_conn->query($sql_query);
    echo "<div id=barcodes>";
    echo "<table><tr><td>Product</td><td colspan=10>Sizes</td></tr>";
    echo "<tr style=\"height:10px;\"><td colspan=2></td></tr>";
    
    
    while ($result=mysqli_fetch_array($results))
    {
        echo "<tr><td id=\"".$result['colour']."\" onclick=\"javascript:populateStock('".$sku."','".$result['colour']."'); \">".$result['colour']."</td>";
        for ($i=1;$i<=22;$i++)
        {
            echo "<td>".$result['size'.$i]."</td>";
            if ($result['size'.$i]=="")
            {
                $j=$i;
                $i=100;
            }
        }
        
        echo "</tr><tr style=\"height:15px;\"><td style=\"width:95px;\">Qty</td>";
        
        for ($k=1;$k<$j;$k++)
        {
            echo "<td><input style=\"width:40px;\" type=text name='".$result['colour']."-".$k."' ></td>";
        }
        
        echo "</tr>";
        echo "<tr style=\"height:5px;\"><td colspan=20></td></tr>";
        
    }
    echo "<tr><td colspan=".$j."><hr></td></tr></table></div>";
    echo "<div id=stock><table>";
    echo "<tr><td style=\"width:95px;\">Stock Levels</td>";
    for ($k=1;$k<$j;$k++)
    {
        echo "<td><input style=\"width:40px;\" type=text id='stock-".$k."' ></td>";
    }
    echo "<tr style=\"height:10px;\"><td colspan=20></td></tr>";
    echo "</table></div>";
    
    echo "<p><label><input style=\"width:15px;height:17px;\" type=checkbox name=salecheck>Sale prices?</label></p>
    <p align=right><span align=right><button onclick=\"javascript:import_stock();\">Current Stock</button>";
    if ($_REQUEST['launch']=="card")
    {
        echo "<button onclick=\"javascript:printBarcode3('".$sku."');\">Print</button>";
    }
    else 
    {
        echo ":<button onclick=\"javascript:printBarcode2('".$sku."');\">Print</button>";
    }
    
    echo "<button onclick=\"javascript:closeDialog();\">Close</button></span></p>";
}

elseif ($action=="print")
{
    #Parse the request
    $sql_query="select style.sku, stock.colour, size1, size2, size3, size4, size5, size6, size7, size8, size9, size10, size11, size12, size13, size14, size15, size16, size17, size18, size19, size20, retailprice, saleprice
		from sizes, style, stock
		where style.company =1
		and style.sizekey = sizes.sizekey
		and style.sku= stock.Stockref
		and style.sku = '".$sku."'";
    $results=$db_conn->query($sql_query);
    
    while ($result=mysqli_fetch_array($results))
    {
        for ($i=1;$i<=21;$i++)
        {
            if ($_REQUEST[$result['colour']."-".$i]<>"")
            {
                
                for ($u=1;$u<=$_REQUEST[$result['colour']."-".$i];$u++)
                {
                    #Execute function to print the number requested
                    if ($_REQUEST['salecheck']==1)
                    {
                        $price=$result['saleprice'];
                    }
                    else
                    {
                        $price=$result['retailprice'];
                    }
                    printBarcode($sku, $result['colour'], $result['size'.$i], $i,$price);
                }
                
                echo "<script type=text/javascript>
	$('#dimmer').hide();
    $('#temp').remove();   
    $('#dialog').hide();
</script>";
            }
            
        }
        
    }
    
}

elseif ($action=="printall")
{
    $stock=stockBalance($_REQUEST['sku'], $_REQUEST['colour']);
    $price=getItemPrice($sku);
    for ($i=1;$i<21;$i++)
    {
        if ($stock['physical'.$i]>0)
        {
            $size=getItemSize($i, $_REQUEST['sku']);
            for ($j=1;$j<=$stock['physical'.$i];$j++)
            {
                printBarcode($_REQUEST['sku'], $_REQUEST['colour'], $size, $i,$price['price']);
            }
        }
    }
}

elseif ($action=="stock")
{
    $sku=$_REQUEST['sku'];
    # echo out the variant
    $sql_query="select style.sku, stock.colour, size1, size2, size3, size4, size5, size6, size7, size8, size9, size10, size11, size12, size13, size14, size15, size16, size17, size18, size19, size20
		from sizes, style, stock
		where style.company =1
		and style.sizekey = sizes.sizekey
		and style.sku= stock.Stockref
        and stock.colour = '".$_REQUEST['colour']."'
		and style.sku = '".$sku."'";
    $results=$db_conn->query($sql_query);
    echo "<table><tr><td style=\"width:95px;\">Stock Levels</td>";
    
    $stock=stockBalance($sku, $_REQUEST['colour']);
    while ($result=mysqli_fetch_array($results))
    {
        for ($i=1;$i<=22;$i++)
        {
            if ($result['size'.$i]=="")
            {
                $j=$i;
                $i=100;
            }
            else
            {
                echo "<td style=\"width:40px;\"><input id=\"".$result['colour']."-$i\" style=\"width:40px;\" type=text  value=".$stock['physical'.$i]."></td>";
            }
        }
        echo "</tr></table>";
    }
}

?>

<Script type="text/javascript">

$(document).ready(function(){
		$('button').button();
});


function closeDialog()
{
	$('#temp').remove();
	$('#dialog').hide();
	$('#dimmer').hide();
}

function printBarcode(sku)
{
    $('#dialog').html('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','39%');
    $('#dialog').css('margin-left','-14%');
	$('#temp').load('./stock/printBarcode.php?sku='+sku);
	$('#dimmer').show();
	$('#dialog').show();
}

function printBarcode2(sku)
{
	var getString="action=print&sku="+sku+"&";
	$('#temp2 input[type!=checkbox]').each(function(){
		if (this.value!="")
		{
		getString=getString+this.name+"="+encodeURIComponent(this.value);
		getString=getString+'&';
		}
	});

	 $('input[name=salecheck]').each(function(){
                if(this.checked)
                {
                        getString=getString+this.name+"=1";
                }
                else
                {
                        getString=getString+this.name+"=0";
                }
        });

	$('#dialog').load('/backend/stock/printBarcode.php?'+getString);
	$('#output').load('/backend/stock/printBarcode.php?');
}

function printBarcode3(sku)
{
	var getString="action=print&sku="+sku+"&";
	$('#dialog').append('<div id=temp></div>');
	$('#temp input[type!=checkbox]').each(function(){
		if (this.value!="")
		{
		getString=getString+encodeURIComponent(this.name)+"="+encodeURIComponent(this.value);
		getString=getString+'&';
		}
	});

	 $('input[name=salecheck]').each(function(){
                if(this.checked)
                {
                        getString=getString+this.name+"=1";
                }
                else
                {
                        getString=getString+this.name+"=0";
                }
        });

	$('#dialog').load('/backend/stock/printBarcode.php?'+getString);
	$('#dimmer').hide();
	$('#dialog').hide();
	$('#temp').remove();
}


function populateStock(sku,colour)
{
	$('#'+colour).css('color','#ff0000');
	$('#stock').load('/backend/stock/printBarcode.php?action=stock&sku='+sku+'&colour='+colour);
}

function import_stock()
{
	$('#stock input[type=text]').each(function()
	{
		var stk=$(this).val();
		var id=$(this).attr('id');
		$("#barcodes input[name='"+id+"']").val(stk);
	});
}

function select2(value)
{
	$('#searchresultssc').hide();
	$('#sku').val(value);
	var search2=encodeURIComponent(value);
	$('#temp2').show();
	$('#temp2').load('/backend/stock/printBarcode.php?action=&sku='+search2);
}

function select3(value)
{
	$('#dialog').append('<div id=temp></div>');
	$('#searchresultssc').hide();
	$('#sku').val(value);
	var search2=encodeURIComponent(value);
	$('#dialog').show();
	$('#temp').show();
	$('#temp').load('/backend/stock/printBarcode.php?action=&sku='+search2);
}

function searchItem()
{
	$('#searchresultssc').show();
	var brand=$('#brand').val();
	var season=$('#season').val();
	var category=$('#category').val();
	var sku=encodeURIComponent($('#sku').val());
	var colour=$('#colour').val();
	var pricefrom=$('#pricefrom').val();
	var priceto=$('#priceto').val();

	if (sku.length>0)
	{
		$('#add').button({
			disabled: false
		});
	}
	if (sku.length>2 || brand!='' || season!='' || category!='' )
	{
		$('#searchresultssc').load('/backend/stock/printBarcode.php?action=results&brand='+brand+'&season='+season+'&sku='+sku+'&colour='+colour+'&category='+category+'&pricefrom='+pricefrom+'&priceto='+priceto);
	}
}
</Script>
