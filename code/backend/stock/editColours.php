<?php 

include '../config.php';
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="")
{
	echo "<h2>Select a colour to Edit</h2>";
	#Must be searching for a size then
	echo "<table>";
	echo "<tr><td><input autocomplete=off onkeyup=\"javascript:srch(this.value);\" name=name></td><td><button onclick=\"javascript:select();\" >Add</button></td></tr>";
	echo "</table>";
	
	echo "<div id=searchresults>";
	
	$sql_query="select colour, id, nicename from colours order by colour";
	$results=$db_conn->query($sql_query);
	echo "<table  width=100%>";
	echo "<tr><th>Colour Code</th><th>Colour Name</th></tr>";
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr onclick=\"javascript:select('".$result['id']."');\" ><td>".$result['colour']."</td>";
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
	$sql_query="select colour, id, nicename from colours where id = '".$term."' order by colour asc";
	
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);
}

elseif ($action=="save")
{
	if ($_REQUEST['term']=="undefined")
	{
	    #Get barcode next value
	    $sql_query="select lpad(max(barcode),4,0) barcode from colours";
	    $barcode_max=$db_conn->query($sql_query);
	    $barcode=mysqli_fetch_array($barcode_max);
		$setClause="'".$_REQUEST['colour']."',\"".$_REQUEST['nicename']."\",".$_SESSION['CO'].",\"".$barcode['barcode']."\"";

		$sql_query="insert into colours (colour, nicename, company, barcode)
			values(".$setClause.")";
		$result=$db_conn->query($sql_query);
	}
	else
	{
		$setClause=" colour='".$_REQUEST['colour']."',nicename=\"".$_REQUEST['nicename']."\", company=\"".$_SESSION['CO']."\"";
		$sql_query="update colours set ".$setClause." where id='".$_REQUEST['term']."'";
		$result=$db_conn->query($sql_query);
	}
	
	echo "<p class=message>Colour saved</p>";
}

elseif ($action=="delete")
{	
    		$term=$_REQUEST['term'];
    		$sql_query="select count(*) cnt from orderdetail od, colours c where od.colour =c.colour and c.id= '$term'";
    		$num_sales=$db_conn->query($sql_query);
    		$num_rows=mysqli_fetch_array($num_sales);
    		
    		if ($num_rows['cnt']==0)
    		{
		  $sql_query="delete from colours where id = '".$term."'";
	
		  $do_it=$db_conn->query($sql_query);
		  echo "<p class=message>Colour Deleted</p>";
    		}
    		else 
    		{
    		    echo "<script type=text/javascript>alert('Colour has been sold');</script>";
    		}
}


if ($action=="select" )
{
# Draw up table for record
echo "<table>";
echo "<h2>Colour Record</h2>";
echo "<tr><td>Colour Description</td><td><input type=text name=nicename value='".$detail['nicename']."' /></td></tr>";
echo "<tr><td>Colour Code</td><td><input  type=text name=colour value='".$detail['colour']."' ></td></tr>";

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
	$('#searchresults').load('./stock/editColours.php?action=results&term='+search);
}

function select(value)
{
	var search2=encodeURIComponent(value);
	$('#updater_att').load('./stock/editColours.php?action=select&term='+search2);
}

function delterm(term)
{
	if (term=="")
	{
		alert('No user searched for');
		exit();
	}
	var areyousure=confirm('Care should be taken in deleting colours. They are used for old stock');
	if (areyousure==true)
	{
		$('#message_att').load('./stock/editColours.php?action=delete&term='+term);
	}
	$('#output').load('./stock/editColours.php');
	
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
	$('#message_att').load('./stock/editColours.php?'+getString);
}

</script>
