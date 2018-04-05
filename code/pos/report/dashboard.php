<div id=outer>
	<div id=ctrls>


<?php
session_start();
if ($_SESSION['level']<=2)
{
	echo "<h2>Today's reports</h2>";
	echo "<table><tr><td><button id=close>Close</button><button id=run>Run</button></td></tr><input type=hidden id=type value=now>
			<input type=hidden id=selection value=trans /><input type=hidden id=datefrom value=''/><input type=hidden id=dateto value=''/></table>";
}
else
{	
	echo "<h2>Report Controls</h2>";
	echo "<table width=100%>";
	echo "<tr><td><select id=type><option value=now>Today</option><option value=yesterday>Yesterday</option><option value=range>Date Range</option></select></td>";
	echo "<th>Date From  <input type=date style=\"width:165px;\" id=datefrom /></th><th>Date To  <input style=\"width:165px;\" type=date id=dateto /></th>
	<td><button id=close>Close</button><button id=run>Run</button></td>
	<td><button id=trans>Trans</button><button id=vouch>Vouchers</button></td><input type=hidden id=selection value=trans /></tr></table>";
	
}


?>

	</div>
	<div id=reports>
		<div class=rptblock id=byProdgroup></div>
		<div class=rptblock id=byCat></div>
		<div class=rptblock id=byBrand></div>
		<div class=rptblock id=hrlySales></div>
		<div class=rptblock id=byAssist></div>
		<div class=rptblock id=tillRoll></div>
	</div>

</div>

<script type="text/javascript">
$(document).ready(function()
{
	$('button').button();
	if ($('#selection').val()=='trans')
	{
		$('#trans').button({
			disabled: true
		});
		$('#vouch').button({
			disabled: false
		});
	
	}
	if ($('#selection').val()=='vouch')
	{
		$('#vouch').button({
			disabled: true
		});
		$('#trans').button({
			disabled: false
		});
	
	}
});	

$('#vouch').click(function(){
	$('#selection').val('vouch');
	if ($('#selection').val()=='trans')
	{
		$('#trans').button({
			disabled: true
		});
		$('#vouch').button({
			disabled: false
		});
	
	}
	if ($('#selection').val()=='vouch')
	{
		$('#vouch').button({
			disabled: true
		});
		$('#trans').button({
			disabled: false
		});
	
	}
});

$('#trans').click(function(){
	$('#selection').val('trans');
	if ($('#selection').val()=='trans')
	{
		$('#trans').button({
			disabled: true
		});
		$('#vouch').button({
			disabled: false
		});
	
	}
	if ($('#selection').val()=='vouch')
	{
		$('#vouch').button({
			disabled: true
		});
		$('#trans').button({
			disabled: false
		});
	
	}
});

$('#close').click(function(){
	$('#temp').load('./auth/login.php?action=logout');
});

$('#run').click(function(){
	var type=$('#type').val();
	var dateFrom=$('#datefrom').val();
	var dateTo=$('#dateto').val();
	if ($('#selection').val()=='trans')
	{
		$('#byProdgroup').load('./report/dashboard/byProdgroup.php?type='+type+'&dateFrom='+dateFrom+'&dateTo='+dateTo);
		$('#byCat').load('./report/dashboard/byCat.php?type='+type+'&dateFrom='+dateFrom+'&dateTo='+dateTo);
		$('#byBrand').load('./report/dashboard/byBrand.php?type='+type+'&dateFrom='+dateFrom+'&dateTo='+dateTo);
		$('#byAssist').load('./report/dashboard/byAssistant.php?type='+type+'&dateFrom='+dateFrom+'&dateTo='+dateTo);
		$('#hrlySales').load('./report/dashboard/byHour.php?type='+type+'&dateFrom='+dateFrom+'&dateTo='+dateTo);
		$('#tillRoll').load('./report/tillRoll.php?print=false&type='+type+'&dateFrom='+dateFrom+'&dateTo='+dateTo);
	}

	if ($('#selection').val()=='vouch')
	{
		$('#byProdgroup').load('./report/dashboard/vouchCredit.php');
		$('#byCat').load('./report/dashboard/vouchDep.php');
		$('#byBrand').load('./report/dashboard/vouchGift.php');
	}
});
</script>