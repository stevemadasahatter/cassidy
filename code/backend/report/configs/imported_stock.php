<?php

$dataset="Imported Stock Report";
#SQL including table joins (LEAVE TRAILING SPACE)
$sql=" 
sto.StockRef, sto.colour, sea.season, bra.brand
from styledetail sd, stock sto, brands bra, seasons sea, colours col, category cat
where bra.id = sd.brand
and sea.id = sd.season
and col.colour = sto.colour
and cat.id = sd.category		
and sd.sku = sto.StockRef
";

#getSelect key for select code
$filters[0]=array('brands', 'seasons', 'colours','category');

#Predicate equivalent
$filters[1]=array('bra.id','sea.id','col.id','cat.id');

#Group by name
$filters[2]=array('bra.nicename','sea.nicename','col.colour','cat.nicename');

#Pre-select names
$filters[3]=array('bra.nicename Brand','sea.nicename Season','col.colour Colour','cat.nicename Category');

#Row titles
$filters[4]=array('Brand','Season','Colour','Category');


#What is the date field called?
//$date="od.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);

$title="Sales Report - Summary";
$orient="portrait";

$orient="portrait";
$rollup=0;
$debug=0;
$category="Stock";
