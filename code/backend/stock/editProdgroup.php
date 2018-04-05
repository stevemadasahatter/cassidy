<?php 

include '../config.php';
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="")
{
	echo "<h2>Search for a Product Group to Edit</h2>";
	#Must be searching for a size then
	echo "<table>";
	echo "<tr><td><input autocomplete=off onkeyup=\"javascript:srch(this.value);\" name=name></td><td><button onclick=\"javascript:select();\" >Add</button></td></tr>";
	echo "</table>";
	
	echo "<div id=searchresults>";

	$sql_query="select productgroup, id, nicename from ProductGroup order by productgroup";
	$results=$db_conn->query($sql_query);
	echo "<table  width=100%>";
	echo "<tr><td>Group</td><td>Description</td></tr>";
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr onclick=\"javascript:select('".$result['id']."');\" ><td>".$result['productgroup']."</td>";
        echo "<td>".$result['nicename']."</td>";
		echo "</tr>";
	}
	echo "</table></div>";
	echo "<div id=updater_att></div>";
	echo "<div id=message_att></div>";
}
elseif ($action=="select")
{

	$term=$_REQUEST['term'];
	$sql_query="select productgroup, id, nicename from ProductGroup where id = '".$term."'";
	
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);
}

elseif ($action=="save")
{
	if ($_REQUEST['term']=="undefined")
	{
		$setClause="'".$_REQUEST['nicename']."',\"".$_REQUEST['productgroup']."\"";
	

		$sql_query="insert into ProductGroup (nicename, productgroup)
			values(".$setClause.")";
		$result=$db_conn->query($sql_query);
	}
	else
	{
		$setClause=" nicename='".$_REQUEST['nicename']."',productgroup=\"".$_REQUEST['productgroup']."\"";
		
		$sql_query="update ProductGroup set ".$setClause." where id='".$_REQUEST['term']."'";
		$result=$db_conn->query($sql_query);
	}
	
	echo "<p class=message>Product Group saved</p>";
}

elseif ($action=="delete")
{	
		$term=$_REQUEST['term'];
		$sql_query="delete from ProductGroup where id = '".$term."'";
	
		$do_it=$db_conn->query($sql_query);
		echo "<p class=message>Product Group Deleted</p>";
}


if ($action=="select" )
{
# Draw up table for record
echo "<table>";
echo "<h2>Size Record</h2>";
echo "<tr><td>Description</td><td><input type=text name=nicename value='".$detail['nicename']."' /></td></tr>";
echo "<tr><td>Product Group</td><td><input  type=text name=productgroup value='".$detail['productgroup']."' ></td></tr>";
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
	$('#searchresults').load('./stock/editProdgroup.php?action=results&term='+search);
}

function select(value)
{
	var search2=encodeURIComponent(value);
	$('#updater_att').load('./stock/editProdgroup.php?action=select&term='+search2);
}

function delterm(term)
{
	if (term=="")
	{
		alert('No Product Group searched for');
		exit();
	}
	var areyousure=confirm('Care should be taken in deleting Product Groups. They are used for old stock');
	if (areyousure==true)
	{
		$('#message_att').load('./stock/editProdgroup.php?action=delete&term='+term);
	}
	$('#output').load('./stock/editProdgroup.php');
	
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
	$('#message_att').load('./stock/editProdgroup.php?'+getString);
}

</script>
