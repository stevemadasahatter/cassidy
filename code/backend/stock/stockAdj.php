<?php

include '../config.php';
include '../functions/stock_func.php';
include '../functions/field_func.php';
include '../functions/auth_func.php';

$action=$_REQUEST['action'];
$sku=$_REQUEST['sku'];
$variant=$_REQUEST['variant'];
$value=$_REQUEST['value'];
$reason=$_REQUEST['reason'];
$sizeid=$_REQUEST['sizeid'];

if ($action=="reason")
{
	$reasons=getSelect('stkadjReason','');
	echo "<table>";
	echo "<tr><td>Reason for Adjustment</td><td><select id=reason name=reason>".$reasons."</select></td></tr>";
	echo "<tr><td><button name=submit onclick=\"javascript:setReason('$sku','$variant','$value', $sizeid);\">Submit</button><button name=cancel onclick=\"javascript:can();\">Cancel</button></td></tr>";
	echo "</table>";
}

if ($action=="commit")
{
	$doit=applStockAdj($sku,$variant,$sizeid,$value,$reason);
}

?>
<script type="text/javascript">
$(document).ready(function(){
	$('button').button();
});

function setReason(sku,variant,value, sizeid)
{	
	var reason=$('#reason').val();
	$('#temp').load('./stock/stockAdj.php?action=commit&sku='+sku+'&variant='+variant+'&value='+value+'&reason='+reason+'&sizeid='+sizeid);
	$('#temp').remove();
	$('#dimmer').hide();
	$('#dialog').hide();
}

function can()
{
	$('#temp').remove();
	$('#dimmer').hide();
	$('#dialog').hide();
}

</script>
