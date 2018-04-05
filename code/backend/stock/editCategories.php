<?php 

include '../config.php';
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="")
{
	echo "<h2>Select Category to Edit</h2>";
	#Must be searching for a categoru then
	echo "<table>";
	echo "<tr><td><input autocomplete=off onkeyup=\"javascript:srch(this.value);\" name=name></td><td><button onclick=\"javascript:select();\" >Add</button></td></tr>";
	echo "</table>";
	
	echo "<div id=searchresults>";

	$sql_query="select id, category, nicename from category order by category";
	$results=$db_conn->query($sql_query);
	echo "<table  width=100%>";
	echo "<tr><td>Category Code</td><td>Category Name</td></tr>";
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr onclick=\"javascript:select('".$result['id']."');\" ><td>".$result['category']."</td>";
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
	$sql_query="select id, category, nicename from category where id = '$term'";
	
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);
}

elseif ($action=="save")
{
	if ($_REQUEST['term']=="undefined")
	{
		$setClause="'".$_REQUEST['category']."',\"".$_REQUEST['nicename']."\"";
	

		$sql_query="insert into category (category, nicename)
			values(".$setClause.")";
		$result=$db_conn->query($sql_query);
	}
	else
	{
		$setClause=" category='".$_REQUEST['category']."',nicename=\"".$_REQUEST['nicename']."\"";
		$sql_query="update category set ".$setClause." where id='".$_REQUEST['term']."'";
		$result=$db_conn->query($sql_query);
	}
	
	echo "<p class=message>Category saved</p>";
}

elseif ($action=="delete")
{	
		$term=$_REQUEST['term'];
		$sql_query="delete from category where id = '".$term."'";
	
		$do_it=$db_conn->query($sql_query);
		echo "<p class=message>Category Deleted</p>";
}


if ($action=="select" )
{
# Draw up table for record
echo "<table>";
echo "<h2>Category Record</h2>";
echo "<tr><td>Category Code</td><td><input type=text name=category value='".$detail['category']."' /></td></tr>";
echo "<tr><td>Category Name</td><td><input  type=text name=nicename value='".$detail['nicename']."' ></td></tr>";

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
	$('#searchresults').load('./stock/editCategories.php?action=results&term='+search);
}

function select(value)
{
	var search2=encodeURIComponent(value);
	$('#updater_att').load('./stock/editCategories.php?action=select&term='+search2);
}

function delterm(term)
{
	if (term=="")
	{
		alert('No user searched for');
		exit();
	}
	var areyousure=confirm('Care should be taken in deleting Categories. They are used for old stock');
	if (areyousure==true)
	{
		$('#message_att').load('./stock/editCategories.php?action=delete&term='+term);
	}
	$('#output').load('./stock/editCategories.php');
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
	$('#message_att').load('./stock/editCategories.php?'+getString);
}

</script>
