<?php

include '../config.php';
include '../functions/auth_func.php';

session_start();
$auth=check_auth();
$sku=$_REQUEST['sku'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$action=$_REQUEST['action'];
if ($action=="")
{
	$sql_query="select Stockref, qty from orderdetail where Stockref = '".$sku."' and transno=".$_SESSION['orderno'];
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	
	echo "<table><tr><th>SKU</th><th>New Qty</th><th></th></tr>";
	echo "<tr><td>".$result['Stockref']."</td><td><input id=qty value=".$result['qty']."></td><td><button id=submit onclick=\"javascript:submit('".$result['Stockref']."');\">Save</button></td></tr>";
	echo "</table>";
}

if ($action=="chg")
{
	$sql_query="update orderdetail set qty=".$_REQUEST['qty']." where transno = ".$_SESSION['orderno']." and Stockref='".$_REQUEST['sku']."'";
	$doit=$db_conn->query($sql_query);
	echo "<script type=text/javascript>javascript:location.reload();</script>";
	exit();
}

?>
<script type="text/javascript">
function submit(sku)
{
	var qty=$('#qty').val();
	$('#temp').load('./order/chgqty.php?action=chg&qty='+qty+'&sku='+sku);
}

$(document).ready(function(){
	$('#qty').spinner();
	$('#submit').button();
	
});

</script>