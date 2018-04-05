<?php

#Config values for syncer

#$soapURL        =       "http://192.168.1.66/shopfront/index.php/api/soap/?wsdl";
#$soapURL        =       "http://thehub2.mooo.com/shopfront/index.php/api/soap/?wsdl=1";
$soapURL        =       "https://www.cocorose.co.uk/shopfront/index.php/api/soap/?wsdl";

#API/Web services username and password
$proxyUser      =       "steve";
$proxyPass      =       "sausages";

#database details
$dbUser         =       "syncer";
$dbPass         =       "butt0n5!";
$dbDB           =       "stocksync";

$pics_path="/var/www/backend/images/product";

# Mail detail
    $from = "Your lovely website<sync@cocorose.co.uk>";
    $to = "steve@cocorose.co.uk , rebecca@cocorose.co.uk";
    $subject = "Website Syncer report";

    $host = "mail.cocorose.co.uk";
    $port = "25";
    $username = "steve";
    $password = "butt0n5!";

?>
