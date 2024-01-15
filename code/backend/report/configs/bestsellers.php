<?php

$dataset="Best Sellers";
#SQL including table joins (LEAVE TRAILING SPACE)
$sql=" 
 sum(od.qty) Qty
, sum(sto.costprice) 'Cost'
, sum(coalesce(od.actualGrand, od.grandTot)) 'Gross Value'
, sum(coalesce(od.actualVat, od.vatTot)) 'VAT Value'
, sum(coalesce(od.actualNet, od.netTot)) 'Net Value'
, round((sum(coalesce(od.actualNet, od.netTot))-sum(sto.costprice))/(sum(coalesce(od.actualNet, od.netTot)))*100,2) 'Margin' 
from stock sto, styleDetail sd, brands bra, orderdetail od, seasons sea
where sto.StockRef 	= sd.sku
and sd.brand 		= bra.id
and od.StockRef		= sto.StockRef
and sd.season		= sea.id
and od.colour		= sto.colour
and sd.company = 1
and [[DATE]] 
";

#getSelect key for select code
$filters[0]=array('brands', 'seasons', 'sku');

#Predicate equivalent
$filters[1]=array('bra.id','sea.id', 'sto.StockRef');

#Group by name
$filters[2]=array('bra.nicename','sea.nicename', 'sto.StockRef');

#Pre-select names
$filters[3]=array('bra.nicename Brand','sea.nicename Season', 'sto.StockRef SKU');

#Row titles
$filters[4]=array('Brand','Season', 'SKU');


#What is the date field called?
$date="od.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);

#Order by clause
$order_by=" sum(od.qty) desc LIMIT 30";

$title="Best Sellers";
$orient="portrait";
$rollup=0;
$debug=0;
$category="Sales";
