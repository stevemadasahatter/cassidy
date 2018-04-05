<?php

include '../config.php';
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="commit")
{
	$sql_query="update stock set saleprice=".$_REQUEST['sale']." , retailprice=".$_REQUEST['retail']." , costprice=".$_REQUEST['cost']." where Stockref ='".$_REQUEST['sku']."' and colour='".$_REQUEST['colour']."'";
	$doit=$db_conn->query($sql_query);

	echo "Price updated";
}

?>
