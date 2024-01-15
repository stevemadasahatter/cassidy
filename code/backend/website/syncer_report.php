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
	color:#0000ff;
}
.return
{
	color:#ff0000;
}
td
{
    border:1px solid #000000;
}
";
echo "</style>";


$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

include_once '../website/config.php';

$sql_query="select webdetails.photo, concat(syncer.SKU,'-',syncer.size)  SKU
, case
 when qty_now < qty_then then 'Sale'
 when qty_now > qty_then then 'Refund'
end type
, qty_now 
, qty_then
, qty_onhand
from syncer left join webdetails
on syncer.SKU = concat(webdetails.sku,'-',webdetails.colour)
where timestamp between current_date() and now()
and type = 'S'
order by  concat(syncer.SKU,'-',syncer.size) asc
";

echo "<table width=60%>";
echo "<tr><th></th><th>SKU</th><th>Type</th><th>Qty Before</th><th>Qty Now</th><th>Total Onhand</th></tr>";

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
	$photos=preg_split("/\|/", $result['photo']);
	echo "<td><img width=90 src=\"$syncer_path/images/product/".$photos[0]."\" /></td><td>".$result['SKU']."</td><td>".$result['type']."</td><td>".$result['qty_then']."</td><td>".$result['qty_now']."</td><td>".$result['qty_onhand']."</td></tr>";
}
echo "</table>";

$message="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
$message.=<<<EOF
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
EOF;
$message.=ob_get_clean();

$host = "smtp.gmail.com";
$username = "rebecca@cocorose.co.uk";
$password = "R0semaryandthym3!";
$to="sdkellymail@gmail.com, rebecca@cocorose.co.uk";
$subject="Cassidy Syncer";

$headers = array (
		'To' => $to,
		'Subject' => $subject,
		'MIME-Version' => '1.0',
		'Content-type' => 'text/html; charset=iso-8859-1',
		'return-receipt-to' => $from ,
		'return-path' => $from,
		'From' =>  $from);

$smtp = Mail::factory('smtp',
 array ('host' => $host,
     'port'=> 587,
     'auth' => true,
     'socket_options' => array('ssl' => array('verify_peer_name' => false)),
     'debug' => true,
     'username' => $username,
     'password' => $password));
$mail = $smtp->send($to, $headers, $message);
//echo $message;
?>
