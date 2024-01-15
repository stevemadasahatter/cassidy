<?php
include '../config.php';
include '../functions/auth_func.php';
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="select action from readout";
$results=$db_conn->query($sql_query);

$result=mysqli_fetch_array($results);

#Clear it down status
if ($result['action']=="0")
{
	if ($_SESSION['orderno']<>"")
	{
		$_SESSION['orderno']="";
		echo "<script type=text/javascript>location.reload();</script>";
		exit();
	}
	exit();
}

if ($action=='updated')
{
	$sql_query="update readout set action=3";
	$do_it=$db_conn->query($sql_query);
}


#Refresh the screen status
if ($result['action']==1)
{
	echo "<script type=text/javascript>location.reload(); </script>";
}



#Leave screen alone status
if ($result['action']==3)
{
	exit();
}


?>
