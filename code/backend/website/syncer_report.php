<?php
include_once '../config.php';
include_once '../website/config.php';
include_once '../functions/auth_func.php';
include_once '../functions/web_func.php';
require_once "Mail.php";

ob_start();
echo "<title>Syncer Report</title></head>";
echo "<body><style>
.sale
{
	background-color:#0000ff;
}
.return
{
	background-color:#ff0000;
}
td
{
	color:#FFFFFF;
}";
echo "</style>";


$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

include_once '../website/config.php';

$sql_query="select concat(SKU,'-',size)  SKU
, case
 when qty_now < qty_then then 'Sale'
 when qty_now > qty_then then 'Refund'
end type
, qty_now 
, qty_then
, qty_onhand
from syncer
where timestamp between current_date() and now()
and type = 'S'
";

echo "<table width=60%>";
echo "<tr><th>SKU</th><th>Type</th><th>Qty Before</th><th>Qty Now</th><th>Total Onhand</th></tr>";

$results=$db_conn->query($sql_query);

while ($result=mysqli_fetch_array($results))
{
	if ($result['type']=='Sale')
	{
		echo "<tr class=sale>";
	}
	else
	{
		echo "<tr class=return>";
	}
	echo "<td>".$result['SKU']."</td><td>".$result['type']."</td><td>".$result['qty_then']."</td><td>".$result['qty_now']."</td><td>".$result['qty_onhand']."</td></tr>";
}
echo "</table>";

$message="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
$message.=<<<EOF
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
EOF;
$message.=ob_get_clean();

$host = "mail.cocorose.co.uk";
$username = "cocoshop";
$password = "S4usages!";
$to="steve@cocorose.co.uk, rebecca@cocorose.co.uk";
$subject="Cassidy Syncer";

$headers = array (
		'To' => $to,
		'Subject' => $subject,
		'MIME-Version' => '1.0',
		'Content-type' => 'text/html; charset=iso-8859-1',
		'return-receipt-to' => 'shop@cocorose.co.uk' ,
		'return-path' => 'shop@cocorose.co.uk',
		'From' =>  'Coco Rose'. " <shop@cocorose.co.uk>");

$smtp = Mail::factory('smtp',
		array ('host' => $host,
				'auth' => true,
				'username' => $username,
				'password' => $password));
$mail = $smtp->send($to, $headers, $message);
?>