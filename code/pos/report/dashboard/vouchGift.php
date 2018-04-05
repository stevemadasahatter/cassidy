<?php

include '../../config.php';
$type=$_REQUEST['type'];
$dateFrom=$_REQUEST['dateFrom'];
$dateTo=$_REQUEST['dateTo'];

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="SELECT c.forename, c.lastname, s.amount, s.expireDate FROM spendPots s
left join customers c
on s.custref = c.custid
where type='G'
and expireDate>now()
and usedDate is NULL";

echo "<h2 align=center>Gift Vouchers Ouststanding</h2>";

echo "<table align=center><tr><th>Customer</th><th>Amount</th><th>Expiry Date</th></tr>";

$results=$db_conn->query($sql_query);
while ($result=mysqli_fetch_array($results))
{
	echo "<tr><td>".$result['forename']." ".$result['lastname']."</td>";
	echo "<td class=totalnum>".$result['amount']."</td>";
	echo "<td class=totalnum>".$result['expireDate']."</td>";
	echo "</tr>";
}
echo "</table>";

?>
