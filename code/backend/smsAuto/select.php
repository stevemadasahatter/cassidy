<html>
<body>
<h1>SMS to Send Details</h1>
<table>
	<tr><th>From Name (No Spaces)</th><td><input type=text id=name /></td></tr>
	<tr><th>Text body</th><td><textarea id=textbody></textarea></td></tr>
	<tr><th>SMS Group</th><td><select id=group>
<?php

$db_conn=mysqli_connect('localhost', 'mailer', 'mailer', 'smsAuto');

$sql_query="select grpID, grpName from smsGroup";

$results=$db_conn->query($sql_query);

while ($result=mysqli_fetch_array($results))
{
			echo "<option value=".$result['grpID'].">".$result['grpName']."</option>";
}
echo "</select></td></tr>";
?>
	<tr><th>Just testing</th><td><input type=checkbox id=testbox checked /></td></tr>
	<tr><td></td><td><button id=chk>Check</button></td></tr>
</table>

</body>


</html>

<script type="text/javascript">
	$('#chk').click(function(){
			var grp=$('#group').val();
			var txt=encodeURIComponent($('#textbody').val());
			var name=encodeURIComponent($('#name').val());
			var test=$('#testbox').is(':checked');
			$('#check').load('./check.php?grp='+grp+'&name='+name+'&txt='+txt+'&test='+test);
	});

</script>
