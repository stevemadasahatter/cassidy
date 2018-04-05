<?php
$dataset="Import by Delivery Note";

#SQL including table joins (LEAVE TRAILING SPACE)
$sql=<<<EOF
* from (select
concat(sd.sku,'-Sizes') SKU,sto.colour Colour,size1
, size2
, size3
, size4
, size5
, size6
, size7
, size8
, size9
, size10
, size11
, size12
, size13
, size14
, size15
, size16
, sto.delnote,sto.deldate
from sizes s, style sd, stock sto
where s.sizekey = sd.sizekey
and sd.sku = sto.Stockref
union
select concat(sd.sku,'-Holding'),sto.colour,sto.physical1
,sto.physical2
,sto.physical3
,sto.physical4
,sto.physical5
,sto.physical6
,sto.physical7
,sto.physical8
,sto.physical9
,sto.physical10
,sto.physical11
,sto.physical12
,sto.physical13
,sto.physical14
,sto.physical15
,sto.physical16
,sto.delnote, sto.deldate
from sizes s, style sd, stock sto
where s.sizekey = sd.sizekey
and sd.sku = sto.Stockref
) foo 
where 1=1 
EOF;

#getSelect key for select code
$filters[0]=array('delnote','deldate');

#Predicate equivalent
$filters[1]=array('delnote','deldate');

#Group by name
$filters[2]=array();

#Pre-select names
$filters[3]=array();

#Row titles
$filters[4]=array('Delivery Note','Delivery Date');

#What is the date field called?
$date="";

#can we group by everything (to be implemented)
//$group_by=array();

$orderby_fixed="2,1 desc";

#Orientation
$orient="landscape";
$title="Imports by Delivery Note";
$debug=0;
$category="Stock";
?>

