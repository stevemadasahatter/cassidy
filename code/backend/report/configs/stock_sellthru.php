<?php

$dataset="Stock - Sell Through";
#SQL including table joins (LEAVE TRAILING SPACE)
$sql="
sum(summ.s_qty) 'Starting Qty', sum(summ.stocking_cost) 'Stocking Cost'
, coalesce(sum(summ.sales_qty),0) 'Sales Qty' , sum(summ.instock_cost) 'Instock Cost', coalesce(round(sum(summ.costofsale),2),0) 'Sales Cost'
, coalesce(round(avg(summ.sales_qty/summ.s_qty)*100,1),0) '%Sell Thru (Qty)' , coalesce(round(avg(summ.costofsale/summ.stocking_cost)*100,1),0) '%Sell Thru (Cost)'
from ( select stockqty.brand Brand , stockqty.sku , stockqty.colour , stockqty.sid season_id
, stockqty.season season_name , stockqty.bid brand_id , stockqty.stock , stockqty.costprice, soldqty.costofsale
, stockqty.retailprice , soldqty.qty sales_qty, if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0) adj_qty
, coalesce(stockqty.purchased,stockqty.stock) purchased, (stockqty.stock - if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0)) s_qty
, (stockqty.costprice * (stockqty.stock - if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0))) stocking_cost
, (stockqty.stock - soldqty.qty - if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0)) instock_qty
, (stockqty.costprice * (stockqty.stock - if(abs(soldqty.qty)>0,soldqty.qty,0) - if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0))) instock_cost
, (stockqty.retailprice * (stockqty.stock - if(soldqty.qty>0,soldqty.qty,0) - if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0))) instock_sales_val
, (stockqty.costprice * (if(abs(soldqty.qty)>0,soldqty.qty,0))) sales_cost
, soldqty.salesvalue sales_value
, if(abs(soldqty.salesnet)>0,soldqty.salesnet,0) sales_net , soldqty.salesvat salesvat
from (select sd.sku , sto.colour , sto.costprice , sto.retailprice , sea.nicename season
, sea.id sid , bra.id bid , bra.nicename brand , adj.qty adj_qty, coalesce((physical1 + physical2 + physical3 + physical4 + physical5
+ physical6 + physical7 + physical8 + physical9 + physical10 + physical11 + physical12 + physical13
+ physical14 + physical15),0) stock , (purchased1 + purchased2 + purchased3 + purchased4 + purchased5
+ purchased6 + purchased7 + purchased8 + purchased9 + purchased10 + purchased11 + purchased12 + purchased13
+ purchased14 + purchased15) purchased from styleDetail sd, brands bra, seasons sea, stock sto
left join (select sa.sku, sa.colour, sum(qty*sr.polarity) qty from stkadjustments sa, stkadjreason sr 
where sa.reasonid = sr.id group by sku,colour) adj
        on adj.sku = sto.StockRef and adj.colour = sto.colour
where sd.sku = sto.Stockref and bra.id = sd.brand and sea.id = sd.season) stockqty
left join (select od.StockRef , od.colour , sum(od.qty) qty, sum(od.costprice * od.qty) costofsale
, sum(if (od.zero_price=1,0,(if (abs(od.actualgrand) > 0, od.actualgrand,od.grandTot)))) salesvalue
, sum(if (od.zero_price=1,0,(if (abs(od.actualnet) > 0, od.actualnet,od.netTot)))) salesnet
, sum(if (od.zero_price=1,0,(if (abs(od.actualvat) > 0, od.actualvat,od.vatTot)))) salesvat from orderdetail od
where 1=1 and status not in ( 'A','X','N','P','W','S')
and [[DATE]]
group by od.StockRef , od.colour) soldqty
on (stockqty.sku = soldqty.StockRef and stockqty.colour = soldqty.colour) ) summ
where 1=1

";


#getSelect key for select code
$filters[0]=array('Stockref','brands', 'seasons', 'colours2');

#Predicate equivalent
$filters[1]=array('summ.sku','summ.brand_id','summ.season_id','summ.colour');

#Group by name
$filters[2]=array('summ.sku','summ.brand','summ.season_name','summ.colour');

#Pre-select names
$filters[3]=array('summ.sku SKU','summ.brand Brand','summ.season_name Season','summ.colour Colour');

#Row titles
$filters[4]=array('SKU','Brand','Season','Colour');


#What is the date field called?
$date="od.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);

$title="Stock - Sell Through";
$orient="landscape";
$rollup=1;
$debug=0;
$category="Stock";
?>
