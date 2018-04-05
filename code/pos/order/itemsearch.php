<?php 

include '../config.php';
include '../functions/auth_func.php';
session_start();
#Authorised?
$auth=check_auth();
if ($auth<>1)
{
	exit();
}

#Active till session?
$active=getTillSession($_COOKIE['tillIdent']);
if ($active==0)
{
	exit();
}

$custref=$_SESSION['custref'];
echo "<table width=80%><tr><td><h2>Items</h2></td>";
echo "<td class=reduce><img src=./images/search.png /></td><td><input onkeyup=\"javascript:searchItem(this.value);\" id=item ></input></td>";
echo "<td class=reduce><img onclick=\"javascript:grabBarcode();\" id=barcodeimg  src=./images/barcode.png /></td>
		<td><input style=\"width:0px;\" onfocus=\"javascript:barcodefocus();\" onblur=\"javascript:barcodeblur();\" tabindex=1 onchange=\"javascript:barcodeItem(this.value);\" id=barcodeentry ></input></td></tr></table>";

?>

<script type="text/javascript">
$(document).ready(function(){
	$('#barcodeentry').focus();
});

function grabBarcode()
{
	$('#barcodeentry').focus();
}

function barcodefocus()
{
	$('#barcodeimg').removeClass('barcodeoff');
	$('#barcodeimg').addClass('barcodeon');
	$('#item').val('');
	$('#barcodeentry').val('');
}

function barcodeblur()
{
	$('#barcodeimg').removeClass('barcodeon');
	$('#barcodeimg').addClass('barcodeoff');
}

function searchItem(search)
{
	if (search =="")
	{
		$('#itemsearchresults').slideUp("fast");
	}
	else if (search.length>2)
	{
		$('#itemsearchresults').slideDown("fast");
		//$('#dimmer').show();
		var stringf=search.replace(/ /g,'%20');
		$('#itemsearchresults').load('./order/ajaxsearch.php?s='+stringf);
	}
	


}

function barcodeItem(search)
{
	if (search.length>=14)
	{
		$('#item').val();
		var string=search.substr(0,14);
		$('#bagitems').load('./order/bagContents.php?action=add&barcode=1&value='+string);
		
	}
}

function clearCust()
{

	$('#custdetail').load('./customer/custDetail.php?action=clear');
}
</script>
