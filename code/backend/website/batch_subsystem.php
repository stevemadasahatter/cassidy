<?php
include_once '../config.php';
include_once '../website/config.php';
include_once '../functions/auth_func.php';
include_once '../functions/web_func.php';

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

include_once '../website/config.php';

$time=date('Y-m-d H:i:s');

#When was I last run?
$sql_query="select value from config where config = 'batch_run'";
$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);

$last_batch_run=$result['value'];

#Determine what I need to do
$sql_query="select config, value from config where config in ('batch_prices','batch_stock','batch_upload')";
$results=$db_conn->query($sql_query);
while ($result=mysqli_fetch_array($results))
{
	$switches[$result['config']]=$result['value'];
}



# Any web items to create?
if ($switches['batch_upload']==1)
{
	echo date('Y-m-d H:i:s')." - INFO - Started Batch Upload of Stock)\n";
	$sql_query='select count(*) cnt
	from webDetails w, stock s
	where 1 = 1
	and w.sku = s.Stockref
	and w.colour = s.colour
	and s.web_complete = 1
	and s.web_uploaded = 0';
	
	$results=$db_conn->query($sql_query);
	$creations=mysqli_fetch_array($results);
	
	if ($creations['cnt']>=1)
	{
		#Create items where required and then update webDetails record to show uploaded
		create_web_item();
	}
	$sql_query="update config set value ='".$time."' where config='batch_upload_run'";
	$doit=$db_conn->query($sql_query);
}
else {
	echo date('Y-m-d H:i:s')." - INFO - Didn't perform Batch upload of Stock this time)\n";
}

if ($switches['batch_stock']==1)
{
	echo date('Y-m-d H:i:s')." - INFO - Started Batch stock level sync\n";
	
	$sql_query="select value from config where config = 'batch_stock_run'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	#Any stock inventory updates?
	$sql_query="select count(*) cnt
	from orderdetail od, stock 
	where od.StockRef = stock.Stockref
	and od.colour = stock.colour
	and od.timestamp > '".$result['value']."'";
	
	$results=$db_conn->query($sql_query);
	$changes=mysqli_fetch_array($results);
	
	if ($changes['cnt']>=1)
	{
		 $output=change_web_item($result['value']);
		 echo $output;
	}
	$sql_query="update config set value ='".$time."' where config='batch_stock_run'";
	$doit=$db_conn->query($sql_query);
}
else
{
	echo date('Y-m-d H:i:s')." - INFO - Didn't perform Stock level sync)\n";
}
#Any stock price updates?
if ($switches['batch_prices']==1)
{
	#DO Price updates
	echo date('Y-m-d H:i:s')." - INFO - Started Stock price updates)\n";
	$output=change_web_special_price();
	echo $output;
}
else
{
	echo date('Y-m-d H:i:s')." - INFO - Didn't perform Price updates)\n";
}

#Update last batch runtime
$sql_query="update config set value ='".$time."' where config='batch_run'";
$doit=$db_conn->query($sql_query);

?>