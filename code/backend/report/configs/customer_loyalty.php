<?php

$dataset="Customer Spend";
#SQL including table joins (LEAVE TRAILING SPACE)
$sql=" 
c.custid 'ID', c.forename 'Forename', c.lastname 'Last Name', c.addr1 'Addr1', c.addr2 'Addr2', c.addr3 'Addr3', c.addr4 'Addr4', c.postcode 'Postcode', c.landline 'Landline', c.mobile 'Mobile',c.email 'Email'
,sum(if(abs(od.actualgrand>0),od.actualgrand*abs(qty), od.grandTot*abs(qty))) pay_value
from customers c, orderheader oh, orderdetail od
where od.transno=oh.transno
and c.custid = oh.custref
and od.status not in ( 'A','X','N','P','W','S')
and [[DATE]]
group by c.custid, c.forename, c.lastname, c.addr1, c.addr2, c.addr3, c.addr4, c.postcode, c.landline, c.mobile,c.email
order by pay_value desc
 ";

#getSelect key for select code
$filters[0]=array();

#Predicate equivalent
$filters[1]=array();

#Group by name
$filters[2]=array();

#Pre-select names
$filters[3]=array();

#Row titles
$filters[4]=array();


#What is the date field called?
$date="od.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);

#Formatting array
#$seperators=array(0,0,0,0,0,0,0,0);
#$high_titles=array('',1,'Sales',3,'',3);

$title="Customer Spend";
$orient="portrait";

$orient="portrait";
$rollup=0;
$debug=0;
$category="Customer";
