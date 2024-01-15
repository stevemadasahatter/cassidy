<?php
include '../config.php';
include '../functions/auth_func.php';
include '../functions/field_func.php';
include '../functions/stock_func.php';

$sku=$_REQUEST['sku'];
$variant=$_REQUEST['variant'];
$web_status=$_REQUEST['web_status'];
session_start();
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($_REQUEST['action']=="update")
{
	
	$sql_query="update stock set web_status = ".$web_status." where Stockref = '".$sku."' and colour = '".$variant."'";
	$do_it=$db_conn->query($sql_query);
	exit();
}

?>