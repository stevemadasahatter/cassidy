<?php
include '../config.php';
include '../website/config.php';
include_once '../functions/auth_func.php';
include_once '../functions/web_func.php';
include '../website/config.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($_REQUEST['action']=="save")
{
	if ($_REQUEST['batch_prices']=="true")
	{
		$sql_query="update config set value=1 where config='batch_prices'";
	}
	else
	{
		$sql_query="update config set value=0 where config='batch_prices'";
	}
	$do_it=$db_conn->query($sql_query);
	
	if ($_REQUEST['batch_stock']=="true")
	{
		$sql_query="update config set value=1 where config='batch_stock'";
	}
	else
	{
		$sql_query="update config set value=0 where config='batch_stock'";
	}
	$do_it=$db_conn->query($sql_query);
	
	
	if ($_REQUEST['batch_upload']=="true")
	{
		$sql_query="update config set value=1 where config='batch_upload'";
	}
	else
	{
		$sql_query="update config set value=0 where config='batch_upload'";
	}
	$do_it=$db_conn->query($sql_query);
		
}

echo "<h2>Control background tasks</h2>";

$sql_query="select nicename, config, value from config where config in ('batch_prices','batch_stock','batch_upload')";

$results=$db_conn->query($sql_query);

echo "<table><tr><th>Synchronisation Process</th><th>Status</th><th>Last Run</th></tr>";
while ($result=mysqli_fetch_assoc($results))
{
	echo "<tr><td>".$result['nicename']."</td>";
	if ($result['value']==1)
	{
		echo "<td><input type=checkbox name=\"".$result['config']."\" checked /></td>";
		
	}
	else
	{
		echo "<td><input type=checkbox name=\"".$result['config']."\" /></td>";
		
	}
	echo "<td>";
	$sql_query2="select value from config where config = '".$result['config'].'_run'."'";
	$results2=$db_conn2->query($sql_query2);
	$result=mysqli_fetch_array($results2);
	echo $result['value']."</td>";
	echo "<tr>";
}
echo "<tr><td></td><td><button id=submit>Save</button></td></tr>";
echo "</table>";

?>

<script type="text/javascript">
$(document).ready(function(){
	$('button').button();
});

$('#submit').click(function(){
	var getstring="action=save&";
	$(document).find('input[type=checkbox]').each(function(){
		getstring=getstring+$(this).attr('name')+"="+$(this).prop('checked')+"&";
	});
	$('#output').load('./website/batch_control.php?'+getstring);
});


</script>