<?php

$dataset="Financial Report";
#SQL including table joins (LEAVE TRAILING SPACE)
$sql=" 
sum(ten.PayValue) Payment
, round(((sum(od.actualnet*(100-oh.discount)/100) - sum(sto.costprice))/sum(od.actualnet*(100-oh.discount)/100))*100,1) as margin
from orderdetail od, styleDetail style, seasons sea, brands bra, stock sto, category cat, colours col, tenders ten, TenderTypes tent, orderheader oh
where 1=1
and od.StockRef = sto.Stockref
and od.colour = sto.colour
and od.transno = oh.transno
and od.StockRef = style.sku
and style.season = sea.id
and style.brand = bra.id
and sto.colour = col.colour
and style.category=cat.id
and ten.transno = od.transno
and ten.PayMethod = tent.PayId
and [[DATE]] ";

#getSelect key for select code
$filters[0]=array('seasons', 'brands', 'colours','category', 'paytype');

#Predicate equivalent
$filters[1]=array('sea.id','bra.id','col.id','cat.id','tent.PayId');

#Group by name
$filters[2]=array('sea.nicename','bra.brand','col.colour','cat.nicename','tent.payDescr');

#Pre-select names
$filters[3]=array('sea.nicename Season','bra.brand Brand','col.colour Colour','cat.nicename Category','tent.payDescr PayType');

#Row titles
$filters[4]=array('Season','Brand','Colour','Category','Pay Method');


#What is the date field called?
$date="od.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);
$category="Financial";
$debug=0;
