<?php
include './config.php';
include './functions/auth_func.php';
session_start();
header("Cache-control: no-store, no-cache, must-revalidate");
header("Expires: Mon, 26 Jun 1997 05:00:00 GMT");
header("Pragma: no-cache");
if (!isset($_SESSION['BE']))
{
echo <<<EOF
<html>
<head>
		    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="cache-control" content="no-store">
    <meta http-equiv="pragma" content="no-store">
        <title>Cassidy - Back Office</title>
        <link rel=stylesheet type="text/css" href="./style/login.css" />
         <link rel="stylesheet" href="./style/jquery-cr/jquery-ui.css">
		<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="./style/jquery-cr/jquery-ui.js"></script>
</head>
<body>
        <div id=page>
        <div id=dialog></div>
        <div id=dimmer></div>
                <div id=header>
                        <div id=login></div>
		</div>
	</div>
</body>
<script type="text/javascript">
$(document).ready(function(){
	$('#dimmer').show();
        $('#login').load('./auth/login.php');
});
</script>
</html>

EOF;
exit();
}
?>
<html>
<head>
		    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="cache-control" content="no-store§">
    <meta http-equiv="pragma" content="no-store">
	<title>Cassidy - Backoffice</title>
        <?php
        $size=getTillType();
        $company=getTillCompany($_COOKIE['tillIdent']);
        echo "<link rel=stylesheet type=text/css href=\"./style/site-$company-".$size['size'].".css\">";
        ?>

	 <link rel="stylesheet" href="./style/jquery-cr/jquery-ui.css">  
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<script src="./style/jquery-cr/jquery-ui.js"></script>
</head>
<body>
	<div id=page>
	<div id=wait></div>
	<div id=dialog></div>
	<div id=dimmer></div>
		<div id=header>
			<div id=login></div>
			<div id=logo>
				<p width=100% align=center><img src=./images/<?php echo $_SESSION['CO'];?>-logo.png /></p>
			</div>
			<div id=status></div>
		</div>
		<div id=body>
			<div id=menu></div>
			<div id=output></div>
		</div>
	</div>
</body>
<script type="text/javascript">
$(document).ready(function(){
	$('#login').load('./auth/login.php');
	$('#menu').load('./menu/menu.php');
});
</script>
</html>

