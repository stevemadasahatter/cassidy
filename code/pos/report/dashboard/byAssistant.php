<?php

include '../../config.php';
$type=$_REQUEST['type'];
$dateFrom=$_REQUEST['dateFrom'];
$dateTo=$_REQUEST['dateTo'];

#Work out the date predicate
if ($type=="now")
{
	$datePred="current_date() and now() ";
}

if ($type=="yesterday")
{
    $datePred="date_sub(current_date(), interval 1 day) and current_date() ";
}

if ($type=="range")
{
	$datePred="STR_TO_DATE('$dateFrom', '%Y-%m-%d') and date_add(STR_TO_DATE('$dateTo', '%Y-%m-%d'), interval 1 day) ";
}

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="
select u.username Grp
	, sum(od.qty) Qty
    , round((sum(od.qty/summs.qty))*100,1) '%Qty'
    , round(sum(if (od.zero_price=1,0,(if (abs(od.actualgrand) > 0, od.actualgrand*abs(od.qty),od.grandTot*abs(od.qty))))),2) Value
	, round((sum((if(abs(od.actualgrand)>0,od.actualgrand, od.grandTot)*abs(od.qty))/summs.vals))*100,1) '%Value'
from orderdetail od, orderheader oh, users u
cross join
(select sum(od.qty) qty, sum(if(abs(od.actualgrand)>0,od.actualgrand, od.grandTot)*od.qty) as vals
from orderdetail od, orderheader oh, users u
where od.transno = oh.transno
and oh.cashierid = u.username
and od.status not in  ('A','X')
and od.timestamp between $datePred) summs
where od.transno = oh.transno
and oh.cashierid = u.username
and od.status not in ( 'A','X','N','P','W','S')
and od.timestamp between $datePred
group by u.username
with rollup";

echo "<h2 align=center>Sales by Assistant</h2>";

echo "<table width=100% align=center><tr><th>Assistant</th><th align=right>Qty</th><th align=right>%Qty</th><th align=right>Value</th><th align=right>%Value</th></tr>";

$results=$db_conn->query($sql_query);

while ($result=mysqli_fetch_array($results))
{
	if ($result['Grp']=="")
	{
		$result['Grp']="Totals";
	}
	echo "<tr><td>".$result['Grp']."</td>";
	echo "<td class=totalnum>".$result['Qty']."</td>";
	echo "<td class=totalnum>".$result['%Qty']."</td>";
	echo "<td class=totalnum>".$result['Value']."</td>";
	echo "<td class=totalnum>".$result['%Value']."</td>";
	echo "</tr>";
}
echo "</table>";
