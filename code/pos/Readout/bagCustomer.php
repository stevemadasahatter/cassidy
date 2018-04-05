<?php

include '../config.php';
include '../functions/auth_func.php';

session_start();
$auth=check_auth();

$till=$_COOKIE['tillIdent'];
$tillsession=getTillSession($till);

$orderno=$_SESSION['orderno'];

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

#Get order lines
$sql_query="select oh.custref, cust.forename, cust.lastname from orderheader oh, customers cust where oh.transno=$orderno and cust.custid=oh.custref";
$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);
echo "<div style\"width:100%;\">";
echo "<p class=cust >    Hi ";
if ($result['forename']<>"Walkin")
{
	echo $result['forename'];
}
	echo "!</p>";
echo "</div>";


?>
