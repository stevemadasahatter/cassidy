<?php
include '../functions/print_func.php';
session_start();
$type=$_REQUEST['type'];

if ($type=="email")
{
	$result=printReceipt($_REQUEST['orderno'],$type);
}
elseif ($type=="print")
{
        $result=printReceipt($_REQUEST['orderno'],$type);
}

elseif ($type=="gift")
{
	$result=giftReceipt($_REQUEST['orderno']);
}

clearReadout();
?>
