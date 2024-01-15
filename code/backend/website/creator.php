<?php
//ini_set('include_path','/usr/share/php');
require_once "Mail.php";
include "./config.php";

ini_set("DISPLAY_ERRORS",'on');
$script_start = microtime(true);
#Setup SOAP Connection
//$proxy = new SoapClient('https://www.cocorose.co.uk/shopfront/api/soap/?wsdl');
$proxy = new SoapClient($soapURL);
$sessionId = $proxy->login($proxyUser, $proxyPass);


		#$index=array_search('Set', array_column($brands,'label'));

		#$catgory_type=$proxy->call($sessionId, 'product_attribute.options','497');
		
		//$colours=$proxy->call($sessionId, 'product_attribute.info',array('51'));
        try 
        {
            $colours=$proxy->call($sessionId, 'catalog_product.info','JOURNALJUMPER-GREY');
        }
        catch (Exception $e)
        {
            echo "No";
        }
		#$index=array_search(strtolower('GREY'), array_column(array_map('strtolower',$colours),'label'));
		#$index=array_search(strtolower('Grey'), array_column(array_map('strtolower',$colours['label']),'label'));
		#print_r(array_map('strtolower',$colours));
		#echo $index;
		#$seasons=$proxy->call($sessionId, 'product_attribute.options','556');
		#print_r($seasons);
		
		#$category_setup=file_get_contents('https://www.cocorose.co.uk/shopfront/steve.php');
		
?>
