<?php

$dataset="Stock - General Overview";
#SQL including table joins (LEAVE TRAILING SPACE)
$sql="
sum(summ.s_qty) 'Act Qty', sum(summ.stocking_cost) 'St Cost'
, sum(summ.stock) - sum(summ.sales_qty) - sum(summ.adj_qty) 'C Qty' , sum(summ.instock_cost) 'C Cost'
, sum(summ.sales_qty) 'S Qty' , round(coalesce(sum(summ.costofsale),0),2) 'S Cost'
, sum(coalesce(summ.sales_value,0)) 'S Sales' , round(sum(coalesce(summ.salesvat,0)),2) 'VAT'
, round(coalesce(sum(summ.sales_value),0)-sum(coalesce(salesvat,0))-sum(coalesce(summ.costofsale,0)),2) Profit
, coalesce(ROUND((SUM(COALESCE(summ.sales_net, 0)) - SUM(COALESCE(summ.costofsale, 0))) / (SUM(COALESCE(summ.sales_net, 0))) * 100,1),0) 'Margin (%)'
, round(sum(coalesce(summ.sales_value,0))-sum(coalesce(salesvat,0))-sum(summ.stocking_cost),2) 'Actual Profit'
, coalesce(ROUND(((SUM(COALESCE(summ.sales_value, 0)) - SUM(COALESCE(salesvat, 0)) - SUM(coalesce(summ.stocking_cost,0))) / SUM(coalesce(summ.stocking_cost,0))) * 100,1),0) 'Actual Profit (%)'
from ( select stockqty.brand Brand , stockqty.sku , stockqty.colour , stockqty.sid season_id, stockqty.cid cid, stockqty.category category, stockqty.description
, stockqty.season season_name , stockqty.sea sea, stockqty.bid brand_id , stockqty.stock , stockqty.costprice, soldqty.costofsale
, stockqty.retailprice , coalesce(soldqty.qty,0) sales_qty, if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0) adj_qty
, coalesce(stockqty.purchased,stockqty.stock) purchased, (stockqty.stock - if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0)) s_qty
, (stockqty.costprice * (stockqty.stock - if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0))) stocking_cost
, (stockqty.stock - coalesce(soldqty.qty,0) - if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0)) instock_qty
, (stockqty.costprice * (stockqty.stock - if(abs(soldqty.qty)>0,soldqty.qty,0) - if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0))) instock_cost
, (stockqty.retailprice * (stockqty.stock - if(soldqty.qty>0,soldqty.qty,0) - if(abs(stockqty.adj_qty)>0,stockqty.adj_qty,0))) instock_sales_val
, (stockqty.costprice * (if(abs(soldqty.qty)>0,soldqty.qty,0))) sales_cost
, soldqty.salesvalue sales_value
, if(abs(soldqty.salesnet)>0,soldqty.salesnet,0) sales_net , soldqty.salesvat salesvat
from (select sd.sku , sd.description, sto.colour , sto.costprice , sto.retailprice , sea.nicename season, sea.season sea, cat.nicename category, cat.id cid
, sea.id sid , bra.id bid , bra.nicename brand , adj.qty adj_qty, coalesce((physical1 + physical2 + physical3 + physical4 + physical5
+ physical6 + physical7 + physical8 + physical9 + physical10 + physical11 + physical12 + physical13
+ physical14 + physical15),0) stock , (purchased1 + purchased2 + purchased3 + purchased4 + purchased5
+ purchased6 + purchased7 + purchased8 + purchased9 + purchased10 + purchased11 + purchased12 + purchased13
+ purchased14 + purchased15) purchased from styleDetail sd, brands bra, seasons sea, category cat, stock sto
left join (select sa.sku, sa.colour, sum(qty*sr.polarity) qty from stkadjustments sa, stkadjreason sr
where sa.reasonid = sr.id group by sku,colour) adj
        on adj.sku = sto.StockRef and adj.colour = sto.colour
where sd.sku = sto.Stockref and bra.id = sd.brand and sea.id = sd.season and cat.id = sd.category) stockqty
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
$filters[0]=array('brands', 'seasons', 'colours2','category', 'StockRef');

#Predicate equivalent
$filters[1]=array('summ.brand_id','summ.season_id','summ.colour','summ.cid', 'concat(summ.sku," - ", summ.description)');

#Group by name
$filters[2]=array('summ.brand','summ.sea','summ.colour','summ.category', 'concat(summ.sku," - ", summ.description)');

#Pre-select names
$filters[3]=array('summ.brand Brand','summ.sea Season','summ.colour Colour','summ.category Category', 'concat(summ.sku," - ", summ.description) SKU');

#Row titles
$filters[4]=array('Brand','Season','Colour','Category', 'SKU');

#Formatting array
$seperators=array(1,0,1,0,1,0,0,0,1,0,0,0,1);
$high_titles=array('Ordered',2,'Stock',2,'Sales',4,'Profit',4);

#What is the date field called?
$date="od.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);

$title="Stock - General Overview";
$orient="landscape";
$rollup=1;
$debug=0;
$category="Stock";
?>
