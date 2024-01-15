<?php
include '/var/www/pos/functions/barcode_func.php';
$im=print_barcode($_REQUEST['orderno']);

header('Content-type: image/png');
echo $im;

?>	
