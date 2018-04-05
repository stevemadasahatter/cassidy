<?php
$dataset="Petty Cash Breakdown";

#SQL including table joins (LEAVE TRAILING SPACE)
$sql=<<<EOF
call Pivot('pettycash_join','date_format(timestamp,"%Y-%m-%d")','Date','Descr','transamnt',"where 1=1 and [[DATE]]",'');         
EOF;

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
$date="timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);


$nodate=0;
$orient="landscape";
$debug=0;
$category="Financial";
$select_off=1;
?>
