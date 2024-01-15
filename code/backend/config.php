<?php

$domain="";

$db_username="till";
$db_password="secure";
$db_host="172.17.0.1";
$db_name="till";

$timeout=86400;

$barcode_height=4.8;
$barcode_width=24;
$barcode_printer="PDF";
$barcode_tmp="/var/www/backend/tmp";
$report_save="/var/www/backend/report/saved";
$receipt_printer="receipt2";

#Report printer (A4)
//$main_printer="Canon_MG5700_series";
$main_printer="PDF";

$web_path="http://192.168.1.118/backend";
$syncer_path="http://192.168.1.118/backend";
$barcode_css="
<style>
.receiptprice
{
        position:relative;
        font-size:16pt;
    font-weight:bold;
        left:225px;
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

</style>";

?>
