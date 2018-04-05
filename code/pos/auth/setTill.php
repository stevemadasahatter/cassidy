
<html>
<head>
	<title>Cassidy : Point of Sale</title>
	<link rel=stylesheet type="text/css" href="../style/site.css" />	
	<script src="../style/jquery-1.11.3.min.js"></script>
	<script src="../style/jquery-cr/jquery-ui.js"></script>
	<script src="../style/jquery.price_format.2.0.js"></script>
</head>
<?php

include '../config.php';

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);


if ($_REQUEST['action']=="set")
{
	setcookie('tillIdent',$_REQUEST['tillid'],time()+315360000, '/');
	print_r($_COOKIE);
	exit();
}
$sql_query="select t.tillname, t.nicename as tillnice, c.nicename from tills t, companies c where t.company = c.conum";
$results=$db_conn->query($sql_query);

echo "<h2>Till Admin<h2>";

echo "<table><tr><th></th><th>Value</th></tr>";

echo "<tr><td>Till Identity</td><td><select id=tillid><option value=''></option>";

while ($result=mysqli_fetch_array($results))
{
	echo "<option value=\"".$result['tillname']."\" >".$result['tillnice']."</option>";
}

echo "</select></td></tr>";
echo "</table>";

echo "<button id=setTill>Set Cookie</button>";


echo "<div id=dump></div>";

?>

<script type=text/javascript>

$('#setTill').click(function(){
	var till=$('#tillid').val();
	$('#dump').load('./setTill.php?action=set&tillid='+till);
});

</script>