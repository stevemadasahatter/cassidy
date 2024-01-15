<?php
include_once '../config.php';


function getColour($colour)
{
	include '../website/config.php';
	#Set up SOAP connection
	$proxy = new SoapClient($soapURL);
	$sessionId = $proxy->login($proxyUser, $proxyPass);
	
	$colours=$proxy->call($sessionId, 'product_attribute.options','76');

	#strip off first letter and then lower case
	$colsearch=substr(strtolower($colour),2);
	$arr=search($colours, 'label', $colsearch);
	
	$html="<select name=colour>";
	
	foreach ($arr as $value)
	{
		$html.="<option value='".$value['value']."'>".$value['label']."</option>";
	}
	$html.="</select>";
	return $html;
	
}

function getBrand($brand)
{
        include '../website/config.php';
        #Set up SOAP connection
	
        $proxy = new SoapClient($soapURL);
        $sessionId = $proxy->login($proxyUser, $proxyPass);

	$brands=$proxy->call($sessionId, 'product_attribute.options','66');

        #strip off first letter and then lower case
        $brandsearch=substr(strtolower($brand),2);
        $arr=search($brands, 'label', $brandsearch);
        $html="<select name=brand>";

	if ($arr[0]<>"")
	{
        	foreach ($arr as $value)
        	{
              	  $html.="<option value='".$value['value']."'>".$value['label']."</option>";
        	}
	}
	else 
	{
		$html.="<option value=>Not in Magento - $brand</option>";
        	$html.="</select>";
	}
        return $html;

}


function getAttribArray($searchterm, $attrid, $typename)
{
	include '../website/config.php';
	#Set up SOAP connection
	$proxy = new SoapClient($soapURL);
	$sessionId = $proxy->login($proxyUser, $proxyPass);
	$attribute=$proxy->call($sessionId, 'product_attribute.options',$attrid);
	#Only send part of the search term to allow for fuzzy match
	$searchterm_clean=substr(strtolower($searchterm),0,10);
	$arr=search($attribute, 'label', $searchterm_clean);
	$html="<select id=$typename>";
	
	$array_zero=0;
	foreach ($attribute as $value)
	{
		if ($value['value']==$arr[0]['value'])
		{
			$html.="<option value='".$value['value']."' selected>".$value['label']."</option>";
		}
	    elseif ($arr[0]=="" && $array_zero==0)
	    {
	        $html.="<option value=\"\"  selected>Not in Magento - $searchterm</option>";
	        $array_zero=1;
	    }
		else
		{
			$html.="<option value='".$value['value']."'>".$value['label']."</option>";
		}
	}
	$html.="</select>";
	return $html;

}

function getAttribValue($searchterm, $attrid)
{
	include '../website/config.php';
	#Set up SOAP connection
	$options = array(
			'uri' => 'urn:Magento',
			'location' => 'https://www.cocorose.co.uk/shopfront/index.php/api/soap/index/?wsdl',
			'trace' => true,
			'connection_timeout' => 120,
			'wsdl_cache' => WSDL_CACHE_NONE,
	);
	#Set up SOAP connection
	$proxy2 = new SoapClient($soapURL);
	$sessionId = $proxy2->login($proxyUser, $proxyPass);	
	//$proxy2 = new SoapClient($soapURL, $options);
	//$sessionId2 = $proxy2->login($proxyUser, $proxyPass);
	
	$attribute=$proxy2->call($sessionId, 'product_attribute.options',$attrid);
	$arr=search($attribute, 'label', $searchterm);
	return $arr[0]['value'];
}


function search($array, $key, $value)
{
	$results = array();
	$val_len=strlen($value);
	$value2=strtolower($value);
	if (is_array($array)) {
		if (isset($array[$key]) && strtolower(substr($array[$key],0)) == $value2) {
			$results[] = $array;
		}

		foreach ($array as $subarray) {
			$results = array_merge($results, search($subarray, $key, $value2));
		}
	}
	//$results=1;
	return $results;
}


function doProductMigration($productType, $setId, $sku, $productData) 
{ 
	include_once '../website/config.php';
	#Set up SOAP connection
	$options = array(
			'uri' => 'urn:Magento',
			'location' => 'https://www.cocorose.co.uk/shopfront/index.php/api/soap/index/?wsdl',
			'trace' => true,
			'connection_timeout' => 120,
			'wsdl_cache' => WSDL_CACHE_NONE,
	);
	
	$proxy = new SoapClient($soapURL, $options);
	$sessionId = $proxy->login($proxyUser, $proxyPass);

	$result = array(); 
	try 
	{ 	
		$call=$proxy->call($sessionId, 'product.create', array($productType, $setId, $sku, $productData)); 
		$result = $proxy->call($sessionId, 'product.list', array('sku' => $sku)); 
	} 
		catch(Exception $e) 
		{ 
				$call=$proxy->logError($e); 
				$result = false; 
		} 
		return $result; 
}  

function create_web_item()
{
	include '../config.php';
	include '../website/config.php';
	include_once '../functions/auth_func.php';
	include_once '../functions/web_func.php';

	#Set up SOAP connection
	$options = array(
	'uri' => 'urn:Magento',
	'location' => 'https://www.cocorose.co.uk/shopfront/index.php/api/soap/index/?wsdl',
	'trace' => true,
	'connection_timeout' => 120,
	'wsdl_cache' => WSDL_CACHE_NONE,
	);
	
	#Set up SOAP connection
	$proxy = new SoapClient($soapURL);
	$sessionId = $proxy->login($proxyUser, $proxyPass);
		
	//$proxy = new SoapClient($soapURL, $options);
	//$sessionId = $proxy->login($proxyUser, $proxyPass);

	//ini_set('DISPLAY_ERRORS','on');
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$db_conn3=mysqli_connect($db_host, $db_username, $db_password, $db_name);

	$sql_query3="select format((0.005-(unix_timestamp(current_date())/10000000000000)),11)*-1 posn";
	$results3=$db_conn->query($sql_query3);
	$result3=mysqli_fetch_array($results3);
	
	# What do I need to do?
	$sql_query='select w.sku, w.colid colour, w.colour col, w.name, w.photo
		, w.description, w.brand, w.season
		, w.sizeGroup, w.categories, w.categorytype
	from webDetails w, stock s
	where 1 = 1
	and w.sku = s.Stockref
	and w.colour = s.colour
	and s.web_complete = 1
	and s.web_uploaded = 0';

	$results=$db_conn->query($sql_query);
	while ($result=mysqli_fetch_array($results))
	{
	    try
	    {
	        #If it exists we do nothing but update
	        $colours=$proxy->call($sessionId, 'catalog_product.info',$result['sku']."-".$result['col']);
	        echo $result['sku']."-".$result['col']." Already exists";
	        $sql_query="update stock set web_uploaded=1 where Stockref='".$result['sku']."' and colour = '".$result['col']."'";
	        $doit=$db_conn2->query($sql_query);
	    }
	    
	    catch (Exception $e)
	    {
	        #We need to create these products
	        # Get the addition data needed - sizes and price
	        $sql_query2 = "select s.physical1, s.physical2, s.physical3, s.physical4, s.physical5, s.physical6, s.physical7,
        	s.physical8, s.physical9, s.physical10, sz.size1, sz.size2,sz.size3,sz.size4,sz.size5,sz.size6,
        	sz.size7,sz.size8,sz.size9,sz.size10,sz.size11,sz.size12,sz.size13,sz.size14,sz.size15,sz.size16,sz.size17,sz.size18,sz.size19,sz.size20, st.onsale, s.retailprice, s.saleprice
        	from stock s, style st, sizes sz
        	where 1=1
        	and s.Stockref = st.sku
        	and st.sizekey = sz.sizekey
        	and s.colour = '".$result['col']."'
        	and s.Stockref = '".$result['sku']."'";
	        $results2=$db_conn2->query($sql_query2);
	        $stock=stockBalance($result['sku'], $result['col'],'');

	    }
    	
    	
		while ($result2=mysqli_fetch_array($results2))
		{
		$configurable_skus=array();
		#pick the price
		if ($result2['onsale']==0)
			{
			$price=$result2['retailprice'];
			}
			else
			{
			$price=$result2['saleprice'];
			}
			for ($i=1;$i<=20;$i++)
			{
			if ($stock['physical'.$i]<>0)
			{
			    if ($result['sizeGroup']<>'111')
			    {
				    $size=getAttribValue($result2['size'.$i], $result['sizeGroup']);
			    }
				if ($result['sizeGroup']=="503")
					{
						$sizeGroup="cloth_sizes";
					}
					elseif ($result['sizeGroup']=="498")
					{
							$sizeGroup="shoe_sizes";
					}
					elseif ($result['sizeGroup']=="519")
					{
						$sizeGroup="size";
					}
					elseif ($result['sizeGroup']=="558")
					{
						$sizeGroup="trouser_size";
					}
					elseif ($result['sizeGroup']=='111')
					{
					    $sizeGroup="simple";
					}
					elseif ($result['sizeGroup']=='580')
					{
					    $sizeGroup="trousers";
					}
					elseif ($result['sizeGroup']=='581')
					{
					    $sizeGroup="jqsize";
					}
				
				# Create variant simple product
				$productData = array(
				'name' => $result['name'],
				'description' => $result['description'],
				'short_description' => $result['description'],
							'website_ids' => array($website_id), // Id or code of website
									'status' => 1, // 1 = Enabled, 2 = Disabled
							         'visibility' => 1, // 1 = Not visible, 2 = Catalog, 3 = Search, 4 = Catalog/Search
							        'tax_class_id' => 2, // Default VAT
									'weight' => 0,
				                     'posn' => $result3['posn'],
									'season' => $result['season'],
									'price' => $result2['retailprice'], // Same price than configurable product, no price change
									'special_price' => $result2['saleprice'], // Same price than configurable product, no price change
									'manufacturer' => $result['brand'],
									'categorytype2' => $result['categorytype'],
									'color' => $result['colour'],
									$sizeGroup => $size,
									'stock_data' => array(
									'use_config_manage_stock' => 1,
									'manage_stock' => 1, // We do not manage stock, for example
									'qty' => ($stock['physical'.$i]-$stock['appro'.$i]),
									'is_in_stock' => 1,
											),
					);
				
				                    if ($sizeGroup=='simple')
				                    {
				                        #Override ProductData
				                        $productData['visibility']='4';
				                        $productData['sizeGroup']="";
				                        try {
				                            $result_call = $proxy->call($sessionId, 'product.create', array('simple', '4', $result['sku'].'-'.$result['col'], $productData));
				                        } catch (SoapFault $e) {
				                            echo '<p style="color:red;">Creation of '.$result['sku'].'-'.$result['col'].'failed.'.$e -> getMessage().'</p>';
				                        }
				                    }
									// Creation of product #1
									else 
									{
        									try {
        										$result_call = $proxy->call($sessionId, 'product.create', array('simple', '4', $result['sku'].'-'.$result['col'].'-'.$result2['size'.$i], $productData));
        									} catch (SoapFault $e) {
        										echo '<p style="color:red;">Creation of '.$result['sku'].'-'.$result['col'].'-'.$result2['size'.$i].'failed.'.$e -> getMessage().'</p>';
        									}
        									array_push($configurable_skus,$result['sku'].'-'.$result['col'].'-'.$result2['size'.$i]);
									}
									
			}
																	
			}
			
			#Create configurable

			$productData = array(
			'name' => $result['name'],
			'description' => $result['description'],
			'short_description' => $result['description'],
			'website_ids' => array($website_id), // Id or code of website
			'status' => 1, // 1 = Enabled, 2 = Disabled
			'visibility' => 4, // 1 = Not visible, 2 = Catalog, 3 = Search, 4 = Catalog/Search
			'tax_class_id' => 2, // Default VAT
			'weight' => 0,
			'posn' => $result3['posn'],
			'season' => $result['season'],
			'manufacturer' => $result['brand'],
			'categorytype2' => $result['categorytype'],
			'color' => $result['colour'],
			'stock_data' => array(
			'use_config_manage_stock' => 1,
			'manage_stock' => 1, // We do not manage stock, for example
			'is_in_stock' => 1,
			),
			'price' => $result2['retailprice'], // Same price than configurable product, no price change
			'special_price' => $result2['saleprice'], // Same price than configurable product, no price change
			'associated_skus' => array($configurable_skus), // Simple products to associate
			'configurable_attributes' => array($result['sizeGroup']),
			//'configurable_attributes' => array('Dress Size'),
			//'configurable_attributes' => 'cloth_sizes',
			);
			// Creation of configurable product
			if ($sizeGroup<>'simple')
			{
        			try {
        				$result_call = $proxy->call($sessionId, 'product.create', array('configurable', '4', $result['sku'].'-'.$result['col'], $productData));
        			} catch (SoapFault $e) {
        				echo '<p style="color:red;">Creation of configurable '.$result['sku'].'-'.$result['col'].'failed.'.$e -> getMessage().'</p>';
        			}
		     }
			// Assign categories
			foreach (explode(",",$result['categories']) as $category)
			{
				if ($category <> '')
				{
					$proxy->call($sessionId, 'catalog_category.assignProduct', array('categoryId'=> $category, 'product'=> $result['sku'].'-'.$result['col']));
				}
			}
				
			$photos=explode("|",$result['photo']);
			$firsttimethru=0;
			foreach ($photos as $photo)
			{
				$filetype=explode(".",$photo);
				if (exif_imagetype($pics_path.'/'.$photo)) 
				{
				    
    				if ($filetype[1]=="png")
    				{
    					$mime="image/png";
    				}
    				else
    				{
    					$mime="image/jpeg";
    				}
    				if ($firsttimethru==0)
    				{
    				    $image = array(
    						'file' => array(
    								'name' => $photo,
    								'content' => base64_encode(file_get_contents($pics_path.'/'.$photo)),
    								'mime'    => $mime
    						),
    						'label'    => $result['description'],
    						'position' => 2,
    						'types'    => array('small_image','image', 'thumbnail'),
    						'exclude'  => 0
    				    );
    				}
    				else 
    				{
    				    $image = array(
    				        'file' => array(
    				            'name' => $photo,
    				            'content' => base64_encode(file_get_contents($pics_path.'/'.$photo)),
    				            'mime'    => $mime
    				        ),
    				        'label'    => $result['description'],
    				        'position' => 2,
    				        'types'    => array(),
    				        'exclude'  => 0
    				    );
    				}
    				$firsttimethru=1;
				}
				else
				{
				    $photo="";
				}
				if ($photo<>"")
				{
				    try {
				        $imageFilename = $proxy->call($sessionId, 'product_media.create', array($result['sku'].'-'.$result['col'], $image));
				    } catch (SoapFault $e) {
				        echo '<p style="color:red;">Image insert into configurable '.$result['sku'].'-'.$result['col'].'failed.'.$e -> getMessage().'</p>';
				    }
					
				}
			}
				
			#Verify we have created OK
			$filters = array(
			'sku' => array('like'=>$result['sku'].'-'.$result['col'].'%')
			);

			$products = $proxy->call($sessionId, 'product.list', array($filters));
				
			if ($products)
			{
					echo "Created configurable for ".$result['sku'].'-'.$result['col']."\n";
					$sql_query="update stock set web_uploaded=1 where Stockref='".$result['sku']."' and colour = '".$result['col']."'";
					$doit=$db_conn->query($sql_query);
			}
			else
			{
				echo "Failed to create ".$result['sku'].'-'.$result['col']."\n";
				$sql_query="update stock set web_uploaded=1 where Stockref='".$result['sku']."' and colour = '".$result['col']."'";
				$doit=$db_conn->query($sql_query);
			}
				
		}
	}
}

function change_web_item($last_run)
{
	include '../config.php';
	include_once '../website/config.php';
	include_once '../functions/auth_func.php';
	include_once '../functions/web_func.php';

	#Note down start time for update later
	$time=date('Y-m-d H:i:s');
	
	include '../website/config.php';
	#Set up SOAP connection
	$options = array(
	'trace' => true,
	'connection_timeout' => 120,
	'wsdl_cache' => WSDL_CACHE_NONE,
	);

	$proxy = new SoapClient($soapURL, $options);
	$sessionId = $proxy->login($proxyUser, $proxyPass);

	ini_set('DISPLAY_ERRORS','Off');
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);

	# What do I need to do? *** How do we check that nothing is created while this SQL is open? (long running job)
$sql_query="select od.StockRef, od.colour, od.size
	from orderdetail od, stock, orderheader oh
	where od.StockRef = stock.Stockref
	and od.colour = stock.colour
    and od.transno = oh.transno
	and oh.transDate > current_date()
	and stock.web_uploaded = 1
union all
select sa.sku, sa.colour, sa.sizeid
	from stkadjustments sa,  stock sto
	where sto.Stockref = sa.sku
	and sto.colour = sa.colour
	and sto.web_uploaded =1
	and sa.datetime > current_date()";

	$results=$db_conn->query($sql_query);
	
	while ($result=mysqli_fetch_array($results))
	{
		# If the order came from the web, then it will be put on Appro manually but the website will have the correct stock
		# If the order came from the shop then it *might* be on Appro, but the website will have a higher stock 
		# **** How do we sync stock adjustments? ****
			# Do weekly/checker report for anomalies between stock on website and shop
		# Therefore the Cassidy database has the master stock record
		# We should update the whole stock record for the SKU on the website to ensure it is in line with expectation
		# We should report on the changes we have made, since that represents the old Syncer report
		
		#Select out the stockbalance for the sku
		$stockbalance=stockBalance($result['StockRef'], $result['colour'],"");
		
		$stockonhand=0;
		for ($i=1;$i<=15;$i++)
		{
			if (abs($stockbalance['physical'.$i])>0)
			{
				$stockonhand+=$stockbalance['physical'.$i]-$stockbalance['appro'.$i];
			}
		}
		syslog(LOG_INFO,"--> Performed Sync of ".$result['StockRef']."-".$result['colour'])."\n";

		for ($i=1;$i<=15;$i++)
		{
			$variantsize=getItemSize($i, $result['StockRef']);
			echo "--> Working on ".$result['StockRef']."-".$result['colour']."-".$variantsize."\n";
			unset($variantsku);
			$variantsku=array();
			#Check if this is a simple product
			if ($variantsize=="ANY")
			{
				$variantsku[]=$result['StockRef'].'-'.trim($result['colour']);
			}
			else
			{
				$variantsku[]=$result['StockRef'].'-'.trim($result['colour']).'-'.$variantsize;
			}
			
			#Get Website current
			try
			{
			    $webrecord=$proxy->call($sessionId, 'product_stock.list', array($variantsku));
			}
			catch (Exception $e)
			{
			    echo $e."Failed to get stock list. \n";
			}

			#Perform update if they differ, save time if they don't. Report difference
			if ((int)$webrecord[0]['qty']==($stockbalance['physical'.$i]-$stockbalance['appro'.$i]))
			{
				$type="W";
			}
			else 
			{
				try 
				{
				    $proxy->call($sessionId, 'product_stock.update', array($variantsku[0], array('qty'=>($stockbalance['physical'.$i]-$stockbalance['appro'.$i]))));
				}
				catch (Exception $e)
				{
					echo $e."Failed to update stock item \n";
				}
				echo "--> Set ".$variantsku[0]." from ".$webrecord[0]['qty']." to ".($stockbalance['physical'.$i]-$stockbalance['appro'.$i])."\n";
				$type="S";
				$sql_query2="insert into syncer (SKU, size, qty_now, qty_then, qty_onhand, type) values ('".$result['StockRef']."-".$result['colour']."','".$variantsize."','".($stockbalance['physical'.$i]-$stockbalance['appro'.$i])."',".$webrecord[0]['qty'].",$stockonhand,'".$type."')";
				$doit=$db_conn2->query($sql_query2);
			}
			
			if (getItemSize($i+1, $result['StockRef'])=="")
			{
				$i=20;
			}
		}		
	}
	
	#Finish by updating the last run time, so it doesn't do all the work again. NOte that updates are absolute, so the time is the start time of the run so any 
	# Transactions made in the shop during the run will be picked up next time. Some of the updates may be repeated (doesn't matter because it is absolute)
	# But none will be missed	
	$sql_query2="update config set value='".$time."' where config='batch_stock_run'";
	$doit=$db_conn2->query($sql_query2);
	unset($proxy);
}


function change_web_special_price()
{
	include '../config.php';
	include_once '../website/config.php';
	include_once '../functions/auth_func.php';
	include_once '../functions/web_func.php';

	#Note down start time for update later
	$time=date('Y-m-d H:i:s');

	include '../website/config.php';
	#Set up SOAP connection
	$options = array(
	'trace' => true,
	'connection_timeout' => 120,
	'wsdl_cache' => WSDL_CACHE_NONE,
	);

	$proxy = new SoapClient($soapURL, $options);
	$sessionId = $proxy->login($proxyUser, $proxyPass);

	ini_set('DISPLAY_ERRORS','Off');
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);

	# For every SKU with a sale price which is onsale we will update the website
	$sql_query="select sto.StockRef, sto.colour, max(sto.saleprice) saleprice, max(sto.retailprice)
	from stock sto, style
	where 1=1
    and sto.Stockref = style.sku
	and sto.saleprice is not null
	and sto.web_uploaded = 1 
	group by sto.Stockref, sto.colour";

	$results=$db_conn->query($sql_query);  

        while ($result=mysqli_fetch_array($results))
        {
                # Just set the special_price for the item and job done

                echo "--> Set Special Price on ".$result['StockRef']."-".$result['colour']." to ".$result['saleprice']."\n";

                try
                {
                        $call_out=$proxy->call($sessionId, 'catalog_product.setSpecialPrice', array('product'=>$result['StockRef'].'-'.$result['colour'], 'specialPrice'=>$result['saleprice']));
                }
                catch (Exception $e)
                {
                        echo $e."\n";
                }
        }

        unset($proxy);
        $sql_query="update config set value = 0 where config='batch_prices'";
        $do_it=$db_conn->query($sql_query);
}

?>
