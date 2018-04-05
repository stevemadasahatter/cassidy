<?php
  
session_start();
ini_set('display_errors', 'Off');
include '../config.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$db_conn3=mysqli_connect($db_host, $db_username, $db_password, $db_name);

#Index
echo "Setting up indexes\n";
$index1="ALTER TABLE test.movementsline 
ADD INDEX `migrate` (`TransType` ASC, `TransSubType` ASC, `StockRef` ASC)";
$do_it=$db_conn->query($index1);

$index2="ALTER TABLE `test`.`movementsline` 
DROP INDEX `migrate` ,
ADD INDEX `migrate` (`TransType` ASC, `TransSubType` ASC, `StockRef` ASC, `Colour` ASC)";
$do_it=$db_conn->query($index2);

$index3="ALTER TABLE `test`.`tenderrecs` 
ADD INDEX `migrate` (`TransactionNo` ASC, `PayType` ASC)";
$do_it=$db_conn->query($index3);

$index4="ALTER TABLE `test`.`transrecs` 
ADD INDEX `migrate` (`TransactionNo` ASC)";
$do_it=$db_conn->query($index4);

#Brands
echo "Brands\n";
echo "--> Deleting rows\n";
$delete_query="delete from brands";
$do_it=$db_conn->query($delete_query);

$ai_query="alter table brands AUTO_INCREMENT=1";
$do_it=$db_conn->query($ai_query);
echo mysqli_error($db_conn);

echo "--> Inserting rows\n";
$insert_query="insert into brands (company,brand, nicename) select 1,max(Code), Name from test.UserDefined1 group by Name";
$do_it=$db_conn->query($insert_query);
echo mysqli_error($db_conn);
#Categories

echo "Category\n";
echo "--> Deleting rows\n";
$delete_query="delete from category";
$do_it=$db_conn->query($delete_query);

$ai_query="alter table category AUTO_INCREMENT=1";
$do_it=$db_conn->query($ai_query);

echo "--> Inserting rows\n";
$insert_query="insert into category (category, nicename) select Code, Name from test.Category";
$do_it=$db_conn->query($insert_query);
echo mysqli_error($db_conn);

#Colours
echo "Colours\n";
echo "--> Deleting rows\n";
$delete_query="delete from colours";
$do_it=$db_conn->query($delete_query);

$ai_query="alter table colours AUTO_INCREMENT=1";
$do_it=$db_conn->query($ai_query);

echo "--> Inserting rows\n";
$insert_query="insert into colours (company, colour, nicename, barcode) select 1,Code, Name, Number from test.Colour";
$do_it=$db_conn->query($insert_query);
echo mysqli_error($db_conn);

#Seasons
echo "Seasons\n";
echo "--> Deleting rows\n";
$delete_query="delete from seasons";
$do_it=$db_conn->query($delete_query);

$ai_query="alter table seasons AUTO_INCREMENT=1";
$do_it=$db_conn->query($ai_query);

echo "--> Inserting rows\n";
$insert_query="insert into seasons (season, nicename) select Code, Name from test.UserDefined2";
$do_it=$db_conn->query($insert_query);
echo mysqli_error($db_conn);
 
#Sizes
echo "Sizes\n";
echo "--> Deleting rows\n";
$delete_query="delete from sizes";
$do_it=$db_conn->query($delete_query);

echo "--> Inserting rows\n";
$insert_query="insert into sizes
select
sizekey
, sizekeydescription
, rtrim(size1)
, rtrim(size2)
, rtrim(size3)
, rtrim(size4)
, rtrim(size5)
, rtrim(size6)
, rtrim(size7)
, rtrim(size8)
, rtrim(size9)
, rtrim(size10)
, rtrim(size11)
, rtrim(size12)
, rtrim(size13)
, rtrim(size14)
, rtrim(size15)
, rtrim(size16)
, rtrim(size17)
, rtrim(size18)
, rtrim(size19)
, rtrim(size20)
from test.SizeTable";
$do_it=$db_conn->query($insert_query);
echo mysqli_error($db_conn);

#Style
echo "Style\n";
echo "--> Deleting rows\n";
$delete_query="delete from style";
$do_it=$db_conn->query($delete_query);

echo "--> Inserting rows\n";
$insert_query="insert into style
select
code,'1',description,
sizekey,
suplref,
'1',
0,
Number
from test.StyleDetails";
$do_it=$db_conn->query($insert_query);

#Styledetail
echo "StyleDetail\n";
echo "--> Deleting rows\n";
$delete_query="delete from styleDetail";
$do_it=$db_conn->query($delete_query);

$drop_index="drop index index1 on test.BaseStyle";
$do_it=$db_conn->query($drop_index);
echo mysqli_error($db_conn);

$create_index="alter table test.BaseStyle add index `index1` (`UserDefined1`, `UserDefined2`, `Category`, `Group`)";
$do_it=$db_conn->query($create_index);
echo mysqli_error($db_conn);


echo "--> Inserting rows\n";
$insert_query="insert into styleDetail select BaseStyle.Code, BaseStyle.Name, ProductGroup.id, category.id, seasons.id, brands.id, 1,0
from test.BaseStyle, seasons, brands, ProductGroup, category
where BaseStyle.UserDefined1 = brands.brand
and BaseStyle.UserDefined2 = seasons.season
and BaseStyle.Category = category.category
and BaseStyle.Group = ProductGroup.productgroup";
$do_it=$db_conn->query($insert_query);
echo mysqli_error($db_conn);

echo "--> Inserting Alterations Row\n";
$insert_query="insert into styleDetail (sku, description, season, brand, company, nonstock, category) values ('ALTERATION C','Alterations',18,73,1,1,49)";
$do_it=$db_conn->query($insert_query);


echo "--> Inserting Unknown Row\n";
$insert_query="update styleDetail set nonstock = 1 where sku = 'UNKNOWN'";
$do_it=$db_conn->query($insert_query);


echo "--> Inserting Postage Row\n";
$insert_query="insert into styleDetail (sku, description, season, brand, company, nonstock, category) values ('POSTAGE','Postage',18,73,1,1,49)";
$do_it=$db_conn->query($insert_query);


#Tenders
echo "Tenders\n";
echo "--> Deleting rows\n";
$delete_query="delete from tenders";
$do_it=$db_conn->query($delete_query);

echo "--> Inserting rows\n";
$insert_query="insert into tenders (company, transno, transDate, PayMethod, PayType, PayValue, spendpot) 
select 1, tr.TransactionNo
,date_format(concat(substr(tr.TransDate, 1,10), ' ',substr(lpad(tr.TransTime,4,'0'),1,2),':',substr(lpad(tr.TransTime,4,'0'),1,2)),'%Y-%m-%d %H:%i')
, case 
        when tr.PayMethod = 1 then 1
        when tr.PayMethod = 2 then 3
        when tr.PayMethod = 3 then 4
        when tr.PayMethod = 4 then 5
        when tr.PayMethod = 5 then 10
        when tr.PayMethod = 6 then 9
        when tr.PayMethod = 7 then 8
        when tr.PayMethod = 8 then 2
        when tr.PayMethod = 9 then 7
        when tr.PayMethod = 10 then 6
end as tr.PayMethod
, case 
        when tr.PayType = 1 then 1
        when tr.PayType = 2 then 3
        when tr.PayType = 3 then 4
        when tr.PayType = 4 then 5
        when tr.PayType = 5 then 10
        when tr.PayType = 6 then 9
        when tr.PayType = 7 then 8
        when tr.PayType = 8 then 2
        when tr.PayType = 9 then 7
        when tr.PayType = 10 then 6
end as tr.PayType
, (case tr.PayMethod when '1' then (tr.PayValue - (trr.TotalAmount - trr.TotalSalesValue))
else tr.PayValue
end)
, tr.CNDNGVNumber
from test.TenderRecs tr, test.transrecs trr where tr.PayType <> 5 and tr.TransactionNo = trr.TransactionNo";
$do_it=$db_conn->query($insert_query);
echo mysqli_error($db_conn);

#Customers
echo "Customers\n";
echo "--> Deleting rows\n";
$delete_query="delete from customers";
$do_it=$db_conn->query($delete_query);

echo "--> Inserting rows\n";
$insert_query="insert into customers
select CustTitle, CustForenames
, CustSurName
, CustAddress1
, CustAddress2
, CustAddress3
, CustAddress4
, CustAddress5
, CustPostCode
, CustTelephone2
, CustTelephone1
, CustDate
, CustDOB
, CustInternetEmailAddress
, CustReference
, CustAccount
, '1'
, 0
,1
,1
from test.ClientMainDetails";
$do_it=$db_conn->query($insert_query);
echo mysqli_error($db_conn);

echo "--> Insert walkin record\n";
$insert_query="insert into customers (title, forename, lastname, custid) values ('Mr','New','Customer',4)";
$do_it=$db_conn->query($insert_query);
echo mysqli_error($db_conn);


#Orderdetail
echo "OrderDetail\n";
echo "--> Deleting rows\n";
$delete_query="delete from orderdetail";
$do_it=$db_conn->query($delete_query);

echo "--> Inserting rows\n";
$insert_query="insert into orderdetail select TransactionNo, rtrim(StockRef), (SellingPrice - VatAmount),VatAmount, SellingPrice
, (case TransType 
		when 'S' then 'C'
		when 'R' then 'C'
		when 'AS' then 'X'
		when 'AR' then 'X'
end)
, @rownum:=@rownum+1
, (case TransType when 'S' then Quantity
	when 'R' then Quantity*-1
	when 'AS' then Quantity
	when 'AR' then Quantity*-1
   end)
, Colour, rtrim(size), NULL
, date_format(concat(substr(TransDate, 1,10), '	',substr(lpad(TransTime,4,'0'),1,2),':',substr(lpad(TransTime,4,'0'),1,2)),'%Y-%m-%d %H:%i')
, 1,(DiscSellingPrice-VatAmount), VatAmount, (DiscSellingPrice), CostPriceR, (if (DiscSellingPrice=0,1,0))
from test.ItemsRecs,(SELECT @rownum:=0) r";
$do_it=$db_conn->query($insert_query);

echo "--> Updating rows\n";

$sql_query="select style.sizekey, orderdetail.size, orderdetail.StockRef, orderdetail.transno from style, orderdetail where style.sku=orderdetail.Stockref";
$results=$db_conn->query($sql_query);

while ($result=mysqli_fetch_array($results))
{
	$sql_query2="select size1, size2, size3, size4, size5, size6, size7, size8, size9, size10, size11, size12, size13, size14, size15 from sizes where sizekey = ".$result['sizekey'];
	$results2=$db_conn2->query($sql_query2);
	$result2=mysqli_fetch_row($results2);
	echo mysqli_error($db_conn);

	$index=array_search($result['size'],$result2);
	$index=$index+1;
	
	if ($index=="")
	{
		echo $index+1;
		print_r($result2);
		echo "here".$result['size']."here";
}

	$update_query="update orderdetail set sizeindex = $index where StockRef=\"".$result['StockRef']."\" and transno = '".$result['transno']."'";
	$do_it=$db_conn2->query($update_query);
	echo mysqli_error($db_conn);
}


#Orderheader
echo "Orderheader\n";
echo "--> Deleting rows\n";
$delete_query="delete from orderheader";
$do_it=$db_conn->query($delete_query);

echo "--> Remove duplicate Order Numbers";
$delete_query="delete from test.TransRecs where TransactionNo in
		(select TransactionNo from
		( select TransactionNo, count(*) from test.TransRecs group by TransactionNo having count(*) >1) dups)";
$do_it=$db_conn->query($delete_query);
echo mysqli_error($db_conn);


echo "--> Inserting rows\n";
$insert_query="insert into orderheader
select 1,NULL, null, transactionno
,date_format(concat(substr(TransDate, 1,10), ' ',substr(lpad(TransTime,4,'0'),1,
2),':',substr(lpad(TransTime,4,'0'),1,2)),'%Y-%m-%d %H:%i')
, case when CLMNumber = '' then 0
else CLMAccNumber
END CLMAccNumber
, NULL, NULL, NULL, totalamount, 'C',0,NULL
from test.TransRecs";


$do_it=$db_conn->query($insert_query);
echo mysqli_error($db_conn);

echo "-->Retrofitting Walkin customer\n";
$update_query="update orderheader set custref=4 where custref=0";
$do_it=$db_conn->query($update_query);
echo mysqli_error($db_conn);


#Stock!
echo "Stock\n";

echo "--> Delete Stock Adjustment reasons\n";
$delete_query="delete from stkadjreason\n";
$do_it=$db_conn->query($delete_query);

echo "--> Delete Stock Adjustments\n";
$delete_query="delete from stkadjustments\n";
$do_it=$db_conn->query($delete_query);



echo "--> Rebuild Stock Adjustment Reasons\n";
$sql_query="select Reference from test.movementsline where TransType in ('A-','A+','IS') and TransSubType <> 'ESL' group by Reference";

$i=0;
$stkadjments=array();
$results=$db_conn->query($sql_query);
while ($result=mysqli_fetch_array($results))
{
	$insert_query="insert into stkadjreason values ($i,'".$result['Reference']."')";
	$do_it=$db_conn2->query($insert_query);
	
	$stkadjments['Reference'][$i]=$result['Reference'];
	$i++;
}

echo "--> Deleting rows\n";
$delete_query="delete from stock";
$do_it=$db_conn->query($delete_query);
#Build starting point picture for stock

echo "--> Inserting rows\n";
$sql_query="select stkcrd.stockref,stkcrd.colour,physical1, physical2, physical3, physical4, physical5, physical6, physical7, physical8, physical9, physical10
			, physical11, physical12, physical13, physical14, physical15, mvmt.Reference, mvmt.TransDate from test.StockCard stkcrd
,(SELECT Stockref, colour, max(Reference) Reference, max(TransDate) TransDate FROM test.movementsline
where TransType = 'RC' and TransSubType = 'STR'
group by Stockref, colour) mvmt
where stkcrd.stockref = mvmt.stockref and stkcrd.colour = mvmt.colour";

$results=$db_conn->query($sql_query);

while ($result=mysqli_fetch_array($results))
{
	
	#Build adjustments per stock at datetrack
	$sql_query="select  orderdetail.status, sizeindex,  sum(qty) qty from orderdetail, orderheader where orderdetail.transno = orderheader.transno and
	orderdetail.Stockref = '".$result['stockref']."' and orderdetail.colour='".$result['colour']."' and orderdetail.status not in ('X','V')";

	$sql_query.=" group by status, sizeindex";
	#build stock picture per stock
	$results2=$db_conn2->query($sql_query);

	while ($result2=mysqli_fetch_array($results2))
	{
		# REVERSE out the sales and inflate the starting stock
		$result['physical'.$result2['sizeindex']]=$result['physical'.$result2['sizeindex']]+$result2['qty'];

	}
	
	#Find stockadjustments
	$sql_query="select sizepos, case 
			when TransType = 'A-' then Quantity
			when TransType = 'A+' then Quantity*-1
			when TransType = 'IS' then Quantity
			END Quantity
			, Reference, TransDate from test.movementsline where TransType in  ('A-','A+','IS') and TransSubType <> 'ESL' 
				and StockRef = '".$result['stockref']."' and colour = '".$result['colour']."'";
	$results3=$db_conn2->query($sql_query);
	
	while ($result3=mysqli_fetch_array($results3))
	{
		#Add the quantity back in
		$result['physical'.$result3['sizepos']]=$result['physical'.$result3['sizepos']]+$result3['Quantity'];
		
		#And create a stkadjustment - find Reference in Stkadjment array
		$id=array_search($result3['Reference'],$stkadjments['Reference']);

		$stk_sql="insert into stkadjustments values (1,'".$result['stockref']."','".$result['colour']."',".$result3['Quantity'].",$id,'".$result3['TransDate']."',
				".$result3['sizepos'].")";
		$do_it=$db_conn3->query($stk_sql);
		
	}
	
	$sql_query="insert into stock (Stockref, company, colour, forsale, web_status, physical1
			, physical2, physical3, physical4, physical5, physical6, physical7, physical8, physical9
			, physical10, physical11, physical12, physical13, physical14, physical15, vatable, delnote, deldate)   
			values ('".$result['stockref']."',1,'".$result['colour']."',1,0,'".$result['physical1']."'
			,'".$result['physical2']."','".$result['physical3']."','".$result['physical4']."'
			,'".$result['physical5']."','".$result['physical6']."','".$result['physical7']."','".$result['physical8']."'
			,'".$result['physical9']."','".$result['physical10']."','".$result['physical11']."','".$result['physical12']."'
			,'".$result['physical13']."','".$result['physical14']."','".$result['physical15']."',1,'".$result['Reference']."','".$result['TransDate']."')";
	$doit=$db_conn2->query($sql_query);
	echo mysqli_error($db_conn2);
	unset($result2);
	

}

$create_index="alter table test.StyleDetails add index `index1` (`Code`)";
$do_it=$db_conn->query($create_index);
echo mysqli_error($db_conn);

#Update prices in stock
echo "--> Updating Prices\n";
$update_query="update stock stk
 inner join test.StyleDetails sd
	on sd.Code = stk.Stockref
set stk.costprice=sd.CostPrice,
		stk.retailprice = sd.RetailPrice1";
$do_it=$db_conn->query($update_query);
echo mysqli_error($db_conn);

echo "--> Insert Alterations row";
$sql_query="delete from stock where StockRef = 'ALTERATION C'";
$doit=$db_conn2->query($sql_query);

$sql_query="insert into stock (StockRef, company, colour, forsale, vatable) values ('ALTERATION C',1,'MULTI',1,0)";
$doit=$db_conn2->query($sql_query);
echo mysqli_error($db_conn2);

echo "--> Insert UNKNOWN row";
$sql_query="insert into stock (StockRef, company, colour, forsale, vatable) values ('UNKNOWN',1,'NONE',1,1)";
$doit=$db_conn2->query($sql_query);
echo mysqli_error($db_conn2);

echo "--> Insert Postage  row";
$sql_query="insert into stock (StockRef, company, colour, forsale, physical1, vatable) values ('POSTAGE',1,'MULTI',1,1,0)";
$doit=$db_conn2->query($sql_query);
echo mysqli_error($db_conn2);


echo "--> Sort zero quantities";
$sql_query="update orderdetail set qty=1 where qty is NULL";
$doit=$db_conn2->query($sql_query);
echo mysqli_error($db_conn2);

#GiftVouchers
echo "Spend Pots\n";
echo "--> Deleting rows\n";
$delete_query="delete from spendpots";
$do_it=$db_conn2->query($delete_query);

echo "--> Inserting GV rows\n";
$sql_query="insert into spendpots
select GiftVoucherNo, TransDate, DateRedemed, TransactionNo, CLMNumber, 0, 'G',GiftVoucherAmount,0,TransDate + INTERVAL 1 YEAR
from test.cdggiftvoucher";

$do_it=$db_conn2->query($sql_query);
echo mysqli_error($db_conn2);

echo "--> Inserting CN rows\n";
$sql_query="insert into spendpots
select CreditNoteNo, TransDate, DateRedemed, TransactionNo, CLMNumber, 0, 'C',CreditAmount,0,TransDate + INTERVAL 1 YEAR
from test.creditnote";

$do_it=$db_conn2->query($sql_query);
echo mysqli_error($db_conn2);

echo "--> Inserting DN rows\n";
$sql_query="insert into spendpots
select DepositNoteNo, TransDate, DateRedemed, TransactionNo, CLMNumber, 0, 'D',DepositAmount,0,TransDate + INTERVAL 1 YEAR
from test.depositnote";

$do_it=$db_conn2->query($sql_query);
echo mysqli_error($db_conn2);

#Drop migration indexes
#Index
echo "Dropping indexes\n";
$index1="ALTER TABLE test.movementsline
DROP INDEX `migrate`";
$do_it=$db_conn->query($index1);

$index2="ALTER TABLE `test`.`movementsline`
DROP INDEX `migrate`";
$do_it=$db_conn->query($index2);

$index3="ALTER TABLE `test`.`tenderrecs`
DROP INDEX `migrate` ";
$do_it=$db_conn->query($index3);

$index4="ALTER TABLE `test`.`transrecs`
DROP INDEX";
$do_it=$db_conn->query($index4);

#Create reporting views just in case
#echo "Create reporting views\n";
#$create_query="
#create or replace view sales as select od.StockRef
#, od.timestamp
#, sto.retailprice, sto.colour
#, od.actualgrand, od.actualvat
#, sto.costprice, sd.season
#, sd.brand
#, (od.actualgrand - sto.costprice) as profit
#, ((od.actualgrand - sto.costprice)/od.actualgrand)*100 as margin
#from orderdetail od,#
#	stock sto,
#	styleDetail sd
#where sto.Stockref = sd.sku
#and od.StockRef = sto.Stockref
#and od.colour = sto.colour
#";
#$do_it=$db_conn->query($create_query);
#echo mysqli_error($db_conn);
?>
