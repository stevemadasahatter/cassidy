<?php

include '../config.php';
include '../functions/auth_func.php';

session_start();
$auth=check_auth();

$till=$_COOKIE['tillIdent'];
$tillsession=getTillSession($till);

$orderno=$_SESSION['orderno'];

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="select transno, custref, action from readout";
$results=$db_conn->query($sql_query);

$result=mysqli_fetch_array($results);

$_SESSION['custref']=$result['custref'];
$custref=$_SESSION['custref'];

if ($_REQUEST['action']=="update")
{
	if ($_REQUEST['type']=="text")
	{
		$sql_query="update customers set textmkt =".$_REQUEST['value']." where custid=".$custref;
	}

	if ($_REQUEST['type']=="email")
	{
		$sql_query="update customers set emailmkt =".$_REQUEST['value']." where custid=".$custref;
	}

	if ($_REQUEST['type']=='addr')
	{
		$sql_query="update customers set email ='".$_REQUEST['value']."' where custid=".$custref;
	}
	$doit=$db_conn->query($sql_query);
}

#Get order lines
$sql_query="select emailmkt, textmkt, email, forename from customers where custid = $custref";

$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);

if ($result['forename']<>"New")
{
	echo "<div style\"width:100%;\">";
	echo "<p class=cust >As a valued customer, we would like the opportunity to keep you up to date with offers and information. Your information is never shared.</p>  ";
	echo "<table>";
	echo "<tr><td>Saved email address</td><td><input style=\"width:400px;\" type=text name=email value=\"".$result['email']."\" /></td><td><button id=butsave onclick=\"javascript:saveEmail();\">Save</button></tr>";

	echo "<tr><td>Would you like to receive</td><td></td></tr>";
	echo "<tr><td>Text messages about sales and offers?</td><td><input onchange=\"javascript:updatetextmkt();\"type=checkbox id=textmkt";
	if ($result['textmkt']==1)
	{
		echo " checked ";
	}
	echo "></input></td></tr>";
	echo "<tr><td>Emails about sales and offers?</td><td><input onchange=\"javascript:updateemailmkt();\" type=checkbox id=emailmkt ";
	if ($result['emailmkt']==1)
	{
		echo " checked ";
	}		
	echo " ></input></td></tr>";
	echo "</table>";
}
else
{
	echo "<p class=cust>Would you like to become a cherished customer? Benefits include emailed receipts, and text messages and emails informing you of forthcoming sales, offers and special evenings. Please ask! We'd love to have you onboard!</p>";
}


?>

<script type="text/javascript">

function saveEmail()
{
	var email;
	email=$('input[name=email]').val();	
	$('#bagemail').load('../Readout/bagEmail.php?action=update&type=addr&value='+email);
}	

function updateemailmkt()
{
	if ($('#emailmkt').is(':checked'))
	{
		var value=1;
	}
	else
	{
		var value=0;
	}
	$('#bagemail').load('../Readout/bagEmail.php?action=update&type=email&value='+value);
	
}

function updatetextmkt()
{
	if ($('#textmkt').is(':checked'))
	{
		var value=1;
	}
	else
	{
		var value=0;
	}
	$('#bagemail').load('../Readout/bagEmail.php?action=update&type=text&value='+value);
	
}
</script>
