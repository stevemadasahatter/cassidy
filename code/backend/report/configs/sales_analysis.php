<?php

$dataset="Sales Analysis";
#SQL including table joins (LEAVE TRAILING SPACE)
$sql=" 
sum(qty) as Qty, sum(if (od.zero_price=1,0,(if (abs(od.actualgrand) > 0, od.actualgrand*abs(qty),od.grandTot*abs(qty))))) 'Sales'
, round(sum(if (od.zero_price=1,0,(if (abs(od.actualvat) > 0, od.actualvat*abs(qty),od.vatTot*abs(qty))))),2) VAT
, round(sum(if (od.zero_price=1,0,(if (abs(od.actualnet) > 0, od.actualnet*abs(qty),od.netTot*abs(qty))))),2) 'Net Sales'
, round(sum(if(abs(od.costprice)>0,abs(od.costprice)*qty,0)),2) 'Cost'
, round((sum(if (od.zero_price=1,0,(if (abs(od.actualnet) > 0, od.actualnet*abs(qty),od.nettot*abs(qty))))) - sum(if(abs(od.costprice)>0,abs(od.costprice)*qty,0))),2) as 'Gross Profit'
, round((sum(if (od.zero_price=1,0,(if (abs(od.actualnet) > 0, od.actualnet*abs(qty),od.nettot*abs(qty))))) - sum(if(abs(od.costprice)>0,abs(od.costprice)*qty,0)))
		/sum(if (abs(od.actualnet) > 0, od.actualnet*abs(qty),od.nettot*abs(qty)))*100,2) as 'Margin %'
from orderdetail od, styleDetail style, seasons sea, brands bra, stock sto, category cat, colours col, ProductGroup pg
where 1=1
and od.StockRef = sto.Stockref
and od.colour = sto.colour
and od.StockRef = style.sku
and style.season = sea.id
and style.brand = bra.id
and style.productgroup = pg.id
and sto.colour = col.colour
and style.category=cat.id
and od.status not in ( 'A','X','N','P','W','S')
and [[DATE]] ";

#getSelect key for select code
$filters[0]=array('brands', 'seasons', 'colours','category', 'Productgroup', 'StockRef');

#Predicate equivalent
$filters[1]=array('bra.id','sea.id','col.id','cat.id','pg.id','od.StockRef');

#Group by name
$filters[2]=array('bra.nicename','sea.nicename','col.colour','cat.nicename', 'pg.nicename', 'od.StockRef');

#Pre-select names
$filters[3]=array('bra.nicename Brand','sea.nicename Season','col.colour Colour','cat.nicename Category', 'pg.nicename Dept', 'od.StockRef SKU');

#Row titles
$filters[4]=array('Brand','Season','Colour','Category','Group', 'StockRef');


#What is the date field called?
$date="od.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);

#Formatting array
$seperators=array(1,1,0,0,1,0,0,0);
$high_titles=array('',1,'Sales',3,'',3);

$title="Sales Report - Summary";
$orient="portrait";

$orient="portrait";
$rollup=1;
$debug=0;
$category="Sales";
