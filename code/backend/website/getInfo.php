<?php
include '../config.php';
include '../website/config.php';
include_once '../functions/auth_func.php';
include_once '../functions/web_func.php';


include '../website/config.php';
#Set up SOAP connection
$options = array(
'uri' => 'urn:Magento',
'location' => 'http://www.cocorose.co.uk:82/index.php/api/soap/index',
'trace' => true,
'connection_timeout' => 120,
'wsdl_cache' => WSDL_CACHE_NONE,
);

$proxy = new SoapClient($soapURL, $options);
$sessionId = $proxy->login($proxyUser, $proxyPass);

ini_set('DISPLAY_ERRORS','on');
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);

# What do I need to do?
$result = $proxy->call($sessionId, 'catalog_product.list');

$productData = new stdClass();
$productData->additional_attributes = array('short_description','categorytype2');

#Get colours for array search
$colours=$proxy->call($sessionId, 'product_attribute.info',array('76'));

foreach ($result as $item)
{
	#Is there a webdetail entry?
	if ($item['type']=="configurable")
	{
		$item_sku=preg_split('/-/', $item['sku']);
		//print_r($item);
		//$results_product = $proxy->catalogProductInfo($sessionId,$item->product_id,null,$productData);
		$results_product = $proxy->call($sessionId,'catalog_product.info',$item['product_id'],null,$productData);
		//print_r($results_product);
		$pro_imag = $proxy->call($sessionId, 'catalog_product_attribute_media.list', $item['product_id']);
		//print_r($pro_imag);
		
		$sku_full=preg_split('/-/',$results_product['sku']);
		$sku=$sku_full[0];
		$colour=$sku_full[1];
		$name=$results_product['name'];
		
		$photos="";
		foreach ($pro_imag as $picture)
		{
			$filename_pos=strrpos($picture['file'], '/');
			$filename=substr($picture['file'], $filename_pos);
			file_put_contents($pics_path."/".$filename, file_get_contents($picture['url']));
			$photos.=$filename;
			$photos.="|";
		}
		
		
		#$photo=foreach $pro_imag['url'] $pro_imag['file']
		$description=$results_product['description'];
		$brand=$results_product['manufacturer'];
		$season=$results_product['season'];
		$sizeGroup=$results_product['set'];
		
		$categories="";
		foreach ($results_product['categories'] as $cat)
		{
			$categories.=$cat;
			$categories.=",";
		}
		
		#$categories=foreach $results_product['category_ids']; comma seperate
		$colid=$results_product['color'];
		$category_type=$results_product['categorytype2'];
		
		#firstly check that we have the stock item in stock
		$sql_query="select count(*) cnt from stock where StockRef ='$sku' and colour = '$colour'";
		$results=$db_conn->query($sql_query);
		$result=mysqli_fetch_array($results);
		
		echo "SKU is ".$sku;
		echo " colour is ".$colour."\n";
		if ($result['cnt']==1)
		{
			echo " and found a stock record to update \n";
			#Update the record as being a stock item online
			$sql_query="update stock set web_uploaded=1, web_complete=1 where StockRef ='$sku' and colour = '$colour'";
			$doit=$db_conn->query($sql_query);
			
			#Now create the webdetails
			$sql_query="insert into webDetails values ('$sku','$colour','$name','$photos','$description','$brand','$season','$sizeGroup','$categories','$colid','$category_type')";
			$doit=$db_conn->query($sql_query);
		}
	}
}

?>