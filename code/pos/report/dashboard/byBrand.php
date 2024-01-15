<style>
table th
{
    padding-right:0px !important;
}

</style>
<?php

include '../../config.php';
$type=$_REQUEST['type'];
$dateFrom=$_REQUEST['dateFrom'];
$dateTo=$_REQUEST['dateTo'];

ob_start();
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
select bra.nicename Grp
	, sum(od.qty) Qty
    , round((sum(od.qty/summs.qty))*100,2) Qtypct
    , round(sum(if (od.zero_price=1,0,(if (abs(od.actualgrand) > 0, od.actualgrand*abs(od.qty),od.grandTot*abs(od.qty))))),2) Value
	, round((sum(if (od.zero_price=1,0,(if (abs(od.actualgrand) > 0, od.actualgrand*abs(od.qty),od.grandTot*abs(od.qty))))/summs.vals)*100),1) Valuepct
from orderdetail od, stock sto, styleDetail sd, brands bra, category cat, ProductGroup pg, orderheader oh
cross join
(select sum(od.qty) qty, sum(if (od.zero_price=1,0,(if (abs(od.actualgrand) > 0, od.actualgrand*abs(od.qty),od.grandTot*abs(od.qty))))) as vals
from orderdetail od, stock sto, styleDetail sd, brands bra, category cat, ProductGroup pg, orderheader oh
where od.StockRef = sto.StockRef
and od.transno = oh.transno
and sto.company = sd.company
and od.StockRef = sd.sku
and sd.brand = bra.id
and sd.category=cat.id
and od.colour = sto.colour
and od.status not in  ('A','X')
and sd.ProductGroup = pg.id
and od.timestamp between $datePred) summs
where od.StockRef = sto.StockRef
and od.transno = oh.transno
and od.StockRef = sd.sku
and sd.brand = bra.id
and sd.category=cat.id
and od.colour = sto.colour
and sd.ProductGroup = pg.id
and od.status not in ( 'A','X','N','P','W','S')
and sto.company = sd.company
and od.timestamp between $datePred
group by bra.nicename
with rollup";

echo "<h2 align=center>Sales by Brand</h2>";

echo "<table align=center><tr><th>Brand</th><th align=right>Qty</th><th align=right>%Qty</th><th align=right>Value</th><th align=right>%Value</th></tr>";

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

ob_end_flush();
