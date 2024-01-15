<?php

include '../config.php';
include '../functions/auth_func.php';
session_start();
$auth=check_auth();
if ($auth<>1)
{
	exit();
}
$till=$_COOKIE['tillIdent'];
$tillsession=getTillSession($till);

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($_REQUEST['action']=="load")
{
	$_SESSION['orderno']=$_REQUEST['orderno'];
	$cust=getCustomer($_SESSION['orderno']);
	$_SESSION['custref']=$cust['custid'];
	
	$sql_query="update orderdetail set timestamp = NOW() where transno = '".$_REQUEST['orderno']."'";
	$do_it=$db_conn->query($sql_query);
	
	$sql_query="select rollID from tillrolldetail where orderno = '".$_SESSION['orderno']."'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$_SESSION['rollID']=$result['rollID'];
	echo "<script type=text/javascript>location.reload();</script>";
}
#Get open sessions for this till session
$sql_query="select orderheader.transno, time_format(orderheader.transDate,'%H:%i') time, max(orderdetail.Stockref) Stockref from orderheader, orderdetail 
		where orderheader.transno=orderdetail.transno   
		and (orderheader.status = 'P')
group by 1,2";
$results=$db_conn->query($sql_query);

echo "<table width=100%><tr><td>";
$i=1;
while ($openorder=mysqli_fetch_array($results))
{
	
	if ($_SESSION['orderno']<>$openorder['transno'])
	{
	    echo "<button ";
		echo "class=\"openorders\" title=\"style:".$openorder['Stockref']."\" onclick=\"javascript:loadOrder('".$openorder['transno']."');\">Retrieve<br>Sale ".$i."</button>";
	}
	$i++;
}
echo "</td></tr></table>";
?>

<script type="text/javascript">
	$(document).ready(function(){
		$('button').button();
	});

function loadOrder(orderno)
{
	$('#launcher').load('./page/launcher.php?action=load&orderno='+orderno);
}
</script>
