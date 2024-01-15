<?php

$domain=".mooo.com";

$db_username="till";
$db_password="secure";
$db_host="192.168.1.105";
$db_name="till";

$timeout=86400;

$barcode_printer="barcode";
$barcode_width=24;
$barcode_height=4.8;
$barcode_tmp="/var/www/backend/images/product/barcodes";
$report_save="/var/www/backend/report/saved";
$receipt_printer="PDF";

#Report printer (A4)
$main_printer="main";

$web_path="http://cocorose.kokua.dns-cloud.net/backend";
$syncer_path="http://cocorose.kokua.dns-cloud.net/backend";
$website_id ="base";

$barcode_css="
<style>
.receiptprice
{
        position:relative;
        font-size:16pt;
    font-weight:bold;
        left:210px;
        top:-15px;
        font-family:Arial;
    transform: rotate(-90deg);
}
    
p
{
        margin:0px;
        padding:0px;
}
    
.receipttext
{
        font-family:Arial;
        font-size:9pt;
    line-height:60%;
        white-space:nowrap;
}
.receiptdetail
{
        position:relative;
        font-size:14pt;
        font-weight:bold;
        left:210px;
        top:0px;
        font-family:Arial;
}
    
td
{
    font-size:10pt;
}
    
@page { margin-left:35px;
        margin-top:25px;
        }
    
</style>";

?>
