<?php 

include '../config.php';
include '../functions/auth_func.php';
session_start();
$auth=check_auth();
$action=$_REQUEST['action'];
$disc=$_REQUEST['disc'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($_REQUEST['passtrans']<>"")
{
	$orderno=$_REQUEST['passtrans'];
	$_SESSION['orderno']=$custref;
}
else 
{
	$orderno=$_REQUEST['orderno'];
	$_SESSION['orderno']=$_REQUEST['orderno'];
}

if ($auth<>1)
{
        exit();
}

if ($orderno=="")
{
	echo "<p>You must select a transaction in order to list the information</p>";
        exit();
}

if ($action=="load")
{
	$sql_query="select custref from orderheader where transno=".$_REQUEST['orderno'];
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$_SESSION['orderno']=$_REQUEST['orderno'];
	$_SESSION['custref']=$result['custref'];

	echo "<script type=text/javascript>location.reload();</script>";
	exit();
}

$sql_query="select od.transno, od.StockRef, if(od.actualgrand>0,od.actualgrand,od.grandTot) grandTot, od.lineno, od.size, od.colour, od.status from orderdetail od, orderheader oh 
		where od.status ='A'
		and oh.transno=od.transno
		and oh.transno = '".$orderno."'
		order by oh.transDate desc, lineno asc
		limit 10";


$results=$db_conn->query($sql_query);
$num_rows=mysqli_affected_rows($db_conn);

if ($num_rows==0)
{
	echo "<h2>There are no On-Appro Items for this customer</p>";
}
else {
	

		echo "<table><tr>";
		echo "<th>Order Number</th><th>Code/SKU</th><th>Colour</th><th>Size</th><th>Price</th><th>Status</th></tr>";
		while ($result=mysqli_fetch_array($results))
		{
			echo "<tr onclick=\"javascript:loadOrder(".$result['transno'].");\">";
			echo "<td class=clickable>".$result['transno']."</td>";
			echo "<td>".$result['StockRef']."</td>";
			echo "<td>".$result['colour']."</td>";
			echo "<td>".$result['size']."</td>";
			echo "<td>".$result['grandTot']."</td>";
			if ($result['status']=='C')
			{
				echo "<td>Complete</td>";
			}
			else
			{
				echo "<td>On-Appro</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
}
if ($_REQUEST['action']<>"all")
{
	echo "<p width=100% align=right><button onclick=\"javascript:closeDiag();\">Close</button></p>";
}	

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

function loadOrder(orderno)
{
	$('#temp').load('./customer/transAppro.php?action=load&orderno='+orderno);
}
</script>
