<?php


include '../config.php';
include '../functions/auth_func.php';
include '../functions/barcode_func.php';

require_once "Mail.php";

//Set up Sendmail to go
ini_set("SMTP","mail.cocorose.co.uk");
ini_set("display_errors",0);

$action=$_REQUEST['action'];
require_once '../functions/dompdf/dompdf_config.inc.php';
session_start();
$till=$_SERVER['REMOTE_ADDR'];
$tillsession=getTillSession($till);
$company=getTillCompany($till);
$orderno=$_REQUEST['orderno'];


$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

ob_start();
?>
<html><body style="left:0px;margin:0px;width:280px">
<p width=100% align=center><img width=240 src="../images/<?php echo $company;?>-logo.png" />
<style>
.receiptaddress
		{
		text-align:center;
		font-family:arial;
		font-size:10pt;
		padding:0px;
		margin:3px;
		}
.receiptheader
		{
		text-align:left;
		font-family:arial;
		font-size:11pt;
		font-weight:bold;
		}
		
@page { margin:0px; }		

.price 
{
	transform: rotate(90deg);
	transform-origin:left top 0;
}		
</style>

<?php
$sql_query="select nicename,addr1, addr2, addr3, postcode, telephone, VATno from companies where conum = $company";
$results=$db_conn->query($sql_query);

while ($result=mysqli_fetch_array($results))
{
	echo "<p class=receiptaddress>".$result['addr1']."</p>";
	echo "<p class=receiptaddress>".$result['addr2']."</p>";
	echo "<p class=receiptaddress>".$result['addr3']."</p>";
	echo "<p class=receiptaddress>".$result['postcode']."</p>";
	echo "<p class=receiptaddress>".$result['telephone']."</p>";
	echo "<p class=receiptaddress>".$result['VATno']."</p>";
}

echo "<p class=receiptheader>Items purchased</p>";
$sql_query="select lineno, StockRef, colour, size, coalesce(actualgrand,grandtot) grandtot from orderdetail where transno=$orderno and status = 'C'";
$results=$db_conn->query($sql_query);

echo "<table width=90% align=center>";
echo "<tr><td>Product Code</td><td>Price</td></tr>";
while ($item=mysqli_fetch_array($results))
{
	echo "<tr><td>".$item['StockRef']."-".$item['colour']."-".$item['size']."</td>";
	echo "<td align=right>".$item['grandtot']."</td>";
	
}
echo "</table>";

$sql_query="select lineno, StockRef, colour, size, grandtot from orderdetail where transno=$orderno and status = 'A'";
$results=$db_conn->query($sql_query);
if (mysqli_affected_rows($db_conn)<>0)
{
	echo "<p class=receiptheader>Items on Approval</p>";
	echo "<table width=90% align=center>";
	echo "<tr><td>Product Code</td><td>Taken</td></tr>";
	while ($item=mysqli_fetch_array($results))
	{
	        echo "<tr><td>".$item['StockRef']."-".$item['colour']."-".$item['size']."</td>";
	        echo "<td>_____</td>";
	
	}
	echo "</table>";
}
echo "<p class=receiptheader>Payment Summary</p>";

$sql_query="select TenderTypes.PayDescr, tender.PayValue from TenderTypes, tenders where tenders.PayMethod=TenderTypes.PayId and transno=$orderno";
$results=$db_conn->query($sql_query);

echo "<table width=90% align=center>";
echo "<tr><td>Type</td><td>Amount</td></tr>";
while ($item=mysqli_fetch_array($results))
{
        echo "<tr><td>".$item['PayDescr']."</td>";
        echo "<td align=right>".$item['PatyValue']."</td>";

}
echo "</table>";

$totals=receiptTotals($orderno);
echo "<table width=100%><tr><td>Net Amount</td><td align=right>".$totals['net']."</td></tr>";
echo "<tr><td>VAT Amount</td><td align=right>".$totals['vat']."</td></tr>";
echo "<tr><td>Amount Paid</td><td align=right>".$totals['paid']."</td></tr>";
if ($totals['discount']>0)
{
	echo "<tr><td>Discount</td><td align=right>".$totals['discount']."%</td></tr>";
}
echo "</table>";

$sql_query="select message from receipt_messages where company = $company";
$messages=$db_conn->query($sql_query);
$message=mysqli_fetch_array($messages);
echo "<p width=100% align=center>".$message['message']."</p>";
echo "<p align=center width=100%><img src=\"http://thehub2.mooo.com/pos/order/barcode.php?orderno=$orderno&false=.png\" /></p>";
$html=ob_get_clean();

$dompdf= new DOMPDF();
$dompdf->set_paper(array(0,0,3*72,11*72),"portrait");
$dompdf->load_html($html);
$dompdf->render();

if ($action=="email")
{
	$attachment=$dompdf->output();
	$customer=getCustomer($orderno);
	//define the receiver of the email
	$to = $customer['email'];
	
	//define the subject of the email
	$subject = 'Your Shopping Receipt';
	//create a boundary string. It must be unique
	//so we use the MD5 algorithm to generate a random hash
	$random_hash = md5(date('r', time()));
	//define the headers we want passed. Note that they are separated with \r\n
//read the atachment file contents into a string,
//encode it with MIME base64,
//and split it into smaller chunks
$attachment_chunk = chunk_split(base64_encode($attachment));
	//define the body of the message.
	ob_start(); //Turn on output buffering
?>
--PHP-mixed-<?php echo $random_hash; ?> 
Content-Type: multipart/alternative; boundary="PHP-alt-<?php echo $random_hash; ?>"

--PHP-alt-<?php echo $random_hash; ?> 
Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

<h2>Your shopping receipt</h2>
<p>Please find your shopping receipt attached for your convenience. Thanks again for your custom.</p>
<p>Kind regards</p>
<p>Coco Rose</p>
--PHP-alt-<?php echo $random_hash; ?>--

--PHP-mixed-<?php echo $random_hash; ?> 
Content-Type: application/pdf; name="receipt.pdf" 
Content-Transfer-Encoding: base64 
Content-Disposition: attachment 

<?php echo $attachment_chunk; ?>
--PHP-mixed-<?php echo $random_hash; ?>--

<?php
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();

$host = "mail.cocorose.co.uk";
$username = "cocoshop";
$password = "s4usages!";

$headers = array (
   'To' => $to,
   'Subject' => $subject,
    'MIME-Version' => '1.0',
    'Content-type' => 'multipart/mixed; boundary="PHP-mixed-'.$random_hash.'"',
        'return-receipt-to' => 'shop@cocorose.co.uk' ,
        'return-path' => 'shop@cocorose.co.uk',
        'From' =>  'Coco Rose'. " <shop@cocorose.co.uk>");

                $smtp = Mail::factory('smtp',
                array ('host' => $host,
     'auth' => true,
     'username' => $username,
     'password' => $password));
    $mail = $smtp->send($to, $headers, $message);

echo "<p>Receipt mailed to Customer</p>";
echo "<button onclick=\"javascript:location.reload();\">Close</button>";

}

if ($action=='print')
{
	$pdf=$dompdf->output();

	file_put_contents($receipt_tmp."/printing.pdf",$pdf);

	#Execute O/S command to print
	exec('lp -d '.$receipt_printer.' '.$receipt_tmp.'/printing.pdf');

	echo "<h3>Receipt Printed</h3>";
	echo "<button onclick=\"javascript:location.reload();\">Close</button>";
}
?>
