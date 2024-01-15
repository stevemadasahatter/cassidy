<?php
include '../config.php';
include '../functions/auth_func.php';

session_start();
$auth=check_auth();

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

#Process acceptance
if ($_REQUEST['action']=="accept")
{
	$sql_query="update orderheader set onApproAccept=1 where transno =".$_SESSION['orderno'];
	$doit=$db_conn->query($sql_query);
}
	


#Do we need to show anything?
$sql_query="select count(*) nbr from orderdetail where transno = ".$_SESSION['orderno']." and status = 'A'";
$results=$db_conn->query($sql_query);
$count=mysqli_fetch_array($results);

$sql_query="select onApproAccept from orderheader where transno=".$_SESSION['orderno'];
$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);

if ($count['nbr']>0 && $result['onApproAccept']<>1)
{
	# Show on appro panel
	echo "<br><br><h2>By ticking this box you agree that you are taking the items above On Approval<input type=checkbox onchange=\"javascript:onAppro();\" id=appro /></h2>";
	
}


if ($result['onApproAccept']==1)
{
	#Show accepted
	echo "<h2>Thank you for agreeing to our On Approval terms and conditions</h2>";
}

?>

<script type=text/javascript>
function onAppro()
{
	if ($('#appro').is(':checked'))
	{
		$('#bagAppro').load('./bagAppro.php?action=accept');
	}
}


</script>