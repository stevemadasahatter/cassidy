<?php 

include '../config.php';
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

        echo "<div id=message>";
	echo "<h2>Receipt Message Editor</h2>";

	$sql_query="select message from receipt_messages where company = '".$_SESSION['CO']."'";
	
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);

if ($action=="save")
{
	$setClause=" message='".$_REQUEST['message']."'";
		
	$sql_query="update receipt_messages set ".$setClause." where company='".$_SESSION['CO']."'";
	$result=$db_conn->query($sql_query);
	//echo $sql_query;
	echo "<p class=message>Receipt save</p>";
}

# Draw up table for record
echo "<table>";
echo "<h3>Current Receipt Message</h3>";
echo "<tr><td>Receipt Message</td><td><input type=text name=message value='".$detail['message']."' /></td></tr>";

echo "</table>";
echo "</div>";
echo "<p width=100% align=right><button onclick=\"javascript:subForm();\" >Save</button><button onclick=\"javascript:location.reload();\" >Close</button></p>";
?>
<script type="text/javascript">

$(document).ready(function(){
			$('button').button();
	});


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
	$('#message').load('./customer/editReceipt.php?'+getString);
}

</script>
