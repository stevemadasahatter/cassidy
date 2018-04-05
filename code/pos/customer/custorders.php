<?php 

include '../config.php';
include '../functions/auth_func.php';
session_start();
$auth=check_auth();
$action=$_REQUEST['action'];

if ($auth<>1)
{
        exit();
}
$custref=$_SESSION['custref'];
if ($custref=="")
{
		echo "<p>You must select a customer in order to list their information</p>";
        exit();
}

if ($action=="load")
{
	$_SESSION['orderno']=$_REQUEST['transno'];
	$_SESSION['custref']=$_REQUEST['custref'];
	echo "<script type=text/javascript>location.reload();</script>";
}
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="select od.transno, od.StockRef, coalesce(od.actualgrand,od.grandTot) total, od.lineno, od.size, 
		date_format(od.timestamp, '%d/%m/%Y') transDate, od.colour, od.status from orderdetail od, orderheader oh 
		where od.status in ('C', 'A', 'J','K','V')
		and oh.transno=od.transno
		and oh.custref=$custref
		order by oh.transDate desc, lineno asc
		";
$results=$db_conn->query($sql_query);

echo "<table><tr>";
echo "<th>Order#</th><th>Date</th><th>Code/SKU</th><th>Colour</th><th>Size</th><th>Price</th><th>Status</th></tr>";
while ($result=mysqli_fetch_array($results))
{
	echo "<tr onclick=\"javascript:loadOrder(".$result['transno'].",".$custref.");\">";
	echo "<td>".$result['transno']."</td>";
	echo "<td>".$result['transDate']."</td>";
	echo "<td>".$result['StockRef']."</td>";
	echo "<td>".$result['colour']."</td>";
	echo "<td>".$result['size']."</td>";
	echo "<td>".$result['total']."</td>";
	if ($result['status']=='C')
	{
		echo "<td>Sold</td>";
	}
	elseif ($result['status']=='V')
	{
	    echo "<td>Sold (Returned)</td>";
	}
	elseif ($result['status']=='J' || $result['status']=='K')
	{
		echo "<td>Returned</td>";
	}
	else 
	{
	    echo "<td>On-Appro</td>";
	}
	echo "</tr>";		
}
echo "</table>";

echo "<p width=100% align=right><button onclick=\"javascript:closeDiag();\">Close</button></p>";
?>

<script type=text/javascript>
$(document).ready(function(){
		$('button').button();
});

function closeDiag()
{
        $('#temp').remove();
        location.reload();
}

function loadOrder(orderno,custref)
{
	$('#temp').load('./customer/custorders.php?action=load&transno='+orderno+'&custref='+custref);
}

</script>
