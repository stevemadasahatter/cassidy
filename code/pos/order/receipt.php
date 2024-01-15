<?php
include '../functions/print_func.php';
session_start();
$type=$_REQUEST['type'];
$output=$_REQUEST['output'];


if ($type=="email")
{
	$result=printReceipt($_REQUEST['orderno'],$type, $output);
}
elseif ($type=="print")
{
        $result=printReceipt($_REQUEST['orderno'],$type, $output);
}

elseif ($type=="gift")
{
	$result=giftReceipt($_REQUEST['orderno'], $type, $output);
}

elseif ($type=="last")
{
    exec('lp -d '.$printer.' '.$receipt_tmp.'/last.pdf');
}

clearReadout();
?>
