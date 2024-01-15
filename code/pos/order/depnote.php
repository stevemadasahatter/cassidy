<?php

echo "<h2>Enter Deposit Amount</h2>";

echo "<table><tr><td>Deposit Note Amount</td><td>£<input type=text id=amnt value=0.00 ></input></td></tr>";

echo "<tr><td></td><td align=right><button id=closedep>Close</button><button id=saveAmnt>Save</button></td></tr></table>";

?>
<script type="text/javascript">

$(document).ready(function()
{
	$('button').button();
	$('#amnt').priceFormat({
	    prefix: '',
	     thousandsSeparator: ''
	});
});

$('#saveAmnt').click(function()
{
	$('#depnoteval').val($('#amnt').val());
	$('#amntout').val($('#amnt').val());
	updateSplit($('#amnt').val(),0);
	$('#depnotediv').hide();
});

$('#closedep').click(function()
{
	$('#depnotediv').hide();
});

</script>
