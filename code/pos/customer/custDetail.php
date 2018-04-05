<script>
function editCust(ref)
{
         $('#dialog').append('<div id=temp></div>');
         $('#dialog').css('left','45%');
         $('#dialog').css('margin-left','-10%');
	 $('#temp').load('./customer/updateCustomer.php?action=edit');
         $('#dimmer').show();
         $('#dialog').show();
}
</script>
<?php

include '../config.php';
include '../functions/auth_func.php';
session_start();

if ($_REQUEST['action']=="select")
{
	$_SESSION['custref']=$_REQUEST['cust'];
}

if ($_REQUEST['action']=='clear')
{
	$_SESSION['custref']=4;
}

if ($_SESSION['custref']<>"")
{
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$sql_query="select forename, lastname, addr1, postcode, mobile, email from customers where custid='".$_SESSION['custref']."'";
	$results=$db_conn->query($sql_query);
	
	$result=mysqli_fetch_array($results);
	
	#Don't allow edit of walkin customer
	if ($_SESSION['custid']<>4)
	{
	   echo "<table class=custdetail>
		<tr><td style=\"font-weight:bold;\">".$result['forename']." ".$result['lastname']."</td>
		<td rowspan=4><img onclick=\"javascript:editCust($custref);\" src=./images/edit.png /></td>
		</tr><tr><td>Mobile: ".$result['mobile']." </td></tr>
		<tr><td>Address: ".$result['addr1']."</td></tr>
		<tr><td>Email: ".$result['email']."</td></tr>";
	   echo "</table>";
	}

}

if ($_SESSION['custref']<>"" && $_REQUEST['action']=="select")
{
	echo "<script type=text/javascript>editCust($custref);</script>";
}

if ($_SESSION['orderno']=="" && $custref<>"")
{
	echo "<script type=text/javascript>location.reload();</script";
}

elseif ($_SESSION['orderno']<>"" && $_SESSION['custref']<>"")
{
	$update=changeCust($_SESSION['orderno'],$custref);
	updateReadout();
}
?>

<script type="text/javascript">

	$(document).ready(function(){
		$('#custresult').hide;
<?php if($_REQUEST['action']=='clear')
{
		echo "$('#custsearch').load('./customer/search.php?action=clear');";
}
else 
{
	echo "$('#custsearch').load('./customer/search.php');";
	echo "$('#itemsearchbox').load('./order/itemsearch.php');";
	echo "$('#custinfo').load('./customer/custinfo.php');";
}
	?>
	});

</script>
