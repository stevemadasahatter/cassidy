<?php

include '../config.php';
include '../functions/auth_func.php';

session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=='change')
{
	$total=bagTotals();
	echo "<h2>Change Discount</h2>";
	echo "<table><tr><td>Discount</td><td><input id=disc type=text value='".$total['discount']."' /></td></tr>";
	echo "<tr><td colspan=2 align=center><button id=commitDiscount>Save</button></td></tr>";
	echo "</table>";
	echo <<<EOF
<script>	
$('#commitDiscount').click(function(){
		var disc=$('#disc').val();
		$('#dimmer').hide();
		$('#dialog').hide();
		$('#temp').remove();
		$('#totals').load('./order/totalsCalc.php?action=commit&disc='+disc);
});
$('button').button();
</script>
EOF;
	
	exit();
}

elseif ($action=='commit')
{
	$sql_query="update orderheader set discount = ".$_REQUEST['disc']." where transno = ".$_SESSION['orderno'];
	$do_it=$db_conn->query($sql_query);

}

$total=bagTotals();


//echo "<table width=100%><tr><td  class=totalhead width=200px></td><td class=totalhead>Total</td><td class=totalhead>Discount Amount (Non-Sale)</td><td class=totalhead>Item Count</td>
	//	<td class=totalhead>Already Paid</td><td class=totalhead>Outstanding Total</td><td class=totalhead></td></tr>";
echo "<table><tr><td class=totalhead>Total</td><td class=totalhead>Count</td></tr>";
echo "<tr><td class=totalamnt >&pound;".number_format($total['outstanding'],2)."</td>";
echo "<td class=totalamnt >".$total['count']."</td></tr>";	
//echo "<td class=clickable onclick=\"javascript:changeDiscount();\">&pound;".number_format($total['discountamt'],2)."</td>";

//echo "<td>&pound;".number_format($total['paid'],2)."</td>";

echo "</table>";
?>

<script type="text/javascript">
function changeDiscount()
{
	$('#dialog').append('<div id=temp></div>');
	 $('#dialog').css('top','20%');
	 $('#dialog').css('left','50%');
	 $('#dialog').css('margin-left','-30%');
   $('#temp').load('./order/totalsCalc.php?action=change');
	$('#dimmer').show();
   $('#dialog').show();
	
}

</script>