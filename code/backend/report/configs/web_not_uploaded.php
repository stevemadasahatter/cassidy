<?php
$dataset="Web Outstanding Stock";

#SQL including table joins (LEAVE TRAILING SPACE)
$sql=<<<EOF
 w.sku 'SKU', s.colour 'Colour',  w.name 'Name'
		, st.description 'Decription', b.nicename 'Brand', sea.season 'Season'
	from webDetails w, stock s, styleDetail sd, seasons sea, brands b, style st
	where 1 = 1
	and w.sku = s.Stockref
	and w.colour = s.colour
    and sd.season = sea.id
    and sd.brand = b.id
    and sd.sku = s.Stockref
    and st.sku = s.Stockref
	and s.web_complete = 0
	and s.web_uploaded = 0
 
EOF;

#getSelect key for select code
$filters[0]=array('brands', 'seasons');

#Predicate equivalent
$filters[1]=array('b.id','sea.id');

#Group by name
$filters[2]=array();

#Pre-select names
$filters[3]=array();

#Row titles
$filters[4]=array('Brand','Season');


#What is the date field called?
$date="t.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);


$nodate=1;
$orient="landscape";
$debug=0;
$category="Stock";
?>
