<?php
include '../config.php';

session_start();
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="select transno, custref, action from readout";
$results=$db_conn->query($sql_query);

$result=mysqli_fetch_array($results);
if ($result['action']==1)
{
	$_SESSION['orderno']=$result['transno'];
	$_SESSION['custref']=$result['custref'];
}

if ($_SESSION['orderno']=="")
{
	echo <<<EOF
<html>
<head>
	<title>Customer Readout</title>
	<link rel=stylesheet type="text/css" href="../style/readout.css">";	

 	<link rel="stylesheet" href="./style/jquery-cr/jquery-ui.css">  
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	<script src="./style/jquery-cr/jquery-ui.js"></script>
</head>
<body>
<div id=page>
			<div id=empty>
			<img style="width:100%;" src=./images/large-1-logo.png />
			</div>
			<div id=detector></div>
<div>
<script type="text/javascript">
	var auto_refresh = setInterval(
	function ()
	{
	$('#detector').load('readoutDetect.php');
	}, 1500); // refresh every 10000 milliseconds

</script>
EOF;
	exit();	
}

?>
<html>
<head>
	<title>Customer Readout</title>
	<link rel=stylesheet type="text/css" href="../style/readout.css">

 	<link rel="stylesheet" href="./style/jquery-cr/jquery-ui.css">  
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	<script src="./style/jquery-cr/jquery-ui.js"></script>
</head>
<body>
<div id=page>
<div>
	<div id=logo>
		<img style="width:20%;text-align:center;" src=./images/large-1-logo.png />
	</div>
	<div id=detector></div>
</div>
<div id=customer></div>
<div id=shopping>
<div id=bagContent></div>
<div id=bagTotals></div>
</div>
<div id=advert></div>
<div id="upsell">
	<div id="bagemail"></div>
	<div id="bagAppro"></div>
</div>
</div>

<script type="text/javascript">

	$(document).ready(function() {
                $('#bagemail').load('./bagEmail.php');
                $('#bagAppro').load('./bagAppro.php');
		$('#customer').load('./bagCustomer.php');
		$('#bagContent').load('./bagContents.php');
		$('#bagTotals').load('./totalsCalc.php');
	});
</script>

<script type="text/javascript">
	var auto_refresh = setInterval(
	function ()
	{
	$('#detector').load('readoutDetect.php?action=updated').fadeIn("slow");
	}, 1500); // refresh every 10000 milliseconds

</script>
</body>
</html>
