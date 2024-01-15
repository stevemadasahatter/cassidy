<?php
include '../config.php';
include '../functions/auth_func.php';
include '../functions/print_func.php';
session_start();
$till=$_COOKIE['tillIdent'];
$action=$_REQUEST['action'];
$tillsession=getTillSession($till);

$db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);

printPettyCash($_REQUEST['pcash']);
?>
