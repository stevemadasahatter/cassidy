<?php

$dataset="Outstanding Stock Checkin";
#SQL including table joins (LEAVE TRAILING SPACE)


$sql="
 style.sizekey, stock.Stockref SKU,stock.colour Colour, styleDetail.description Description
            , brands.nicename Brand, category.nicename Category
			, stock.costprice Cost, stock.retailprice Retail
			from styleDetail, stock, style, category, seasons, brands
			where styleDetail.sku=stock.Stockref
			and styleDetail.sku=style.sku
            and styleDetail.season = seasons.id
			and styleDetail.category=category.id
            and styleDetail.brand = brands.id
			and stock.forsale=0
";	

		

#getSelect key for select code
$filters[0]=array('brands', 'seasons', 'category');

#Predicate equivalent
$filters[1]=array('styleDetail.brand','seasons.id','category.id');

#Group by name
//$filters[2]=array('stock_pos.StockRef','stock_pos.brand','stock_pos.season','stock_pos.colour','stock_pos.size');

#Pre-select names
//$filters[3]=array('stock_pos.StockRef SKU','stock_pos.brand Brand','stock_pos.season Season','stock_pos.colour Colour', 'stock_pos.size Size');	

#Row titles
$filters[4]=array('Brand','Season','Category');

#Fixed Group by
//$groupby_fixed="stock_pos.size, stock_pos.colour, stock_pos.season, stock_pos.brand, stock_pos.StockRef,stock_pos.colour";

#Fixed Order by 
//$orderby_fixed="stockqty.sku, stockqty.colour, stockqty.sizeid";


#What is the date field called?
$date="od.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);

$title="Outstanding Stock Checkin";
$orient="portrait";
$nodate=1;
$rollup=0;
$debug=0;
$category="Stock";
?>
