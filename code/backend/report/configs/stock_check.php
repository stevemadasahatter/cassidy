<?php

$dataset="Stock Check Report";
#SQL including table joins (LEAVE TRAILING SPACE)


$sql="
            stockqty.sku,
			stockqty.description,
            stockqty.colour,
            stockqty.size,
            coalesce(if(abs(stockqty.stock)>0, stockqty.stock,0) - if(abs(soldqty.qty)>0,soldqty.qty,0) - IF(ABS(stockqty.adj_qty) > 0, stockqty.adj_qty, 0),0) 'In Stock',
		    coalesce(soldqty.on_appro,0) 'On Appro'
    FROM
        (SELECT 
        sd.sku,
            sto.colour,
			sd.description,
            sto.costprice,
            sto.retailprice,
            sto.sizeid,
            sto.size,
            sea.nicename season,
            sea.id sid,
            bra.id bid,
            bra.nicename brand,
            adj.qty adj_qty,
            sto.physical stock
    FROM
        styleDetail sd, brands bra, seasons sea, flat_stock sto
    LEFT JOIN (SELECT 
        sa.sku, sa.colour, sa.sizeid, SUM(sa.qty*sr.polarity) qty
    FROM
        stkadjustments sa, stkadjreason sr
	where
		sa.reasonid = sr.id
    GROUP BY sku , colour,sizeid) adj ON adj.sku = sto.StockRef
        AND adj.colour = sto.colour
        and adj.sizeid = sto.sizeid
    WHERE
        sd.sku = sto.Stockref
            AND bra.id = sd.brand
            AND sea.id = sd.season
            ) stockqty
    LEFT JOIN (SELECT 
        od.StockRef,
            od.colour,
            od.sizeindex,
		    sum(CASE WHEN od.status = 'A' THEN od.qty else 0 END) on_appro,
			SUM(od.qty) qty,
            SUM(od.costprice * od.qty) costofsale,
            SUM(IF(od.zero_price = 1, 0, (IF(ABS(od.actualgrand) > 0, od.actualgrand, od.grandTot)))) salesvalue,
            SUM(IF(od.zero_price = 1, 0, (IF(ABS(od.actualnet) > 0, od.actualnet, od.netTot)))) salesnet,
            SUM(IF(od.zero_price = 1, 0, (IF(ABS(od.actualvat) > 0, od.actualvat, od.vatTot)))) salesvat
    FROM
        orderdetail od
    WHERE
        1 = 1
            AND status NOT IN ('X', 'N', 'P', 'W', 'S')
    GROUP BY od.StockRef , od.colour, od.sizeindex) soldqty ON (stockqty.sku = soldqty.StockRef
        AND stockqty.colour = soldqty.colour and stockqty.sizeid = soldqty.sizeindex)
        where 1=1
        and stockqty.size <>''
";	

		

#getSelect key for select code
$filters[0]=array('StockRef', 'brands', 'seasons', 'colours2');

#Predicate equivalent
$filters[1]=array('stockqty.sku','stockqty.bid','stockqty.sid','stockqty.colour');

#Group by name
//$filters[2]=array('stock_pos.StockRef','stock_pos.brand','stock_pos.season','stock_pos.colour','stock_pos.size');

#Pre-select names
//$filters[3]=array('stock_pos.StockRef SKU','stock_pos.brand Brand','stock_pos.season Season','stock_pos.colour Colour', 'stock_pos.size Size');	

#Row titles
$filters[4]=array('SKU','Brand','Season','Colour');

#Fixed Group by
//$groupby_fixed="stock_pos.size, stock_pos.colour, stock_pos.season, stock_pos.brand, stock_pos.StockRef,stock_pos.colour";

#Fixed Order by 
$orderby_fixed="stockqty.sku, stockqty.colour, stockqty.sizeid";


#What is the date field called?
$date="od.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);

$title="Stock Take Report";
$orient="portrait";
$nodate=1;
$rollup=0;
$debug=0;
$category="Stock";
?>
