<?php
include '../config.php';
include '../functions/auth_func.php';
include '../functions/print_func.php';
session_start();
$till=$_COOKIE['tillIdent'];
$action=$_REQUEST['action'];
$pot=$_REQUEST['pot'];
$tillsession=getTillSession($till);

$db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);

printSpendPot($pot);
?>
