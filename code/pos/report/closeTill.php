<?php
include '../config.php';
include '../functions/auth_func.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$till=$_COOKIE['tillIdent'];
$tillsession=getTillSession($till);
$company=getTillCompany($till);

?>
<script type=text/javascript>
	var chk=confirm('Are you sure you want to close the day?');
	if (chk != true)
	{
		location.reload();
	}
</script>
<?php

#close all till sessions for the day
$sql_query="update till_sessions set active=0 where active=1 and company=".$company;
$doit=$db_conn->query($sql_query);

session_destroy();


?>
<script type=text/javascript>
               window.location.href='/pos';
</script>

