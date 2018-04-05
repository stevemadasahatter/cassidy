<?php
$dataset="Stock Movements";

#SQL including table joins (LEAVE TRAILING SPACE)
$sql=<<<EOF
theunion.StockRef, theunion.colour, theunion.forename, theunion.lastname, theunion.size, theunion.qty, date_format(theunion.saletime,'%d/%m/%Y    %H:%i') datetime
		, theunion.Status, if(abs(theunion.actualgrand)>0, theunion.actualgrand, theunion.grandTot) price, if(abs(theunion.actualgrand)>0, theunion.grandTot-theunion.actualgrand,'0') discount
from stock stok, styleDetail sd,
(
select od.Stockref,od.colour colour, c.forename forename, c.lastname lastname
, ELT(FIELD(od.status,
'A', 'X', 'C', 'J', 'K'),'OnAppro','OnAppro Return','Sold','Returned','Returned') Status
, od.timestamp saletime, qty, size , od.actualgrand, od.grandtot
from orderdetail od, customers c, orderheader oh
where 1=1
and c.custid = oh.custref
and od.transno = oh.transno
union
select stk.sku,'Stock' forename, 'Adjustment' lastname, stk.colour colour, rea.nicename Status, stk.datetrack saletime,
case stk.sizeid
when 1 then size1
when 2 then size2
when 3 then size3
when 4 then size4
when 5 then size5
when 6 then size6
when 7 then size7
when 8 then size8
when 9 then size9
when 10 then size10
when 11 then size11
when 12 then size12
when 13 then size13
when 14 then size14
when 15 then size15
when 16 then size16
when 17 then size17
when 18 then size18
when 19 then size19
when 20 then size20
END size
, stk.qty qty,'',''
from stkadjustments stk, stkadjreason rea, sizes s, style st
where stk.reasonid = rea.id
and st.sizekey=s.sizekey
and st.sku = stk.sku
union
select sto.StockRef, 'Stock' forename, 'Receipt' lastname, sto.colour, concat('Receipt - ',sto.delnote), sto.delDate
,CASE
WHEN nums.ROW=1 THEN sto.physical1
WHEN nums.ROW=2 THEN sto.physical2
WHEN nums.ROW=3 THEN sto.physical3
WHEN nums.ROW=4 THEN sto.physical4
WHEN nums.ROW=5 THEN sto.physical5
WHEN nums.ROW=6 THEN sto.physical6
WHEN nums.ROW=7 THEN sto.physical7
WHEN nums.ROW=8 THEN sto.physical8
WHEN nums.ROW=9 THEN sto.physical9
WHEN nums.ROW=10 THEN sto.physical10
WHEN nums.ROW=11 THEN sto.physical11
WHEN nums.ROW=12 THEN sto.physical12
WHEN nums.ROW=13 THEN sto.physical13
WHEN nums.ROW=14 THEN sto.physical14
WHEN nums.ROW=15 THEN sto.physical15
WHEN nums.ROW=16 THEN sto.physical16
WHEN nums.ROW=17 THEN sto.physical17
WHEN nums.ROW=18 THEN sto.physical18
WHEN nums.ROW=19 THEN sto.physical19
WHEN nums.ROW=20 THEN sto.physical20
END physical
, CASE
WHEN nums.ROW=1 THEN sizes.size1
WHEN nums.ROW=2 THEN sizes.size2
WHEN nums.ROW=3 THEN sizes.size3
WHEN nums.ROW=4 THEN sizes.size4
WHEN nums.ROW=5 THEN sizes.size5
WHEN nums.ROW=6 THEN sizes.size6
WHEN nums.ROW=7 THEN sizes.size7
WHEN nums.ROW=8 THEN sizes.size8
WHEN nums.ROW=9 THEN sizes.size9
WHEN nums.ROW=10 THEN sizes.size10
WHEN nums.ROW=11 THEN sizes.size11
WHEN nums.ROW=12 THEN sizes.size12
WHEN nums.ROW=13 THEN sizes.size13
WHEN nums.ROW=14 THEN sizes.size14
WHEN nums.ROW=15 THEN sizes.size15
WHEN nums.ROW=16 THEN sizes.size16
WHEN nums.ROW=17 THEN sizes.size17
WHEN nums.ROW=18 THEN sizes.size18
WHEN nums.ROW=19 THEN sizes.size19
WHEN nums.ROW=20 THEN sizes.size20
END size
, '',''
from stock sto, sizes, style s,
(SELECT @ROW := @ROW + 1 AS ROW
FROM orderdetail t
join (SELECT @ROW := 0) t2
LIMIT 20
) nums
where s.sku = sto.StockRef
and s.sizekey = sizes.sizekey
having physical <>''
) theunion
where stok.StockRef = theunion.StockRef
and sd.sku = stok.Stockref 
EOF;

#getSelect key for select code
$filters[0]=array('seasons', 'brands','transtype','reason_code');

#Predicate equivalent
$filters[1]=array('sd.season', 'sd.brands');

#Group by name
$filters[2]=array();

#Pre-select names
$filters[3]=array();

#Row titles
$filters[4]=array();

#What is the date field called?
$date="datetime";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);

#Orientation
$orient="landscape";
$title="Stock Movements Audit Report";
$debug=1;
$category="Stock";
?>

