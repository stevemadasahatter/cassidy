<?php

include '../config.php';
include '../functions/auth_func.php';
include '../functions/print_func.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

session_start();
$auth=check_auth();
$till=$_COOKIE['tillIdent'];
$tillsession=getTillSession($till);

if ($_REQUEST['action'])
{
	if ($_REQUEST['action']=='pause')
	{
	    $sql_query="update orderheader set status = 'P' where transno = '".$_SESSION['orderno']."'";
	    $do_it=$db_conn->query($sql_query);
		unset($_SESSION['custref']);
		unset($_SESSION['orderno']);
		
		deauthenticate();
		echo "<script type=text/javascript>location.reload();</script>";
	}
	if ($_REQUEST['action']=="opendrawer")
	{
		opendrawer(0);
		deauthenticate();
		echo "<script type=text/javascript>setTimeout(function(){location.reload();},2000);</script>";
	}
	if ($_REQUEST['action']=="reprint")
	{
	    return exec('lp -d '.$printer.' '.$receipt_tmp.'/printing.pdf');
	}
}

$active=getTillSession($till);
$custref=getCustomer($_SESSION['orderno']);

if ($auth==1)
{
	echo "<p width=100% align=right style=\"font-size:10pt;margin-top:5px;\">";
	echo "<button id=giftvoucher>Gift<br>Voucher</button>";
	echo "<button title=\"".$foo."\" id=stockEnq>Stock<br>Enquiry</button>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<button title=\"".$_SESSION['orderno']."\" onclick=\"javascript:pauseOrder('".$_SESSION['orderno']."');\">Park<br>Sale</button>";
	echo "<button title=\"".$_SESSION['orderno']."\" id=loadOrder>Scan<br>Receipt</button>";
	echo "<button id=allOnAppro>All<br>OnAppro</button>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<button id=pettyCash>Float<br>Control</button>";
	echo "<button id=pettyCashTrans>Petty<br>Cash</button>";
	echo "<button id=opendrawer>Open<br>Drawer</button>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<button id=bankrep>Banking<br>Report</button>";
	if ($debug==1)
	{
		echo "<button title=\"".print_r($_SESSION)."\" id=eod>End of<br>Day</button>";
	}
	else
	{
		echo "<button id=eod>End of<br>Day</button>";
	}
	
	//echo "<button onclick=\"javascript:closeTill();\" >Exit<br>Till</button>";
	//echo "<button id=dbg >Debug<br>till</button>";
	echo "</p>";
}
else 
{
	echo "<p width=100% align=right style=\"font-size:10pt;margin-top:5px;\">";
	if ($local_printer==1)
	{
	    echo "<button onclick=\"printJS('$local_printer_path/printing.pdf');\">Reprint<br>Last</button>";
	    
	}
	else
	{
	    echo "<button id=\"reprint\" >Reprint<br>Last</button>";
	}
	
	echo "<button id=\"fillUps\" >Fill<br>Ups</button>";
	echo "<button id=\"barcodes\" >Barcode<br>Print</button>";
	echo "<button   id=stockEnq>Stock<br>Enquiry</button>";
	echo "</p>";
}
echo "</tr></table>";

?>

<script type="text/javascript">
$('#opendrawer').click(function()
{
	$('#controls').load('./page/controls.php?action=opendrawer');
	//$('#controls').load('http://192.168.1.14/drawer.php');
	
});

$('#reprint').click(function(){
	$('#controls').load('./page/controls.php?action=reprint');
});

$('#allOnAppro').click(function(){
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-25%');
$('#temp').load('./order/approOrders.php?action=all');
$('#dimmer').show();
$('#dialog').show();
});

$('#stockEnq').click(function(){
                 $('#dialog').append('<div id=temp></div>');
                 $('#dialog').css('top','0%');
                 $('#dialog').css('left','0%');
                 $('#dialog').css('margin-left','0%');
         $('#temp').load('./order/stockSearch.php');
	 $('#dimmer').show();
         $('#dialog').show();
});

$('#loadOrder').click(function(){
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-17%');
$('#temp').load('./customer/loadOrder.php');
$('#dimmer').show();
$('#dialog').show();

});

function pauseOrder()
{
	$('#controls').load('./page/controls.php?action=pause');
}

$(document).ready(function(){
	$('button').button();
});

function closeDiag()
{
        $('#temp').remove();
        location.reload();
}

$('#pettyCash').click(function(){
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-15%');
	$('#temp').load('./page/pettyCash.php');
	$('#dimmer').show();
	$('#dialog').show();

});

$('#pettyCashTrans').click(function(){
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-15%');
	$('#temp').load('./page/pettyCashTrans.php');
	$('#dimmer').show();
	$('#dialog').show();

});

$('#barcodes').click(function(){
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-15%');
	$('#temp').load('/backend/stock/printBarcode.php?close=close');
	$('#dimmer').show();
	$('#dialog').show();

});

$('#giftvoucher').click(function(){
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-35%');
	$('#temp').load('./customer/giftVoucher.php');
	$('#dimmer').show();
	$('#dialog').show();

});


$('#dbg').click(function(){
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-15%');
	$('#temp').load('./debug.php');
	$('#dimmer').show();
	$('#dialog').show();

});

$('#fillUps').click(function(){
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-15%');
	$('#temp').load('./report/topUps.php');
	$('#dimmer').show();
	$('#dialog').show();

});

$('#eod').click(function(){
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-15%');
	$('#temp').load('./report/eod.php?action=read');
	$('#dimmer').show();
	$('#dialog').show();

});

$('#bankrep').click(function(){
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','0%');
    $('#dialog').css('left','40%');
    $('#dialog').css('margin-left','-15%');
	$('#temp').load('./report/banking.php?action=read');
	$('#dimmer').show();
	$('#dialog').show();

});

</script>
