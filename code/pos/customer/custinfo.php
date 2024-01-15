<?php

include '../config.php';
include '../functions/auth_func.php';
session_start();
$auth=check_auth();
$action=$_REQUEST['action'];
$disc=$_REQUEST['disc'];

if ($auth<>1)
{
	exit();
}
$custref=$_SESSION['custref'];

if ($custref=="" || $custref==4 || $custref==0)
{
	exit();
}
echo "<p width=100% align=right>";
echo "<button onclick=\"javascript:showPanel('custorders');\">Recent<br>Sales</button>";
echo "<button onclick=\"javascript:showPanel('custAppro');\">OnAppro<br>Items</button>";
echo "</p>";
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

?>

<script type=text/javascript>

	$(document).ready(function(){
		$('button').button();
	});

function showPanel(panel)
{
         $('#dialog').append('<div id=temp></div>');
         $('#dialog').css('top','0%');
         $('#dialog').css('left','50%');
         $('#dialog').css('margin-left','-35%');
         $('#temp').load('./customer/'+panel+'.php?action=cust');
         $('#dimmer').show();
         $('#dialog').show();

}
</script>
