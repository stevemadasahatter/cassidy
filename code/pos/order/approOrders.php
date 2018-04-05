<?php

include '../config.php';
include '../functions/auth_func.php';

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$orderno=$_REQUEST['orderno'];
$delorderno=$_REQUEST['delorderno'];
$action=$_REQUEST['action'];

session_start();

if ($action=="load")
{
//	$sql_query="delete from orderheader where transno = ".$delorderno;
//	$results=$db_conn->query($sql_query);
	$_SESSION['orderno']=$orderno;
	echo "<script type=text/javascript>location.reload();</script>";
}


$sql_query="select customers.forename, customers.lastname,customers.custid,orderheader.transno
		, date_format(orderheader.transDate,'%d-%m-%Y %H:%i:%s') transDate
		, count(*) nbr 
		, sum(if(orderdetail.actualgrand>0,orderdetail.actualgrand,orderdetail.grandTot)) grandTot
		from orderheader, orderdetail, customers 
		where orderheader.transno=orderdetail.transno
		and orderheader.custref=customers.custid ";

if ($action=="cust")
{
	echo "<h2>On-appro items</h2>";
	$sql_query.=" and orderheader.custref='".$_SESSION['custref']."' ";
}

if ($action=="all") 
{
	echo "<h2>All On-appro items</h2>";
}

if ($action=="cust" || $action=="all")
{
	$sql_query.=" and orderdetail.status='A' group by customers.forename, customers.lastname,orderheader.transno, orderheader.transDate
			 order by 1,5";
	$results=$db_conn->query($sql_query);
	
	echo "<table width=100%><tr class=bagheader><td>Customer</td><td>Order Number</td><td>Items</td><td>Total</td><td>Date of Order</td></tr>";
	while ($order=mysqli_fetch_array($results))
	{
		echo "<tr >
				<td onclick=\"javascript:showdetailcust(".$order['custid'].");\" class=clickable>".$order['forename']." ".$order['lastname']."</td>
						<td onclick=\"javascript:showdetailtrans(".$order['transno'].");\" class=clickable>".$order['transno']."</td>
				<td>".$order['nbr']."</td>";
		echo "<td>".$order['grandTot']."</td>";
		echo "<td>".$order['transDate']."</td></tr>";
	}
	echo "</table>";
	echo "<div id=detail></div>";
	if ($action=="all")
	{
		echo "<p width=100% align=right><button onclick=\"javascript:closeme();\">Close</button></p>";
	}
}

?>
<script type="text/javascript">
function showdetailcust(custid)
{
	$('#detail').load('./customer/custAppro.php?action=all&passcust='+custid);
	
}

function showdetailtrans(transno)
{
	$('#detail').load('./customer/transAppro.php?action=all&passtrans='+transno);
	
}


$(document).ready(function(){
	$('button').button();	
});

function closeme()
{
	$('#temp').remove();
	$('#dialog').hide();
	$('#dimmer').hide();
	$('#barcodeentry').focus();
}

</script>
