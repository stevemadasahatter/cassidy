<?php

include "../config.php";
include "../functions/auth_func.php";

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$action=$_REQUEST['action'];
$type=$_REQUEST['type'];

if ($action=="amnt")
{
    
	#Get voucher amount
	$voucher=getSpendPot($_REQUEST['id'],$type);
	$ymd = DateTime::createFromFormat('d/m/Y', $voucher['expireDate'])->format('d/m/Y');
	$mdy = DateTime::createFromFormat('d/m/Y', $voucher['expireDate'])->format('m/d/Y');
	if ($voucher['usedDate']<>'')
	{
		#Used gift voucher
		echo "Used on ".date("d-m-Y", strtotime($voucher['usedDate']));
	}
	elseif (strtotime($mdy)-strtotime(date('m/d/Y'))>-5)
	{
		#Valid, return amount
		echo "£ ".$voucher['amount'];
		//echo $voucher['amount'];
	}
	else 
	{
		# Expired
		echo "Expired on ".$ymd;
	}
}

if ($action=="type")
{
	#Get voucher amount
	$voucher=getSpendPot($_REQUEST['id'],$type);
	$ymd = DateTime::createFromFormat('d/m/Y', $voucher['expireDate'])->format('d/m/Y');
	$mdy = DateTime::createFromFormat('d/m/Y', $voucher['expireDate'])->format('m/d/Y');
	if ($voucher['usedDate']<>'')
	{
		#Used gift voucher
		exit();
	}
	elseif (strtotime($mdy)-strtotime(date('m/d/Y'))>0)
	{
		#Valid, return amount
		echo $voucher['type'];
	}
	else 
	{
		# Expired
		exit();
	}
}

?>

