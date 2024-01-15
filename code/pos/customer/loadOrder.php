<?php

include '../config.php';
include '../functions/auth_func.php';
session_start();
$auth=check_auth();
$till=$_COOKIE['tillIdent'];
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="search")
{
	if ($_REQUEST['orderno']<>"")
	{
		$sql_query="select orderheader.custref, orderheader.transno, orderheader.transDate, customers.forename, customers.lastname from orderheader, customers 
				where orderheader.transno like '%".$_REQUEST['orderno']."%' and customers.custid = orderheader.custref
						and orderheader.status = 'C' 
						order by orderheader.transno desc limit 5";
		$results=$db_conn->query($sql_query);
		echo "<table><tr><td>Cust Ref</td><td>Order#</td><td>Order Date</td><td>Forename</td><td>Lastname</td></tr>";
		while ($result=mysqli_fetch_array($results))
		{
			echo "<tr onclick=\"javascript:loadOrder(".$result['transno'].",".$result['custref'].");\"><td>".$result['custref']."</td><td class=clickable>".$result['transno']."</td><td>".$result['transDate']."</td><td>".$result['forename']."</td><td>".$result['lastname']."</td></tr>";
		}
	}
	elseif ($_REQUEST['custref']<>"")
	{
		$sql_query="select orderheader.custref, orderheader.transno, orderheader.transDate, customers.forename, customers.lastname from orderheader, customers
				where concat(customers.forename,' ',customers.lastname) like '%".$_REQUEST['custref']."%' and customers.custid = orderheader.custref
						 and orderheader.status = 'C' 
						order by orderheader.transno desc limit 10";
		$results=$db_conn->query($sql_query);
		echo "<table><tr><td>Cust Ref</td><td>Order#</td><td>Order Date</td><td>Forename</td><td>Lastname</td></tr>";
		while ($result=mysqli_fetch_array($results))
		{
			echo "<tr onclick=\"javascript:loadOrder(".$result['transno'].",".$result['custref'].");\"><td>".$result['custref']."</td><td class=clickable>".$result['transno']."</td><td>".$result['transDate']."</td><td>".$result['forename']."</td><td>".$result['lastname']."</td></tr>";
		}
	}
	exit();
}

if ($action=="load")
{
	$_SESSION['orderno']=$_REQUEST['transno'];
	$_SESSION['custref']=$_REQUEST['custref'];
	echo "<script type=text/javascript>location.reload();</script>";
	exit();
}

echo "<h2>Order Search</h2><p align=right width=100%></p>";

echo "<table>";
echo "<tr><th>Order Number (Scan)</th><th>Customer</th>";
echo "<tr><td><input id=order onkeyup=\"javascript:listOrderno();\"></td><td><input id=cust onkeyup=\"javascript:listCust();\"></td></tr>";
echo "</table>";

echo "<div id=results></div>";

echo "<p width=100% align=right><button onclick=\"javascript:$('#temp').remove;$('#dialog').hide();$('#dimmer').hide(); \">Close</button></p>";


?>

<script type="text/javascript">
function listOrderno()
{
	var orderno=$('#order').val();
	if (orderno.length>=5)
	{	
		$('#results').load('./customer/loadOrder.php?action=search&orderno='+orderno);
	}
}

function listCust()
{
	var custref=$('#cust').val();
	$('#results').load('./customer/loadOrder.php?action=search&custref='+custref);
}

function loadOrder(orderno,custref)
{
	$('#results').load('./customer/loadOrder.php?action=load&transno='+orderno+'&custref='+custref);
}

$(document).ready(function(){
	$('button').button();
	$('#order').focus();
});
</script>
