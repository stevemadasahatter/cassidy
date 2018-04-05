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
select pg.nicename Grp
	, sum(od.qty) Qty
    , round((sum(od.qty/summs.qty)*100),2) Qtypct
    , sum(if (od.zero_price=1,0,(if (abs(od.actualgrand) > 0, od.actualgrand*abs(od.qty),od.grandTot*abs(od.qty))))) Value
	, round((sum(if(abs(od.actualgrand)>0,od.actualgrand, od.grandTot)*abs(od.qty)/summs.vals))*100,2) Valuepct
from orderdetail od, stock sto, styleDetail sd, brands bra, category cat, ProductGroup pg
cross join
(select sum(od.qty) qty, sum(if(abs(od.actualgrand)>0,od.actualgrand, od.grandTot)*abs(od.qty)) as vals
from orderdetail od, stock sto, styleDetail sd, brands bra, category cat, ProductGroup pg
where od.StockRef = sto.StockRef
and sto.company = sd.company
and od.StockRef = sd.sku
and sd.brand = bra.id
and sd.category=cat.id
and sd.ProductGroup = pg.id
and od.colour =sto.colour
and od.status not in ( 'A','X','N','P','W','S')
and od.timestamp between $datePred) summs
where od.StockRef = sto.StockRef
and od.StockRef = sd.sku
and od.colour =sto.colour
and od.status not in ( 'A','X','N','P','W','S')
and sd.brand = bra.id
and sd.category=cat.id
and sd.ProductGroup = pg.id
and sto.company = sd.company
and od.timestamp between $datePred
group by pg.nicename
with rollup		";

echo "<h2 align=center>Sales by Group</h2>";

echo "<table align=center><tr><th>Group</th><th>Qty</th><th>%Qty</th><th>Value</th><th>%Value</th></tr>";

$results=$db_conn->query($sql_query);
while ($result=mysqli_fetch_array($results))
{
	if ($result['Grp']=="")
	{
		$result['Grp']="Totals";
	}
	echo "<tr><td>".$result['Grp']."</td>";
	echo "<td class=totalnum>".$result['Qty']."</td>";
	echo "<td class=totalnum>".$result['Qtypct']."</td>";
	echo "<td class=totalnum>".$result['Value']."</td>";
	echo "<td class=totalnum>".$result['Valuepct']."</td>";
	echo "</tr>";
}
echo "</table>";
