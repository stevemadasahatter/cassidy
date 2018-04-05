<html>
<body>
<?php
$grp=$_REQUEST['grp'];
$name=$_REQUEST['name'];
$txt=$_REQUEST['txt'];
$test=$_REQUEST['test'];

if ($grp=="")
{
	exit();
}


$db_conn=mysqli_connect('localhost','mailer','mailer','smsAuto');

$sql_query="select grpName from smsGroup where grpID=$grp";

$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);

?>

<table>

<?php 

echo "<th>Group name</th><td>".$result['grpName']."</td></tr>";

$sql_query="select count(*) num from sendGroups where grpID=$grp";
$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);

echo "<tr><th>Number in Group</th><td>".$result['num']."</td></tr>";
echo "<tr><th>Name on the text</th><td>".urldecode($name)."</td><tr>";
echo "<tr><th>Text to be sent</th><td>$txt</td></tr>";
echo "<tr><th>Are we testing (true=yes, false=NO)</th><td>$test</td></tr>";
		
echo "</table>";
echo "<input type=hidden id=name value=\"$name\" /><input type=hidden id=grpID value=$grp /><input type=hidden id=txt value=\"$txt\" /><input type=hidden id=test value=$test />";
echo "<button id=send>Send!</button>";
?>
</body>

</html>
<script type="text/javascript">
	$('#send').click(function(){
			var grp=$('#group').val();
			var txt=encodeURIComponent($('#textbody').val());
			var name=encodeURIComponent($('#name').val());
			var test=$('#testbox').is(':checked');
			$('#result').load('./send.php?grp='+grp+'&name='+name+'&txt='+txt+'&test='+test);
	});

</script>
