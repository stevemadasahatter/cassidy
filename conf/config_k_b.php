<?php

$domain=".mooo.com";

$db_username="till";
$db_password="secure";
$db_host="172.17.0.1";
$db_name="till_k";

$timeout=86400;

$barcode_printer="barcode";
$barcode_width=1.8;
$barcode_height=1.8;
$barcode_tmp="/var/www/backend/tmp";
$report_save="/var/www/backend/report/saved";
$receipt_printer="PDF";

#Report printer (A4)
$main_printer="Canon_MG6400_series";

$web_path="http://shop.kokua.dns-cloud.net/backend";
$syncer_path="http://shop.kokua.dns-cloud.net/backend";
$website_id = "kokua";

$barcode_css="
<style>
.receiptprice
{
        position:relative;
        font-size:16pt;
    font-weight:bold;
        left:210px;
        top:-25px;
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

@page { margin-left:5px;
        margin-top:15px;
        }

</style>
";

?>
