<?php
include './config.php';
include './functions/auth_func.php';

#Is it already open
$session=getTillSession($_COOKIE['tillIdent']);

if ($_REQUEST['action']=='open' && $session==0)
{
	setTillSession(1);
	$_REQUEST['action']=="";

}
?>


<html>
<head>
	<title>Point of Sale Reporting</title>
	<?php 
	$size=getTillType();
	$company=getTillCompany($_COOKIE['tillIdent']);
	echo "<link rel=stylesheet type=text/css href=\"./style/site-$company-".$size['size'].".css\">";
	?>	
	
	<link rel=stylesheet type=text/css href="./style/report.css">
 	<link rel="stylesheet" href="./style/jquery-cr/jquery-ui.css">  
	<script src="./style/jquery-1.11.3.min.js"></script>
	<script src="./style/jquery-cr/jquery-ui.js"></script>
</head>
<body>
<div id=page>
<div id=dialog></div>
<div id=dimmer></div>
<div id=signin class=subsection>
</div>
<div id=buttons class=section>
</div>

</div>

<script type="text/javascript">

	$(document).ready(function() {
		$('#signin').load('./auth/login.php');
		$('#buttons').load('./report/buttons.php');
	});


</script>
