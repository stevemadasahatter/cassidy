<?php

include '../config.php';
include '../functions/field_func.php';
include '../functions/auth_func.php';

echo "<h2>Product Search</h2>";
        echo "<script type=text/javascript>$('button').button();</script>";


echo "<table><tr>";
echo "<td>SKU</td><td>Brand</td><td>Season</td><td>Colour</td><td>Type</td></tr>";

$brands=getSelect('brands',0);
$seasons=getSelect('seasons',0);
$colours=getSelect('colours',0);
$category=getSelect('category',0);

echo "<tr>
		<td><input  onkeyup=\"javascript:searchItem2();\" id=sku /></td>
		<td><select onchange=\"javascript:searchItem2();\" id=brand >$brands</select></td>
		<td><select onchange=\"javascript:searchItem2();\" id=season >$seasons</select></td>
		<td><select onchange=\"javascript:searchItem2();\" id=colour>$colours</select></td>
		<td><select onchange=\"javascript:searchItem2();\" id=category>$category</select></td>
		</tr>";
echo "</table>";

?>

<div id=results></div>

<script type="text/javascript">
$(document).ready(function(){
	$('#barcode').focus();
});

function searchItem2()
{
	var brand=$('#brand').val();
	var season=$('#season').val();
	var category=$('#category').val();
	var sku=$('#sku').val();
	var colour=$('#colour').val();

	if (sku.length>2 || brand!='' || season!='' || category!='' || colour!='')
	{
		$('#results').load('./stock/advsearch.php?action=simple&brand='+brand+'&season='+season+'&sku='+sku+'&colour='+colour+'&category='+category);
	}
}

function closeDiag()
{
	$('#temp').remove();
	$('#dialog').hide();
	$('#dimmer').hide();
	$('#barcodeentry').focus();
}

</script>
