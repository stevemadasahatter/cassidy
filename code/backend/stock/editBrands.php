<?php 

include '../config.php';
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="")
{
	echo "<h2>Select Brand to Edit</h2>";
	#Must be searching for a brand then
	echo "<table>";
	echo "<tr><td><input autocomplete=off onkeyup=\"javascript:srch(this.value);\" name=name></td><td><button onclick=\"javascript:select();\" >Add</button></td></tr>";
	echo "</table>";
	
	echo "<div id=searchresults>";


	$sql_query="select id, brand, nicename, active from brands order by brand";
	$results=$db_conn->query($sql_query);
	echo "<table  width=100%>";
	echo "<tr><td>Brand Code</td><td>Brand Name</td></tr>";
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr onclick=\"javascript:select('".$result['id']."');\" ><td>".$result['brand']."</td>";
		echo "<td>".$result['nicename']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
	echo "<div id=updater_att></div>";
	echo "<div id=message_att></div>";
}

elseif ($action=="select")
{

	$term=$_REQUEST['term'];
	$sql_query="select id, brand, nicename, active from brands where id = '$term'";
	
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);
}

elseif ($action=="save")
{
	if ($_REQUEST['term']=="undefined")
	{
		$setClause="'".$_REQUEST['brand']."',\"".$_REQUEST['nicename']."\", 1";
	

		$sql_query="insert into brands (brand, nicename,active)
			values(".$setClause.")";
		$result=$db_conn->query($sql_query);
	}
	else
	{
		$setClause=" brand='".$_REQUEST['brand']."',nicename=\"".$_REQUEST['nicename']."\", active=\"".$_REQUEST['active']."\"";
		$sql_query="update brands set ".$setClause." where id='".$_REQUEST['term']."'";
		$result=$db_conn->query($sql_query);
	}
	
	echo "<p class=message>Brand saved</p>";
}

elseif ($action=="delete")
{	
        $term=$_REQUEST['term'];
        $sql_query="select count(*) cnt from orderdetail od, styleDetail sd where od.Stockref = sd.sku and sd.brand ='$term'";
        $num_sales=$db_conn->query($sql_query);
        $num_rows=mysqli_fetch_array($num_sales);
        
        if ($num_rows['cnt']==0)
        {
		  $sql_query="delete from brands where id = '".$term."'";
		  $do_it=$db_conn->query($sql_query);
		  echo "<p class=message>Brand Deleted</p>";
        }
        else 
        {
            echo "<script type=text/javascript>alert('Brand has been sold');</script>";
        }
}


if ($action=="select" )
{
# Draw up table for record
echo "<table>";
echo "<h2>Brand Record</h2>";
echo "<tr><td>Brand Code</td><td><input type=text name=brand value='".$detail['brand']."' /></td></tr>";
echo "<tr><td>Brand Name</td><td><input  type=text name=nicename value='".$detail['nicename']."' ></td></tr>";
echo "<tr><td>Active? (1/0)</td><td><input  type=text name=active value='".$detail['active']."' ></td></tr>";

echo "</table>";
echo "</div>";
echo "<input type=hidden name=term value='".$term."'>";
echo "<p width=100% align=right><button onclick=\"javascript:subForm();\" >Save</button><button onclick=\"javascript:delterm('".$_REQUEST['term']."');\" >Delete</button><button onclick=\"javascript:location.reload();\" >Close</button></p>";
}
?>
<script type="text/javascript">

$(document).ready(function(){
			$('button').button();
	});

function srch(value)
{
	$('#searchresults').show();
	var search=encodeURIComponent(value);
	$('#searchresults').load('./stock/editBrands.php?action=results&term='+search);
}

function select(value)
{
	var search2=encodeURIComponent(value);
	$('#updater_att').load('./stock/editBrands.php?action=select&term='+search2);
}

function delterm(term)
{
	if (term=="")
	{
		alert('No user searched for');
		exit();
	}
	var areyousure=confirm('Care should be taken in deleting Brands. They are used for old stock');
	if (areyousure==true)
	{
		$('#message_att').load('./stock/editBrands.php?action=delete&term='+term);
	}
	$('#output').load('./stock/editBrands.php');
	
}
function subForm()
{
	var getString="action=save&";
	$('input').each(function(){
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
	$('#message_att').load('./stock/editBrands.php?'+getString);
}

</script>
