<?php

include '../config.php';

echo "<h2>Saved Reports</h2>";
echo "<div id=float></div>";
#Go find all the php files in the saved directory
$list = glob("./saved/*.dfn");

if ($_REQUEST['action']=='delete')
{
	$success=unlink('./saved/'.$_REQUEST['filename']);
	if ($success==true)
	{
		echo "<h2>File deleted</h2>";
	}
}

echo "<table>";
echo "<tr><th>Report Name</th><th>Run</th><th>Schedule</th><th>Delete</th></tr>";
foreach ($list as $l) {
	$filename=substr($l, strlen($l)-20);
	include "./saved/".$filename;
	$runurl="./report/output.php?action=display&dateout=$dateout&datein=$datein&seasons=$seasons&brandsgrp=$brandsgrp&dataset=$dataset&totals=$totals&batch=1";
	echo "<tr><td>".urldecode($name)."</td><td><a onclick=\"javascript:runurl('$runurl');\">Run</a></td>
	<td>Schedule</td>
	<td><a onclick=\"javascript:del('$filename');\" >Delete</a></td></tr>";
}
echo "</table>";



?>

<script type="text/javascript">
function runurl(url)
{
	window.open(url,'_blank');
}

function del(filename)
{
	$('#output').load('./report/loadreport.php?action=delete&filename='+filename);
}

</script>