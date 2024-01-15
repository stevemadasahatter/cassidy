<?php

#Config values for syncer

#$soapURL        =       "http://192.168.1.66/shopfront/index.php/api/soap/?wsdl";
#$soapURL        =       "http://thehub2.mooo.com/shopfront/index.php/api/soap/?wsdl=1";
#$soapURL        =       "https://www.cocorose.co.uk/shopfront/index.php/api/soap/?wsdl";
#$soapURL        =       "http://stevemadasahatter.moo.com:81/front/index.php/api/soap/?wsdl";

#API/Web services username and password
$proxyUser      =       "steve";
$proxyPass      =       "sausages";
$store		=	array('1','2');

#database details
$dbUser         =       "syncer";
$dbPass         =       "butt0n5!";
$dbDB           =       "stocksync";

$pics_path="/var/www/backend/images/product";
$web_pics_path="/backend/images/product";

# Mail detail
    $to = "steve@cocorose.co.uk , rebecca@cocorose.co.uk";
    $subject = "Website Syncer report";

    $host = "mail.cocorose.co.uk";
    $port = "25";
    $username = "steve";
    $password = "butt0n5!";
 
#M2 variables  
$magento2_host="http://stevemadasahatter.mooo.com:81/rest/all/V1";
//$magento2_host="https://www.cocorose.co.uk/rest/kokua/V1";
$magento2_user="steve";
$magento2_pass="Butt15n5!";




?>
