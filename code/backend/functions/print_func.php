<?php

function printReport($html,$orient)
{
	$print=print_action($html, $main_printer,$orient);
}

function printReceipt($orderno, $direction)
{
include '../config.php';
include_once '../functions/auth_func.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

ob_start();
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
        echo "<p class=receiptheader style=\"text-decoration:underline;font-weight:bold;\">Items on Approval</p>";
        echo "<table width=90% align=center>";
        echo "<tr><td>Product Code</td><td>Taken</td></tr>";
        while ($item=mysqli_fetch_array($results))
        {
                echo "<tr><td>".$item['StockRef']."-".$item['colour']."-".$item['size']."</td>";
                echo "<td>_____</td>";

        }
        echo "</table>";
}
echo "<p class=receiptheader>Payments</p>";

$sql_query="select TenderTypes.PayDescr, tenders.PayValue from TenderTypes, tenders where tenders.PayMethod=TenderTypes.PayId and transno=$orderno";
$results=$db_conn->query($sql_query);

echo "<table width=90% align=center>";
echo "<tr><td>Type</td><td>Amount</td></tr>";
while ($item=mysqli_fetch_array($results))
{
        echo "<tr><td>".$item['PayDescr']."</td>";
        echo "<td align=right>".$item['PayValue']."</td>";

}
echo "</table>";

$totals=receiptTotals($orderno);

echo "<p class=receiptheader>Summary</p>";
echo "<table width=90%><tr><td>Net Amount</td><td align=right>".$totals['net']."</td></tr>";
echo "<tr><td>VAT Amount</td><td align=right>".$totals['vat']."</td></tr>";
echo "<tr><td>Amount Paid</td><td align=right>".$totals['paid']."</td></tr>";
if ($totals['discount']>0)
{
        echo "<tr><td>Discount</td><td align=right>".$totals['discount']."%</td></tr>";
}
echo "</table>";

$html=generic_header();
$html.=ob_get_clean();
$html.=generic_footer($orderno);
ob_end_clean();
if ($direction=="print")
{
	#Perform the print
	$print=print_action($html,$receipt_printer);

	if (substr($print, 1,2)=="lp")
	{
		return 1;
	}
	else 
	{ 
		return 0;
	}
}

elseif ($direction=="email")
{
require_once '../functions/dompdf/dompdf_config.inc.php';
require_once "Mail.php";

$dompdf= new DOMPDF();
$dompdf->set_paper(array(0,0,(3*72),(11*72)),"portrait");
$dompdf->load_html($html);
$dompdf->render();

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
}

}

function giftReceipt($orderno)
{
include '../config.php';
include_once '../functions/auth_func.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

ob_start();

echo "<h1 align=center width=100%>Gift Receipt</h1>";
echo "<p class=receiptheader>Items purchased</p>";
$sql_query="select lineno, StockRef, colour, size, coalesce(actualgrand,grandtot) grandtot from orderdetail where transno=$orderno and status = 'C'";
$results=$db_conn->query($sql_query);

echo "<table width=90% align=center>";
echo "<tr><td>Product Code</td></tr>";
while ($item=mysqli_fetch_array($results))
{
        echo "<tr><td>".$item['StockRef']."-".$item['colour']."-".$item['size']."</td></tr>";

}
echo "</table>";


$html=generic_header();
$html.=ob_get_clean();
$html.=generic_footer($orderno);

#Perform the print
$print=print_action($html,$receipt_printer);


}

function printSpendPot($spendPot)
{
include_once '../config.php';
include_once '../functions/auth_func.php';

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="select type, custref, createdDate from spendPots where id = $spendPot";
$results=$db_conn->query($sql_query);

$result=mysqli_fetch_array($results);
ob_start();

if ($result['type']=='G')
{
	echo "<h1 align=center width=100%>Gift Voucher</h1>";
	
}
elseif ($result['type']=='C')
{
	echo "<h1 align=center width=100%>Credit Note</h1>";
}
elseif ($result['type']=='D')
{
	echo "<h1 align=center width=100%>Deposit Note</h1>";
}



echo "<table width=90% align=center>";
echo "<tr><td>Product Code</td></tr>";
while ($item=mysqli_fetch_array($results))
{
	echo "<tr><td>".$item['StockRef']."-".$item['colour']."-".$item['size']."</td></tr>";

}
echo "</table>";


$html=generic_header();
$html.=ob_get_clean();
$html.=generic_footer($orderno);

#Perform the print
$print=print_action($html,$receipt_printer);


}

function printTollRoll($till, $tillsession)
{
include '../config.php';
include '../functions/auth_func.php';

}

function printPettyCash($id)
{
include '../config.php';
include_once '../functions/auth_func.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
session_start();
$sql_query="select pc.transamnt, pc.cashier, date_format(pc.timestamp,'%d/%m/%Y') dte, date_format(pc.timestamp,'%H:%i') tme , pct.Descr from pettycash pc, pettycashtype pct where pc.transtype=pct.typeid and pc.id = $id";
$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);

ob_start();
echo "<h2>Petty Cash Payment</h2>";
echo "<table><tr><td>Date</td><td>".$result['dte']."</td><td>Time</td><td>".$result['tme']."</td>";
echo "<tr><td>Cashier</td><td>".$result['cashier']."</td><td></td></tr>";
echo "<tr><td colspan=2>Reason</td><td colspan=2>Amount</td></tr>";
echo "<tr><td colspan=2>".$result['Descr']."</td><td colspan=2>".$result['transamnt']."</td></tr>";
echo "<br>";

echo "<p>Comments....................................</p>";
echo "<p>......................................................</p>";
echo "<p>......................................................</p>";
echo "<br>";
echo "<br>";
echo "<p>Signed for........................................</p>";


$html=generic_header();
$html.=ob_get_clean();

#Always goes to paper
#Perform the print
$print=print_action($html,$receipt_printer);

	if (substr($print, 1,2)=="lp")
	{
		return 1;
	}
	else
	{
		return 0;
	}

} //printPettyCash

function printBarcode($sku, $colour, $size, $sizeindex, $price)
{
    include '../config.php';
    include_once '../functions/auth_func.php';
    $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
    $db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);
    #Firstly build barcode
    #Barcode is barcode field (7),  followed by color (4) and then 'A' and sizeindex (3)
    $sql_query="select barcode from style where sku='".$sku."'";
    
    $results=$db_conn->query($sql_query);
    $barcode=mysqli_fetch_array($results);
    
    $sql_query="select sd.description, bra.nicename, sea.season from brands bra, styleDetail sd, seasons sea
where bra.id = sd.brand
and sea.id = sd.season
and sd.sku ='".$sku."'";
    $details=$db_conn->query($sql_query);
    $detail=mysqli_fetch_array($details);
    
    $sql_query="select barcode from colours where colour ='".$colour."'";
    $results2=$db_conn2->query($sql_query);
    $col=mysqli_fetch_array($results2);
    
    $barcodetext=$barcode['barcode'].$col['barcode']."A".str_pad($sizeindex,2,'0',STR_PAD_LEFT);
    
    # Put in the html with buffer lines either side
    ob_end_flush();
    ob_start();
    $html="";
    echo $barcode_css;
    
    echo "<div style=\"float:left;clear:both;position:relative;\">";
    echo "<p class=receipttext style=\"font-family:Arial;\">".$sku."-".$colour."-".$size."<br>";
    echo $detail['description']."</p>";
    echo "<br><img style=\"width:200px;\" src=\"".$web_path."/stock/barcode.php?orderno=".$barcodetext."&false=.png\"></img></p>";
    echo "<p class=receiptprice>&pound;".$price."</p>";
    echo "</div>";
    $html = ob_get_clean();
    
    
    $print=print_action($html,$barcode_printer, 'barcode');
    unset($html);
    
}


function generic_header()
{
include '../config.php';
include_once '../functions/auth_func.php';
session_start();
$till=$_SERVER['REMOTE_ADDR'];
$tillsession=getTillSession($till);
$company=getTillCompany($till);
$orderno=$_REQUEST['orderno'];


$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

ob_start();
echo "<html><body style=\"left:0px;margin:0px;width:280px\">";
echo "<p width=100% align=center><img width=240 src=\"../images/$company-logo.png\" />";
echo <<<EOF
<style>
body
{
        font-family:arial;
        font-size:10pt;
}
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

@page { margin:6px; }

</style>
EOF;
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

return ob_get_clean();

}


function generic_footer($orderno)
{
include '../config.php';
include_once '../functions/auth_func.php';

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

ob_start();
$sql_query="select message from receipt_messages where company = $company";
$messages=$db_conn->query($sql_query);
$message=mysqli_fetch_array($messages);
echo "<p width=100% align=center>".$message['message']."</p>";
echo "<p align=center width=100%><img src=\"$base_url/pos/order/barcode.php?orderno=$orderno&false=.png\" /></p>";

return ob_get_clean();
}

function print_action($html,$printer, $orient)
{
    include '../config.php';
    include_once '../functions/auth_func.php';
    //include '../functions/barcode_func.php';
    require_once '../functions/dompdf/dompdf_config.inc.php';
    
    $dompdf= new DOMPDF();
    
    if ($orient)
    {
        if ($orient=="landscape")
        {
            $dompdf->set_paper(array(0,0,(11.69*120),(8.27*120)),$orient);
        }
        elseif ($orient=="portrait")
        {
            $dompdf->set_paper(array(0,0,(11.69*120),(8.27*120)),$orient);
        }
        elseif ($orient=="barcode")
        {
            $dompdf->set_paper(array(0,0,($barcode_width*72),($barcode_height*72)),"landscape");
        }
    }
    else
    {
        $dompdf->set_paper(array(0,0,(8.27*100),(11.69*100)),"portrait");
    }
    //echo $html;
    $dompdf->load_html($html);
    $dompdf->render();
    $pdf=$dompdf->output();
    
    file_put_contents($barcode_tmp."/printing.pdf",$pdf);
    
    #Execute O/S command to print
    if ($orient=="landscape")
    {
        return exec('lp  -d '.$printer.' -olandscape '.$barcode_tmp.'/printing.pdf');
    }
    else
    {
        return exec('lp -d '.$printer.' '.$barcode_tmp.'/printing.pdf');
    }
    echo "<script>javascript:location.reload();</script>";
    
}

function download_action($html,$printer, $orient, $action='display')
{
	include '../config.php';
	include_once '../functions/auth_func.php';
	include_once '../functions/barcode_func.php';
	require_once '../functions/dompdf/dompdf_config.inc.php';
	$dompdf= new DOMPDF();
	if ($orient)
	{
		$dompdf->set_paper(array(0,0,(8.27*100),(11.69*100)),$orient);
	}
	else
	{
		$dompdf->set_paper(array(0,0,(3*72),(11*72)),"portrait");
	}
	$dompdf->load_html($html);
	$dompdf->render();
	//$pdf=$dompdf->output();

	if ($action=='display')
	{
	   header('Content-Type: application/pdf;');
	   echo $dompdf->output();
	}
	
	elseif ($action=="file")
	{
	   return $dompdf->output();
	   exit();
	}
	
}
?>
