<?php 

include '../config.php';
include '../functions/auth_func.php';
include '../functions/field_func.php';
include '../functions/stock_func.php';
include '../functions/barcode_func.php';
session_start();
$code=urldecode($_REQUEST['barcode']);
$brand=urldecode($_REQUEST['brand']);
$season=urldecode($_REQUEST['season']);
$category=urldecode($_REQUEST['category']);
$sku=urldecode($_REQUEST['sku']);
$colour=urldecode($_REQUEST['colour']);
$pricefrom=urldecode($_REQUEST['pricefrom']);
$priceto=urldecode($_REQUEST['priceto']);


$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$new=0;
if ($action=="")
{
echo "<p style=\"width:100%;text-align:right;\">
    <table width=100%><tr><td><h2>Product Search</h2></td><td><button align=right id=add onclick=\"javascript:addSku();\">New</button></td></tr></table></p>";
echo "<script type=text/javascript>$('button').button();</script>";
echo "<table><tr>";
echo "<td>SKU</td><td>Brand</td><td>Season</td><td>Colour</td><td>Type</td></tr>";

$brands=getSelect('brands',0);
$seasons=getSelect('seasons',0);
$colours=getSelect('colours',0);
$category=getSelect('category',0);

echo "<tr>
		<td><input  onkeyup=\"javascript:searchItem2();\" id=sku /></td>
		<td><select onchange=\"javascript:searchItem2();\" id=brand >$brands</select></td>
		<td><select onchange=\"javascript:searchItem2();\" id=season >$seasons</select></td>
        <td><select onchange=\"javascript:searchItem2();\" id=colour >$colours</select></td>
		<td><select onchange=\"javascript:searchItem2();\" id=category>$category</select></td>
		</tr>";
echo "<tr style=\"height:10px;\"><td colspan=10></td></tr>";
echo "</table>";
	
	
	echo "<div id=searchresultssc></div>";
	echo "<div id=updater></div>";
	echo "<div id=message></div>";

}

elseif ($action=="password")
{
    include '../config.php';
    include './auth_func.php';
    echo "<h2>Enter Password</h2>";
    echo "<p width=100%><input type=password id=passwd /></p>";
    echo <<<EOF
<script type=text/javascript>
$('#passwd').keypress(function (e) {
	  if (e.which == 13) {
		var term=encodeURIComponent($('#sku').val());
		var passwd=$('#passwd').val();
		if (passwd=='BLUE')
		{
        		$('#dimmer').hide();
        		$('#dialog').hide();
        		$('#message').load('./stock/editStockcard.php?action=delete&term='+term);
        	    return false;    //<---- Add this line
		}
	  }
});
</script>
EOF;
    exit();
}

elseif ($action=="delvariant")
{
	$sql_query="select count(*) cnt from orderdetail where Stockref = '".$_REQUEST['sku']."' and colour= '".$_REQUEST['colour']."'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	
	if ($result['cnt']>0)
	{
		echo "<script type=text/javascript>alert('You have sold these. Not deleted');</script>";
		getColourways($_REQUEST['sku']);
		exit();
	}
	else
	{
		#We can delete
		$sql_query="delete from stock where Stockref = '".$_REQUEST['sku']."' and colour= '".$_REQUEST['colour']."'";
		$doit=$db_conn->query($sql_query);
		getColourways($_REQUEST['sku']);
	}
}

elseif ($action=="addcolour")
{
	if ($_REQUEST['new']==1)
	{
		$sql_query="select count(*) cnt from stock where Stockref = '".$_REQUEST['sku']."'";
		$results=$db_conn->query($sql_query);
		$result=mysqli_fetch_array($results);
		if ($result['cnt']>0)
		{
			
			echo "<script type=text/javascript>alert('Already exists');</script>";
			echo "<script type=text/javascript>$('#updater').load('./stock/editStockcard.php?action=add');</script>";
			exit();
		}
	}
	
	if ($_REQUEST['colour'])
	{
		addColourway($_REQUEST['sku'], $_REQUEST['colour'], $_REQUEST['costprice'], $_REQUEST['retailprice']);
	}
	getColourways($_REQUEST['sku']);
	exit();
}

elseif ($action=="addsaleprice")
{
    if ($_REQUEST['saleprice']=='')
    {
        $sql_query="update stock set saleprice=NULL where Stockref = '".$_REQUEST['sku']."' and colour = '".$_REQUEST['colour']."'";
        $feedback="Sale Price removed";
    }
    else 
    {
	   $sql_query="update stock set saleprice=".$_REQUEST['saleprice']." where Stockref = '".$_REQUEST['sku']."' and colour = '".$_REQUEST['colour']."'";
	   $feedback="Sale Price updated";
    }
    
	$do_it=$db_conn->query($sql_query);
	echo "<h2>".$feedback."</h2>";
	exit();
}

elseif ($action=="add")
{
	$new=1;
	$action="select";
}
elseif ($action=="results")
{

$sql_query="select style.sku
	, brands.nicename
	, seasons.season
	, style.description
    , stock.costprice
    , colours.colour
    , stock.retailprice
	, category.nicename category
	from stock, styleDetail, sizes, style, brands, seasons, category, colours
	where 1=1
	and styleDetail.sku=stock.Stockref
	and stock.company=style.company
	and stock.company=styleDetail.company
	and styleDetail.category=category.id
	and stock.Stockref=style.sku
	and styleDetail.brand=brands.id
	and styleDetail.season=seasons.id
	and style.sizekey=sizes.sizekey
    and stock.colour = colours.colour
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
		
		if ($colour<>"")
		{
		    $sql_query.=" and colours.id = ".$colour." ";
		}

		$sql_query.=" group by style.sku
    , colours.colour
	, brands.nicename
	, seasons.season
	, style.description
	, category.nicename 
    , stock.costprice
    , stock.retailprice
	order by style.sku asc";
		
	$results=$db_conn->query($sql_query);
	echo "<table class=itemsearchtable>";
	echo "<tr class=itemheader><td class=itemheader>SKU</td><td class=itemheader>Colour</td><td class=itemheader>Brand</td><td class=itemheader>Season</td><td class=itemheader>Type</td><td class=itemheader>Description</td><td>Cost</td><td>Retail</td></tr>";
	echo "<tr style=\"height:10px;\" class=itemheader><td style=\"height:10px;\" colspan=7></td></tr>";
	
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr onclick=\"javascript:select('".$result['sku']."');\"><td>".$result['sku']."</td><td>".$result['colour']."</td><td>".$result['nicename']."</td><td>".$result['season']."</td><td>".$result['category']."</td><td>".$result['description']."</td>
            <td>".$result['costprice']."</td><td>".$result['retailprice']."</td></tr>";
	}
	echo "</table>";
}
elseif ($action=="select")
{

	$term=$_REQUEST['term'];
	if ($term<>"")
	{	
		$sql_query="select styleDetail.sku, styleDetail.description, styleDetail.season, styleDetail.brand, styleDetail.Productgroup, styleDetail.category
			, s.vatkey, s.sizekey, s.onsale	, styleDetail.nonstock, sto.costprice, sto.retailprice
			from styleDetail, style s, stock sto 
			where styleDetail.sku=s.sku 
			and sto.Stockref = styleDetail.sku
			and styleDetail.sku = '".$term."' and s.company = ".$_SESSION['CO'];
	
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);
	}
}


elseif ($action == "enableweb")
{
    $sql_query="update stock set web_status = 1 where Stockref = '".$_REQUEST['sku']."' and colour = '".$_REQUEST['colour']."'";
    echo $sql_query;
    $do_it=$db_conn->query($sql_query);
    
}

elseif ($action == "recycleweb")
{
    $sql_query="update stock set web_complete = 0, web_uploaded=0 where Stockref = '".$_REQUEST['sku']."' and colour = '".$_REQUEST['colour']."'";
    echo $sql_query;
    $do_it=$db_conn->query($sql_query);   
}

elseif ($action=="save")
{

	if ($_REQUEST['new']=="1")
	{
		#StyleDetail first
		$setClause="'".$_REQUEST['sku']."',\"".$_REQUEST['description']."\",\"".$_REQUEST['Productgroup']."\",\"".$_REQUEST['category']."\",\"".$_REQUEST['season']."\",\"".$_REQUEST['brand']."\"";
		$setClause.=",\"".$_SESSION['CO']."\",\"".$_REQUEST['nonstock']."\"";

		$sql_query="insert into styleDetail (sku, description, ProductGroup, category, season, brand, company, nonstock)
			values(".$setClause.")";
		$result=$db_conn->query($sql_query);
		
		#Style last
		$barcode=getNextBarcode();

		$setClause="'".$_REQUEST['sku']."',\"".$_SESSION['CO']."\",\"".$_REQUEST['description']."\",\"".$_REQUEST['sizekey']."\",\"".$_REQUEST['vatkey']."\"";
		$setClause.=",\"".$_REQUEST['onsale']."\", $barcode)";
		$sql_query="insert into style (sku, company, description, sizekey, vatkey, onsale, barcode) values (";
		$sql_query.=$setClause;
		$result=$db_conn->query($sql_query);	
	}
	else
	{
		#Styledetail first
		$setClause=" sku='".$_REQUEST['sku']."',description=\"".$_REQUEST['description']."\",ProductGroup=\"".$_REQUEST['Productgroup']."\",category=\"".$_REQUEST['category']."\",season=\"".$_REQUEST['season']."\"";
		$setClause.=" ,brand=\"".$_REQUEST['brand']."\", nonstock=\"".$_REQUEST['nonstock']."\", company=".$_SESSION['CO'];
		$sql_query="update styleDetail set ";
		$sql_query.=$setClause;
		$sql_query.=" where sku='".$_REQUEST['sku']."'";
		$result=$db_conn->query($sql_query);
		#style last
		$setClause=" sku='".$_REQUEST['sku']."',description=\"".$_REQUEST['description']."\",sizekey=\"".$_REQUEST['sizekey']."\"";
		$setClause.=" ,vatkey=\"".$_REQUEST['vatkey']."\" , company=".$_SESSION['CO'];
		if ($_REQUEST['onsale']=="1")
		{
			$setClause.=" , onsale=1";
		}
		else 
		{
			$setClause.=" , onsale=0";
		}
		$sql_query="update style set ";
		$sql_query.=$setClause;
		$sql_query.=" where sku='".$_REQUEST['sku']."'";
		$result=$db_conn->query($sql_query);
		$sql_query="update stock set costprice=".$_REQUEST['costprice'].", retailprice=".$_REQUEST['retailprice'];

		$sql_query.=" where Stockref = '".$_REQUEST['sku']."'";
		$do_it=$db_conn->query($sql_query);
		

	}
	
	//echo "<script type=text/javascript>$('#output').load('./stock/editStockcard.php?message=yes&action=select&term=".$_REQUEST['sku']."');</script>";
}

elseif ($action=="delete")
{	
		$term=$_REQUEST['term'];
		$sql_query="delete from style where sku = '".$term."'";
	
		$do_it=$db_conn->query($sql_query);
		
		$sql_query="delete from styleDetail where sku = '".$term."'";
		$do_it=$db_conn->query($sql_query);
		
		$sql_query="delete from stock where StockRef = '".$term."'";
		$do_it=$db_conn->query($sql_query);
		
		echo "<p class=message>Size Deleted</p>";
}


if ($action=="select" )
{
$category=getSelect('category', $detail['category']);
$Productgroup=getSelect('Productgroup', $detail['Productgroup']);
$seasons=getSelect('seasons',$detail['season']);
$brand=getSelect('brands', $detail['brand']);
$sizekey=getSelect('sizekey', $detail['sizekey']);
$vatkey=getSelect('vatkey',$detail['vatkey']);
$colours=getSelect('colours2','');

# Draw up table for record
echo "<p width=100%><hr><br><br></p>";
echo "<div id=stockform>";
echo "<div id=style>";
echo "<table>";
echo "<tr><td>Product Code</td><td align=left colspan=3><input style=width:445px; name=sku onblur='javascript:newsku();' type=text";
if ($detail['sku']=="") {echo "disabled";}
echo " value='".$detail['sku']."' /><input type=hidden name=new value=$new /></td></tr>";
echo "<tr><td>Description</td><td colspan=3><input style=width:445px; type=text name=description value='".$detail['description']."' /></td></tr>";
echo "<tr><td>Product Group</td><td><select name=Productgroup>".$Productgroup."</select></td><td>Season</td><td><select name=season>".$seasons."</season></td></tr>";
echo "<tr><td>Category</td><td><select name=category>".$category."</select></td><td>Brand</td><td><select name=brand>".$brand."</select></td></tr>";
echo "<tr><td>Vat Rate</td><td><select name=vatkey>".$vatkey."</select></td><td>Cost Price</td><td><input type=text name=costprice value='".$detail['costprice']."'/></td></tr>";
echo "<tr><td>Size Type</td><td><select name=sizekey>".$sizekey."</select></td><td>Retail Price</td><td><input type=text name=retailprice value='".$detail['retailprice']."'/></td></tr>";

echo "<td>Onsale</td><td  style=\"text-align:left;\"><input style=\"width:20px;\" type=checkbox name='onsale' value='1'";
if ($detail['onsale']==1)
{
	echo " checked ";
	
}
echo "/></td><td>Non-Stock</td><td   style=\"text-align:left;\" ><input style=\"width:20px;\" type=checkbox name='nonstock' value='1'";
if ($detail['nonstock']==1)
{
	echo " checked ";
	
}
echo "</td></tr>";
echo "</table>";
echo "</div></div>";




echo "<div id=colform>";
if ($new<>1)
{	
	getColourways($_REQUEST['term']);
}

echo "</div>";
echo "<p width=100%><br></p>";
echo "<table width=100%>";
echo "<tr><td align=left><button id=movements >Movements</button>
		<button onclick=\"javascript:printBarcode($('input[name=sku]').val());\" >Barcodes</button>
		</td><td align=right>";
echo "<button id=savebut ";
if ($new==1)
{
	echo " disabled ";
}
	echo "onclick=\"javascript:subForm();\" >Save</button>";
	echo "<button onclick=\"javascript:delterm('".$_REQUEST['term']."');\" >Delete</button><button onclick=\"javascript:location.reload();\" >Close</button></td></tr>";
echo "</table>";
echo "<br>";

#Output Stock Holding
echo "<div id=stockholding>";
echo "<h2>Stock Holding</h2>";
echo "<div>";
$sql_query="select sizekey from style where sku = '".$_REQUEST['term']."'";
$sizekeys=$db_conn->query($sql_query);
$sizekey=mysqli_fetch_array($sizekeys);

$sizes=getSizeArray($sizekey['sizekey']);


echo "<ul style=\"display:table-row;width:80%;height:40px;\"><li style=\"width:150px;text-align:left;\" class=stocktitle>Colour</li><li style=\"text-align:left;\" class=stocktitle></li>";
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
	echo "<ul style=\"display:table-row;width:80%;\"><li style=\"text-align:left;\" class=stockitem>".$colour['colour']."</li><li style=\"text-align:left;\" class=stockitem>Stock</li>";
	$stock=stockBalance($detail['sku'], $colour['colour'],'');
	$phystock="";
	$approstock="<li style=\"text-align:left;\" class=stockitem></li><li style=\"text-align:left;\" class=stockitem>OnAppro</li>";
	for ($j=1;$j<$num_sizes;$j++)
	{
		$phystock.="<li class=stockitem><input align=center style=\"width:40px;text-align:center;\" type=text value=".$stock['physical'.$j]."></input></li>";
		$approstock.="<li class=stockitem><input align=center style=\"width:40px;text-align:center;\" type=text value=".$stock['appro'.$j]."></input></li>";
	}
	echo $phystock;
	echo "</ul>";
	echo $approstock;
	echo "</ul>";
}

echo "</div>";

#Output Audit history
echo "<h2>Audit History</h2>";
echo "<div>";

$sql_query="select colour, forename, lastname, size, qty, date_format(saletime,'%d/%m/%Y    %H:%i') datetime, Status, if(abs(actualgrand)>0, actualgrand, grandTot) price, grandTot-actualgrand discount
from 
(
select od.colour colour, c.forename forename, c.lastname lastname
, ELT(FIELD(od.status,'A', 'X', 'C', 'J', 'K','S'),'OnAppro','OnAppro Return','Sale/Return','Sale/Return','Sale/Return', 'Sale/Return') Status
, od.timestamp saletime, abs(qty) qty, size , od.actualgrand, od.grandtot
from orderdetail od, customers c, orderheader oh
where stockref = '".$detail['sku']."'
and c.custid = oh.custref
and od.transno = oh.transno
and od.status in ('A', 'X', 'C', 'J', 'K','S')
union
select stk.colour colour, 'Stock' forename, rea.nicename lastname, stk.Reference,stk.datetrack saletime, stk.qty*-rea.polarity qty,
case stk.sizeid 
when 1 then size1
when 2 then size2
when 3 then size3
when 4 then size4
when 5 then size5
when 6 then size6
when 7 then size7
when 8 then size8
when 9 then size9
when 10 then size10
when 11 then size11
when 12 then size12
when 13 then size13
when 14 then size14
when 15 then size15
when 16 then size16
when 17 then size17
when 18 then size18
when 19 then size19
when 20 then size20
 END size
,'',''
from stkadjustments stk, stkadjreason rea, sizes s, style st
where stk.reasonid = rea.id
and st.sizekey=s.sizekey
and st.sku = '".$detail['sku']."'
and st.sku = stk.sku
union
select sto.colour, 'Stock' forename, 'Import' lastname,  concat('Receipt - ',sto.delnote), sto.delDate
,CASE 
    WHEN nums.ROW=1 THEN sto.physical1
    WHEN nums.ROW=2 THEN sto.physical2
    WHEN nums.ROW=3 THEN sto.physical3
    WHEN nums.ROW=4 THEN sto.physical4
    WHEN nums.ROW=5 THEN sto.physical5
    WHEN nums.ROW=6 THEN sto.physical6
    WHEN nums.ROW=7 THEN sto.physical7
    WHEN nums.ROW=8 THEN sto.physical8
    WHEN nums.ROW=9 THEN sto.physical9
    WHEN nums.ROW=10 THEN sto.physical10
    WHEN nums.ROW=11 THEN sto.physical11
    WHEN nums.ROW=12 THEN sto.physical12
    WHEN nums.ROW=13 THEN sto.physical13
    WHEN nums.ROW=14 THEN sto.physical14
    WHEN nums.ROW=15 THEN sto.physical15
    WHEN nums.ROW=16 THEN sto.physical16
    WHEN nums.ROW=17 THEN sto.physical17
    WHEN nums.ROW=18 THEN sto.physical18
    WHEN nums.ROW=19 THEN sto.physical19
    WHEN nums.ROW=20 THEN sto.physical20
END physical
, CASE 
    WHEN nums.ROW=1 THEN sizes.size1
    WHEN nums.ROW=2 THEN sizes.size2
    WHEN nums.ROW=3 THEN sizes.size3
    WHEN nums.ROW=4 THEN sizes.size4
    WHEN nums.ROW=5 THEN sizes.size5
    WHEN nums.ROW=6 THEN sizes.size6
    WHEN nums.ROW=7 THEN sizes.size7
    WHEN nums.ROW=8 THEN sizes.size8
    WHEN nums.ROW=9 THEN sizes.size9
    WHEN nums.ROW=10 THEN sizes.size10
    WHEN nums.ROW=11 THEN sizes.size11
    WHEN nums.ROW=12 THEN sizes.size12
    WHEN nums.ROW=13 THEN sizes.size13
    WHEN nums.ROW=14 THEN sizes.size14
    WHEN nums.ROW=15 THEN sizes.size15
    WHEN nums.ROW=16 THEN sizes.size16
    WHEN nums.ROW=17 THEN sizes.size17
    WHEN nums.ROW=18 THEN sizes.size18
    WHEN nums.ROW=19 THEN sizes.size19
    WHEN nums.ROW=20 THEN sizes.size20
END size
, sto.retailprice ,sto.retailprice
from stock sto, sizes, style s,
(SELECT @ROW := @ROW + 1 AS ROW
 FROM orderdetail t
 join (SELECT @ROW := 0) t2
 LIMIT 20
 ) nums
where s.sku = sto.StockRef
and s.sizekey = sizes.sizekey
and s.sku = '".$detail['sku']."'
) theunion
order by saletime desc, colour, size"
;


$results=$db_conn->query($sql_query);
echo "<table class=audit><tr><td>Date Time</td><td>Colour</td><td>Comment</td><td>Size</td><td>Qty</td><td>Sell Price</td><td>Discount</td><td>Name</td></tr>";
echo "<tr><td colspan=9 style=\"height:10px;\"></td></tr>";
while ($result=mysqli_fetch_array($results))
{
	if ($result['qty']<>0)
	{
	    echo "<tr><td>".$result['datetime']."</td><td>".$result['colour']."</td><td>".$result['Status']."</td><td>".$result['size']."<td>".$result['qty'].
			"<td>&pound;".number_format($result['price'],2)."</td><td>&pound;".number_format($result['discount'],2)."</td>
            <td>".$result['forename']." ".$result['lastname']."</td></tr>";
	}
}

echo "</table>";
echo "</div></div>";
}
?>
<script type="text/javascript">

$(document).ready(function(){
			$('button').button();
			$('#stockholding').accordion({ heightStyle: "content" });			
	});

function srch(value)
{
	$('#searchresultssc').show();
	var search=encodeURIComponent(value);
	$('#searchresultssc').load('./stock/editStockcard.php?action=results&term='+search);
}

function select(value)
{
	$('#searchresultssc').hide();
	$('#sku').val(value);
	var search2=encodeURIComponent(value);
	$('#updater').load('./stock/editStockcard.php?action=select&term='+search2);
}

function addSku(value)
{
	$('#searchresultssc').hide();
	var search2=$('#sku').val();
	$('#updater').load('./stock/editStockcard.php?action=add');
}


function delterm(term)
{
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','20%');
    $('#dialog').css('left','50%');
    $('#dialog').css('margin-left','-14%');
	$('#temp').load('./stock/editStockcard.php?action=password');
	$('#dimmer').show();
	$('#dialog').show();
	$('#temp').show();
	
}

function subForm()
{
	var problem=0;
	$('input[name=costprice],input[name=retailprice],input[name=description],select[name=Productgroup],select[name=category],select[name=vatkey],select[name=sizekey],select[name=season],select[name=brand]').each(function(){
		if (this.value=="")
		{
			problem=1;
		}
	});
	if (problem==1)
	{
		$('input[name=costprice],input[name=retailprice],input[name=description],select[name=Productgroup],select[name=category],select[name=vatkey],select[name=sizekey],select[name=season],select[name=brand]').css('border','solid 3px #ff0000');
		alert('All highlighted fields MUST be filled in');
	}
	else
	{
		$('#id').val($('input[name=sku]').val());
		var getString="action=save&";
		$('input[type!=checkbox]').each(function(){
			if (this.value!="")
			{
			getString=getString+this.name+"="+encodeURIComponent(this.value);
			getString=getString+'&';
			}
		});
			$('select').each(function(){
				if (this.value!="")
				{
				getString=getString+this.name+"="+encodeURIComponent(this.value);
				getString=getString+'&';
				}
	
		});
		$('input[type=checkbox]').each(function(){
			if(this.checked)
			{
				getString=getString+this.name+"=1&";
			}
			else
			{
				getString=getString+this.name+"=0&";
			}
		});
		$('#message').load('./stock/editStockcard.php?'+getString);
	}
}

function stkAdj(sku,variant,value, sizeid)
{
         $('#dialog').append('<div id=temp></div>');
                 $('#dialog').css('top','20%');
                 $('#dialog').css('left','50%');
                 $('#dialog').css('margin-left','-14%');
         $('#temp').load('./stock/stockAdj.php?action=reason&sku='+sku+'&variant='+variant+'&value='+value+'&sizeid='+sizeid);
         $('#dimmer').show();
         $('#dialog').show();
}

function createVariant(sku,sizekey)
{
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','20%');
    $('#dialog').css('left','50%');
    $('#dialog').css('margin-left','-30%');
$('#temp').load('./stock/addVariant.php?sku='+sku+'&sizekey='+sizekey);
$('#dimmer').show();
$('#dialog').show();	
}

function webify(sku, variant, chkd)
{
	if (chkd==true)
	{
		web_status = 1;
	}
	else
	{
		web_status=0;
	}
	$('#dialog').load('./stock/webAdj.php?action=update&sku='+sku+'&variant='+variant+'&web_status='+web_status);
}

function priceAdj(i, sku, colour)
{
	var sale=$('#variant'+i+'sale').val();
	var retail=$('#variant'+i+'retail').val();
	var cost=$('#variant'+i+'cost').val();
	$('#message').load('./stock/priceAdj.php?action=commit&cost='+cost+'&retail='+retail+'&sale='+sale+'&sku='+sku+'&colour='+colour);
}

function addColour(sku)
{

	var colour=encodeURIComponent($('select[name=colours]').val());
	var costprice=$('input[name=costprice]').val();
	var retailprice=$('input[name=retailprice]').val();
	var urlsku = encodeURIComponent(sku);
	if (costprice>0 && retailprice>0)
	{
		$('#savebut').button({
			disabled: false
		});
		$('#colform').load('./stock/editStockcard.php?action=addcolour&sku='+urlsku+'&colour='+colour+'&costprice='+costprice+'&retailprice='+retailprice);

	}
	else
	{	
		$("select[name=colours] option[value='']").prop('selected', true);
		alert('Cost price and Retail price must be filled in ');	
		
	}
}

function salePrice(colour, sku, saleprice)
{
	$('#colmessage').load('./stock/editStockcard.php?action=addsaleprice&sku='+sku+'&colour='+colour+'&saleprice='+saleprice);
}

$('#movements').click(function()
{	
	var sku=encodeURIComponent($('input[name=sku]').val());
	$('#output').load('./stock/stkMovements.php?type=card&action=load&header=0&sku='+sku);
});

function printBarcode(sku)
{
    $('#dialog').html('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','39%');
    $('#dialog').css('margin-left','-14%');
	$('#temp').load('./stock/printBarcode.php?launch=card&sku='+sku);
	$('#dimmer').show();
	$('#dialog').show();
}

function searchItem2()
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
	if (sku.length>2 || brand!='' || season!='' || category!='' || colour!='')
	{
		$('#searchresultssc').load('./stock/editStockcard.php?action=results&brand='+brand+'&season='+season+'&sku='+sku+'&colour='+colour+'&category='+category+'&pricefrom='+pricefrom+'&priceto='+priceto);
	}
}

function newsku()
{
	var sku=$('input[name=sku]').val();
	$('input[name=sku]').prop('disabled','true');
	sku=sku.toUpperCase();
	$('input[name=sku]').val(sku);
	$('#sku').val(sku);
	urlsku=encodeURIComponent(sku);
	$('#colform').load('./stock/editStockcard.php?action=addcolour&new=1&sku='+urlsku);
}

function delVariant(sku, colour)
{
	$('#colform').load('./stock/editStockcard.php?action=delvariant&sku='+sku+'&colour='+colour);
}

function enableWeb(sku,colour)
{
	var url="./stock/editStockcard.php?action=enableweb&sku="+sku+"&colour="+colour;
	$.get(url, function(data,status){});
	$('#'+sku+'-'+colour).slideUp();
}

function recycleWeb(sku,colour)
{
	var url="./stock/editStockcard.php?action=recycleweb&sku="+sku+"&colour="+colour;
	$.get(url, function(data,status){});
	$('#'+sku+'-'+colour).slideUp();
}

function stockSync(sku)
{
    $('#dialog').html('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','39%');
    $('#dialog').css('margin-left','-14%');
	$('#temp').load('./stock/printBarcode.php?launch=card&sku='+sku);
	$('#dimmer').show();
	$('#dialog').show();
}


</script>
