<?php

function printReceipt($orderno, $direction, $date=NULL)
{
    include '../config.php';
    include_once '../functions/auth_func.php';
    $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
    $db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);
    session_start();
    $company=getTillCompany($_COOKIE['tillIdent']);
    
    if ($direction<>"html")
    {
        $now_date=date('d-m-Y');
        $now_time=date('H:i');
        $rollID=$_SESSION['rollID'];
        $person=$_SESSION['POS'];
    }
    
    elseif ($direction == "html")
    {
        $sql_query="select date_format(transDate,'%d-%m-%Y') transDate from orderheader where transno =$orderno";
        $results=$db_conn->query($sql_query);
        $result=mysqli_fetch_array($results);
        $now_date=$result['transDate'];
        $sql_query="select date_format(transDate,'%H:%i:%s') transTime from orderheader where transno =$orderno";
        $results=$db_conn->query($sql_query);
        $result=mysqli_fetch_array($results);
        
        $now_time=$result['transTime'];
        
        $sql_query="select max(rollID) rollID from tillrolldetail where orderno =$orderno";
        $results=$db_conn->query($sql_query);
        $result=mysqli_fetch_array($results);
        
        $rollID=$result['rollID'];
        
        $sql_query="select cashierid from orderheader where transno =$orderno";
        $results=$db_conn->query($sql_query);
        $result=mysqli_fetch_array($results);
        
        $person=$result['cashierid'];
        
    }
    ob_start();
    if ($direction=="email")
    {
        echo <<<EOF
        <html>
        <head>
        <style>
        </style>
        </head>
        
        <body bgcolor="#eee">
        <p width=100%> 
        <table width=400 align="center" bgcolor="#fff">
        <tr><td align=center colspan=4><img align=center width=300 src="https://www.cocorose.co.uk/shopfront/skin/frontend/cocorose/default/images/logo.png" /></td></tr>
        
EOF;
    }
        
    
     else 
     {
         echo "<table width=100% align=center>";
     }
    
    echo "<tr><td>User:</td><td>".$person."</td><td align=right>Date:</td><td width=35% align=right>$now_date</td></tr>";
    echo "<tr><td>Receipt:</td><td>".$rollID."</td><td align=right>Time:</td><td width=35% align=right>$now_time</td></tr>";
    echo "<tr><td colspan=4>----------------------------------------</td></tr>";
    echo "</table>";
    
    $sql_query="select status, lineno, StockRef, colour, size, actualgrand, grandtot
            , abs(if(abs(actualgrand)>0||zero_price=1,actualgrand,grandTot)) paidtot, qty, zero_price from orderdetail
			where transno=$orderno and status in  ('C','J','K') ";
    
    if ($date<>NULL)
    {
        $sql_query.="and date_format(timestamp, '%Y-%m-%d') = '".$date."'";
    }
    $results=$db_conn->query($sql_query);
    
    if (mysqli_affected_rows($db_conn)<>0)
    {
        
        echo "<table bgcolor='#fff' width=100% align=center>";
        while ($item=mysqli_fetch_array($results))
        {
            echo "<tr><td>".$item['StockRef']."-".$item['colour']."-".$item['size']."</td>";
            if (abs($item['qty'])>1)
            {
                echo "<td align=right>x".$item['qty']."</td>";
            }
            else
            {
                echo "<td align=right></td>";
            }
            echo "<td align=right>&pound;".number_format($item['paidtot']*$item['qty'],2);
            if ($item['status']=="C")
            {
                echo " S";
            }
            elseif ($item['status']=="J" || $item['status']=="K")
            {
                echo "R";
            }
            elseif ($item['status']=="V")
            {
                echo "S";
            }
            
            echo "</td></tr>";
            $order_total+=$item['paidtot']*$item['qty'];
            if ($item['actualgrand']<>$item['grandtot'] && ($item['actualgrand']<>0 || $item['zero_price']==1))
            {
                echo "<tr><td>Discount applied &pound;".number_format(($item['grandtot']-$item['actualgrand'])*$item['qty'],2)."</td><td></td><td></td></tr>";
            }
        }
        
        echo "</table><br>";
        
        $sql_query="select grandTot from orderheader where transno=$orderno";
        if ($date<>NULL)
        {
            $sql_query.=" date_format(transDate, '%Y-%m-%d') = '".$date."'";
        }
        $results=$db_conn->query($sql_query);
        $ordertotal=mysqli_fetch_array($results);
        
        echo "<table bgcolor='#fff' width=100% align=center>";
        if (number_format(($ordertotal['grandTot']-$order_total),2)<>'0.00' && $ordertotal['grandTot']<>0)
        {
            echo "<tr><td>Customer Discount</td><td align=right>&pound;".number_format(($ordertotal['grandTot']-$order_total),2)."</td></tr>";
        }
        if ($order_total>0)
        {
            echo "<tr><td class=receiptheader>Total</td><td align=right>&pound;".number_format($order_total,2)."</td></tr>";
        }
        else
        {
            echo "<tr><td class=receiptheader>Refund Total</td><td align=right>&pound;".number_format($order_total,2)."</td></tr>";
        }
        echo "</table>";
    }
    
    $sql_query="select lineno, StockRef, colour, size, actualgrand, grandtot, abs(if(abs(actualgrand)>0||zero_price=1,actualgrand
            ,grandTot)) paidtot, qty from orderdetail
			where transno=$orderno and status = 'A'";
    $results=$db_conn->query($sql_query);
    
    if (mysqli_affected_rows($db_conn)<>0)
    {
        //echo "<p class=receiptheader style=\"font-weight:bold;\"></p>";
        echo "<table bgcolor='#fff' width=100% align=center>";
        while ($item=mysqli_fetch_array($results))
        {
            if (!$isOnAppro)
            {
                $isOnAppro=1;
            }
            echo "<tr><td>".$item['StockRef']."-".$item['colour']."-".$item['size']."</td>";
            if ($item['qty']>1)
            {
                echo "<td align=right>".$item['qty']."x</td>";
            }
            else
            {
                echo "<td align=right></td>";
            }
            echo "<td align=right>&pound;".number_format($item['paidtot']*$item['qty'],2);
            if ($item['qty']>0)
            {
                echo " AS";
            }
            else {
                echo " AR";
            }
            echo "</td></tr>";
            $order_total+=$item['paidtot']*$item['qty'];
            
            if ($item['actualgrand']<>$item['grandtot'] && ($item['actualgrand']<>0 || $item['zero_price']==1))
            {
                echo "<tr><td>Discount applied &pound;".number_format(($item['grandtot']-$item['actualgrand'])*$item['qty'],2)."</td><td></td><td></td></tr>";
            }
        }
        
        echo "</table>";
        
        
        
        echo "<table bgcolor='#fff' width=100% align=center>";
        
        if ($order_total>0)
        {
            echo "<tr><td class=receiptheader>Total</td><td align=right>&pound;".number_format($order_total,2)."</td></tr>";
        }
        else
        {
            echo "<tr><td class=receiptheader>Refund Total</td><td align=right>&pound;".number_format($order_total,2)."</td></tr>";
        }
        echo "</table>";
        echo "<p width=100% align=center><hr></p>";
    }
    
    if ($isOnAppro)
    {
        if ($direction<>"html")
        {
            echo "<table bgcolor='#fff' width=100% align=center>";
            echo "<tr><td colspan=2 style=\"text-align:center;\">On Approval</td></tr>";
            echo "<tr><td colspan=2 style=\"text-align:center;\">By signing below you are agreeing to purchase the items listed at the price detailed above<br>
				<br><br>Sign: ------------------------<br><br><br>Date:  ------------------</td></tr>";
            echo "<tr><td> </td><td> </td></tr>";
            echo "<tr><td> </td><td> </td></tr>";
            echo "<tr><td> </td><td> </td></tr>";
            echo "<tr><td> </td><td> </td></tr>";
            echo "<tr><td> </td><td> </td></tr>";
            echo "<tr><td> </td><td> </td></tr>";
            echo "<tr><td> </td><td> </td></tr>";
            echo "<tr><td> </td><td> </td></tr>";
            echo "<tr><td colspan=2>----------------------------------------</td></tr>";
            echo "</table>";
        }
        echo "<table bgcolor='#fff' width=100% align=center>";
        $sql_query="select c.forename, c.lastname, c.email, c.mobile from customers c, orderheader oh where c.custid = oh.custref and oh.transno=$orderno";
        $results=$db_conn->query($sql_query);
        $result=mysqli_fetch_array($results);
        echo "<tr><td>Name</td><td>".$result['forename']." ".$result['lastname']."</td></tr>";
        if ($direction<>"html")
        {
            echo "<tr><td>Email</td><td>".$result['email']."</td></tr>";
            echo "<tr><td>Mobile</td><td>".$result['mobile']."</td></tr>";
        }
        
        $dupe="true";
        $footer="false";
    }
    
    else
    {
        echo "<table width=100%>";
        echo "<tr><td colspan=2>----------------------------------------</td></tr>";
        
        $sql_query="select TenderTypes.PayDescr, tenders.PayValue, tenders.spendPot, tenders.changedue from TenderTypes, tenders
		where tenders.PayMethod=TenderTypes.PayId and transno=$orderno and company=$company";
        if ($date==NULL)
        {
            $sql_query.= " and tenders.transDate >= current_date()";
        }
        else
        {
            $sql_query.= " and date_format(tenders.transDate,'%Y-%m-%d') = '".$date."'";
        }
        $sql_query.=" order by TenderTypes.PayID desc";
        
        $results=$db_conn->query($sql_query);
        
        while ($item=mysqli_fetch_array($results))
        {
            if ($item['PayValue']<>0)
            {
                if ($item['PayDescr']=='Cash')
                {
                    echo "<tr><td>".$item['PayDescr']."</td>";
                    echo "<td align=right>&pound;".number_format($item['PayValue']+$item['changedue'],2)."</td></tr>";
                    if ($item['changedue']<>0)
                    {
                        echo "<tr><td>Change given</td>";
                        echo "<td align=right>&pound;".number_format($item['changedue'],2)."</td></tr>";
                    }
                    
                }
                else
                {
                    if ($item['spendPot']==0)
                    {
                        #If not a voucher then don't add the spendpot ID on
                        echo "<tr><td>".$item['PayDescr']."</td>";
                        echo "<td align=right>&pound;".number_format($item['PayValue'],2)."</td></tr>";
                    }
                    else
                    {
                        #Otherwise do
                        $spendpotvalue=getSpendPot($item['spendPot']);
                        if ($spendpotvalue['amount']==0)
                        {
                            $spendpotvalue['amount']=$item['PayValue'];
                        }
                        echo "<tr><td>".$item['PayDescr']." - ".$item['spendPot']."</td>
							<td align=right>&pound;".number_format($spendpotvalue['amount'],2)."</td></tr>";
                        
                        #Did we generate an overflow Spendpot?
                        $sql_query2="select id, amount from spendpots where orderno = $orderno and reason = 'Overflow'";
                        $results2=$db_conn2->query($sql_query2);
                        $result2=mysqli_fetch_array($results2);
                        if ($result2['id']<>"")
                        {
                            echo "<tr><td>".$item['PayDescr']." - ".ltrim($result2['id'],'0')." due</td>
								<td align=right>&pound;".number_format($result2['amount'],2)."</td></tr>";
                        }
                    }
                    
                }
            }
        }
        
        $dupe="false";
        $footer=true;
    }
    echo "</table>";
    
    
    
    
    if ($direction=="print")
    {
        $html=generic_header(1);
        $html.=ob_get_clean();
        $html.=generic_footer($orderno, $footer);
        ob_end_clean();
        #Perform the print
        $print=print_action($html,$receipt_printer,$dupe);
        
        
        if (substr($print, 1,2)=="lp")
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }
    
    elseif ($direction=="html")
    {
        $sql_query="select c.forename, c.lastname, c.email, c.mobile from customers c, orderheader oh where c.custid = oh.custref and oh.transno=$orderno";
        $results=$db_conn->query($sql_query);
        $result=mysqli_fetch_array($results);
        echo "<p align=center width=100%>Customer : ".$result['forename']." ".$result['lastname']."</p>";
        $html=ob_get_clean();
        ob_end_clean();
        return $html;
    }
    
    elseif ($direction=="email")
    {
        require_once '../functions/dompdf/dompdf_config.inc.php';
        require_once "Mail.php";
        
        $html=generic_header(0);
        $html.=ob_get_clean();
        $html.=generic_footer($orderno, $footer);
        $html.=<<<EOF
    <tr><td align=center><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2440.9172103834117!2d-1.5913746839265208!3d52.2812049797697!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4870cb43d158c367%3A0x2c6ce206d5bd40a7!2sCoco+Rose!5e0!3m2!1sen!2suk!4v1522423010290" width="480" height="350" frameborder="0" style="border:0" allowfullscreen></iframe></td></tr>
    </table>
    </div>
    
    </div>
    </body>
    
    </html>
EOF;
        
//        $dompdf= new DOMPDF();
//        $dompdf->set_paper(array(0,0,(3*72),(11*72)),"portrait");
//        $dompdf->load_html($html);
//        $dompdf->render();
        
//        $attachment=$dompdf->output();
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
//        $attachment_chunk = chunk_split(base64_encode($attachment));
        //define the body of the message.

//copy current buffer contents into $message variable and delete current output buffer
$message = $html;

$host = "mail.cocorose.co.uk";
$username = "cocoshop@cocorose.co.uk";
$password = "S4usages!";

$headers = array (
   'To' => $to,
   'Subject' => $subject,
    'MIME-Version' => '1.0',
    'Content-type' => 'text/html',
        'return-receipt-to' => 'shop@cocorose.co.uk' ,
        'return-path' => 'shop@cocorose.co.uk',
        'From' =>  'Coco Rose'. " <shop@cocorose.co.uk>");

                $smtp = Mail::factory('smtp',
 array ('host' => $host,
     'port'=> 25,
     'auth' => false,
     'socket_options' => array('ssl' => array('verify_peer_name' => false)),
     'debug' => true,
     'username' => $username,
     'password' => $password));
    $mail = $smtp->send($to, $headers, $message);
}

echo "<h2>Receipt Sent</h2>";
}

function giftReceipt($orderno)
{
include '../config.php';
include_once '../functions/auth_func.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

    $now_date=date('d-m-Y');
    $now_time=date('H:i');
    $rollID=$_SESSION['rollID'];
    $person=$_SESSION['POS'];

ob_start();
echo "<table width=100% align=center>";
echo "<tr><td>User:</td><td>".$person."</td><td align=right>Date:</td><td width=28% align=right>$now_date</td></tr>";
echo "<tr><td>Receipt:</td><td>".$rollID."</td><td align=right>Time:</td><td width=28% align=right>$now_time</td></tr>";
echo "<tr><td colspan=4>----------------------------------------</td></tr>";
echo "</table>";

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


$html=generic_header(1);
$html.=ob_get_clean();
$html.=generic_footer($orderno);

#Perform the print
$print=print_action($html,$receipt_printer,false);


}

function printSpendPot($spendPot, $direction="print")
{
include '../config.php';
include_once '../functions/auth_func.php';
$company=getTillCompany($_COOKIE['tillIdent']);
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="select nicename,addr1, addr2, addr3, postcode, telephone, VATno from companies where conum = $company";
$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);
$vat=$result['VATno'];
$telephone=$result['telephone'];

$db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$db_conn3=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="select type, custref, createdDate, amount from spendPots where id = $spendPot";

$results=$db_conn2->query($sql_query);

$result=mysqli_fetch_array($results);

ob_start();
echo "<html><body style=\"left:0px;margin:0px;width:370px\">";
if ($result['type']=='G')
{
	echo "<h1 align=center width=100% style=\"font-size:20pt;\">Gift Voucher</h1><br><br>";
	if (intval($result['amount'])==$result['amount'])
	{
		echo "<p align=center width=100% style=\"font-size:40pt;\">&pound;".number_format($result['amount'],0)."</p>";
	}
	else
	{
		echo "<p align=center width=100% style=\"font-size:40pt;\">&pound;".number_format($result['amount'],2)."</p>";
	}
	echo "<br><br>";
	
	$voucher=getSpendPot($spendPot,'G');
	$spendPot_pad=str_pad($spendPot,8,'0',STR_PAD_LEFT);
	echo "<p width=100% align=center><hr></p>";
	echo "<p width=100% align=center>Gift Voucher expires: ".$voucher['expireDate']."</p><br><br>";
	echo "<p align=center width=100%><img src=\"$base_url/pos/order/barcode.php?orderno=$spendPot_pad&false=.png\" /></p>";
	echo "<p width=100% align=center><br></p>";
	echo "<p width=100% align=center><hr></p>";
	if ($direction=='html')
	{
		$html=ob_get_clean();
		ob_end_clean();
	}
	else
	{
		$html=generic_header(2);
		$html.=ob_get_clean();
	}
	
	
	
}
elseif ($result['type']=='C')
{
	echo "<h2 align=center width=100%>Credit Note</h2>";
	
	session_start();
	$company=getTillCompany($_COOKIE['tillIdent']);
	$now_date=date('d-m-Y');
	$now_time=date('H:i');
	$rollID=$_SESSION['rollID'];
	$person=$_SESSION['POS'];
	

	echo "<table width=100% align=center>";
	echo "<tr><td>User:</td><td>".$person."</td><td align=right>Date:</td><td width=28% align=right>$now_date</td></tr>";
	echo "<tr><td>Receipt:</td><td>".$rollID."</td><td align=right>Time:</td><td width=28% align=right>$now_time</td></tr>";
	echo "<tr><td colspan=4>----------------------------------------</td></tr>";
	echo "</table>";
	
	$sql_query3="select cust.forename, cust.lastname,sp.amount, date_format(sp.expireDate ,'%d-%m-%Y') expireDate, od.status, od.StockRef
		,od.colour, od.size, od.qty,coalesce(od.grandTot, od.actualGrand) paid, oh.cashierid
	,oh.till_session, date_format(sp.createdDate,'%d-%m-%Y %H:%i') createdDate
	from spendPots sp, orderdetail od,orderheader oh, customers cust
	where sp.orderno = od.transno
	and oh.transno = od.transno
	and sp.type = 'C'
	and od.status in ('J','K')
	and oh.custref = cust.custid
	and sp.id= $spendPot";
	$voucher=getSpendPot($spendPot,'C');
	$spendPot_pad=str_pad($spendPot,8,'0',STR_PAD_LEFT);
	$results3=$db_conn3->query($sql_query3);
	if (mysqli_affected_rows($db_conn3)>0)
	{
		echo "<p>Item(s) Returned :</p>";
		while ($result3=mysqli_fetch_array($results3))
		{
			echo "<table width=100%><tr>";
			echo "<td>".$result3['StockRef']."-".$result3['colour']."-".$result3['size']."</td>";
			echo "<td align=right>".$result3['qty']."</td>";
			echo "<td align=right>&pound;".number_format($result3['amount']*-1,2)."</td>";
			$expires=$result3['expireDate'];
			$ref=$result3['cashierid']."/".$result3['till_session']."/".$result3['createdDate'];
			$forename=$result3['forename'];
			$lastname=$result3['lastname'];
			$total_cn_value=$result3['amount'];
		}
		echo "</table>";
	}
	else
	{
		#This is an overflow credit note, so we just want the spendpot details
		$sql_query3="select cust.forename, cust.lastname,sp.amount, date_format(sp.expireDate ,'%d-%m-%Y') expireDate, od.status, od.StockRef
		,od.colour, od.size, od.qty,coalesce(od.grandTot, od.actualGrand) paid, oh.cashierid
	,oh.till_session, date_format(sp.createdDate,'%d-%m-%Y %H:%i') createdDate
	from spendPots sp, orderdetail od,orderheader oh, customers cust
	where sp.orderno = od.transno
	and oh.transno = od.transno
	and sp.type = 'C'
	and oh.custref = cust.custid
	and sp.id= $spendPot";
		$results3=$db_conn3->query($sql_query3);
		$result3=mysqli_fetch_array($results3);
		$expires=$result3['expireDate'];
		$ref=$result3['cashierid']."/".$result3['till_session']."/".$result3['createdDate'];
		$forename=$result3['forename'];
		$lastname=$result3['lastname'];
		$total_cn_value=$result3['amount'];
	}
	
	echo "<p width=100% align=center><hr></p>";
	echo "<h2 align=center>Credit Note expires</h2>";
	echo "<h2 align=center>".$expires."</h2>";
	echo "<h2 align=center>&pound;".number_format($total_cn_value,2)."</h2>";
	echo "<p width=100% align=center style=\"font-size:10pt;\">".$forename." ".$lastname."</p>";
	echo "<p width=100% align=center>Ref:".$ref."</p><br><br>";
	echo "<p align=center width=100%><img src=\"$base_url/pos/order/barcode.php?orderno=$spendPot_pad&false=.png\" /></p>";
	echo "<p width=100% align=center><br></p>";
	echo "<p width=100% align=center><hr></p>";
	
	echo "<p class=receiptaddress>VAT: ".$vat."</p>";
	echo "<p width=100% align=center><br></p>";
	
	echo "<table width=100%><tr><td align=left>w:cocorose.co.uk</td><td align=right>f:cocorosewarwick</td></tr>";
	echo "<tr><td align=left>i:cocorosewarwick</td><td align=right>p:cocorosewarwick</td></tr>";
	echo "<tr><td colspan=2><br></td></tr>";
	echo "<tr><td align=left>w:kokua.co.uk</td><td align=right>w:kokuasale.co.uk</td></tr>";
	echo "<tr><td colspan=2><hr></td></tr>";
	echo "</table>";
	if ($direction=='html')
	{
		$html=ob_get_clean();
		ob_end_clean();
	}
	else
	{
		$html=generic_header(2);
		$html.=ob_get_clean();
	}
}
elseif ($result['type']=='D')
{
echo "<h2 align=center width=100%>Deposit Note</h2>";
	
	session_start();
	$company=getTillCompany($_COOKIE['tillIdent']);
	$now_date=date('d-m-Y');
	$now_time=date('H:i');
	$rollID=$_SESSION['rollID'];
	$person=$_SESSION['POS'];
	

	echo "<table width=100% align=center>";
	echo "<tr><td>User:</td><td>".$person."</td><td align=right>Date:</td><td width=28% align=right>$now_date</td></tr>";
	echo "<tr><td>Receipt:</td><td>".$rollID."</td><td align=right>Time:</td><td width=28% align=right>$now_time</td></tr>";
	echo "<tr><td colspan=4>----------------------------------------</td></tr>";
	echo "</table>";
	
	$sql_query3="select cust.forename, cust.lastname,sp.amount, date_format(sp.expireDate ,'%d-%m-%Y') expireDate, od.status, od.StockRef
		,od.colour, od.size, od.qty,coalesce(od.grandTot, od.actualGrand) paid, oh.cashierid
	,oh.till_session, date_format(sp.createdDate,'%d-%m-%Y %H:%i') createdDate
	from spendPots sp, orderdetail od,orderheader oh, customers cust
	where sp.orderno = od.transno
	and oh.transno = od.transno
	and sp.type = 'D'
	and od.status in ('A')
	and oh.custref = cust.custid
	and sp.id= $spendPot";
	$voucher=getSpendPot($spendPot,'D');
	$spendPot_pad=str_pad($spendPot,8,'0',STR_PAD_LEFT);
	$results3=$db_conn3->query($sql_query3);

	while ($result3=mysqli_fetch_array($results3))
	{
		echo "<table width=100%><tr>";
		echo "<td>".$result3['StockRef']."-".$result3['colour']."-".$result3['size']."</td>";
		echo "<td align=right>".abs($result3['qty'])."</td>";
		echo "<td align=right>&pound;".number_format(abs($result3['paid']),2)."</td>";
		$expires=$result3['expireDate'];
		$ref=$result3['cashierid']."/".$result3['till_session']."/".$result3['createdDate'];
		$forename=$result3['forename'];
		$lastname=$result3['lastname'];
	}
	echo "</table>";
	echo "<p>";
	
	echo "</p>";
	echo "<p width=100% align=center><hr></p>";
	echo "<h2>Deposit Note expires: ".$expires."</h2><br><br>";
	echo "<p width=100% align=center style=\"font-size:10pt;\">".$forename." ".$lastname."</p>";
	echo "<p width=100% align=center>Ref:".$ref."</p><br><br>";
	echo "<p align=center width=100%><img src=\"$base_url/pos/order/barcode.php?orderno=$spendPot_pad&false=.png\" /></p>";
	echo "<p width=100% align=center><br></p>";
	echo "<p width=100% align=center><hr></p>";
	
	echo "<p class=receiptaddress>VAT: ".$vat."</p>";
	echo "<p width=100% align=center><br></p>";
	
	echo "<table width=100%><tr><td align=left>w:cocorose.co.uk</td><td align=right>f:cocorosewarwick</td></tr>";
	echo "<tr><td align=left>i:cocorosewarwick</td><td align=right>p:cocorosewarwick</td></tr>";
	echo "<tr><td colspan=2><br></td></tr>";
	echo "<tr><td align=left>w:kokua.co.uk</td><td align=right>w:kokuasale.co.uk</td></tr>";
	echo "<tr><td colspan=2><hr></td></tr>";
	echo "</table>";

	if ($direction=='html')
	{
		$html=ob_get_clean();
		ob_end_clean();
	}
	else
	{
		$html=generic_header(2);
		$html.=ob_get_clean();
	}
	
}


#Perform the print
if ($direction=='html')
{
	ob_end_clean();
	return $html;
}

else 
{
	$print=print_action($html,$receipt_printer, false);
}

}


function printSpendPotTR($spendPot, $direction="html")
{
	session_start();
	include '../config.php';
	include_once '../functions/auth_func.php';
	$company=getTillCompany($_COOKIE['tillIdent']);
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);


	$sql_query="select type, custref, createdDate, amount from spendPots where id = $spendPot";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);

	$sql_query2="select date_format(createdDate,'%d-%m-%Y') transDate from spendpots where id =$spendPot";
	$results2=$db_conn2->query($sql_query2);
	$result2=mysqli_fetch_array($results2);
	$now_date=$result2['transDate'];
	$sql_query2="select date_format(createdDate,'%H:%i:%s') transTime from spendpots where id =$spendPot";
	$results2=$db_conn2->query($sql_query2);
	$result2=mysqli_fetch_array($results2);
	$now_time=$result2['transTime'];
	
	$sql_query2="select rollID from tillrolldetail td, spendpots sp where action = '".$result['type']."' 
    		and date_format(td.timestamp, '%Y-%m-%d %H:%i') = date_format(sp.createdDate, '%Y-%m-%d %H:%i')
        	and sp.id = $spendPot";
	$results2=$db_conn2->query($sql_query2);
	$result2=mysqli_fetch_array($results2);	
	$rollID=$result2['rollID'];
	ob_start();
	
	echo "<html><body style=\"left:0px;margin:0px;width:370px\">";
	echo "<table width=100% align=center>";
	echo "<tr><td></td><td></td><td align=right>Date:</td><td width=28% align=right>$now_date</td></tr>";
	echo "<tr><td>Receipt:</td><td>".$rollID."</td><td align=right>Time:</td><td width=28% align=right>$now_time</td></tr>";
	echo "</table>";
	
	$sql_query2="select Description, td.amount from tillrolldetail td, spendpots sp where td.action = '".$result['type']."'
    		and date_format(td.timestamp, '%Y-%m-%d %H:%i') = date_format(sp.createdDate, '%Y-%m-%d %H:%i')
			and sp.id = $spendPot";

	$results2=$db_conn2->query($sql_query2);
	$result2=mysqli_fetch_array($results2);
	
	echo "<table width=100% align=center>";
	echo "<tr><td>".$result2['Description']."</td><td>".$result2['amount']."</td></tr>";
	echo "</table>";
	
	if ($direction=='html')
	{
		$html=ob_get_clean();
		ob_end_clean();
	}
	else
	{
		$html=generic_header(2);
		$html.=ob_get_clean();
	}
	return $html;
}


function printTollRoll($till, $tillsession)
{
include '../config.php';
include '../functions/auth_func.php';

}

function printPettyCash($id, $direction='print')
{
include '../config.php';
include_once '../functions/auth_func.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
session_start();
$sql_query="select pc.transamnt, pc.cashier, date_format(pc.timestamp,'%d/%m/%Y') dte, date_format(pc.timestamp,'%H:%i') tme , pct.Descr 
	from pettycash pc, pettycashtype pct where pc.transtype=pct.typeid and pc.id = $id";
$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);

if ($direction<>"html")
{
	$rollID=$_SESSION['rollID'];
}

else 
{
	$sql_query="select rollID from tillRollDetail td, pettycash pc where td.action = 'A' 
			and date_format(td.timestamp, '%Y-%m-%d %H:%i') = date_format(pc.timestamp, '%Y-%m-%d %H:%i')
			and pc.id = $id";
	$results2=$db_conn->query($sql_query);
	$result2=mysqli_fetch_array($results2);
	
	$rollID = $result2['rollID'];
}
ob_start();
echo "<html><body style=\"left:0px;margin:0px;width:370px\">";
echo<<<EOF
<style>
body
{
        font-family:Monaco;
        font-size:9pt;
        margin:10px;
}
.receiptaddress
                {
                text-align:center;
                font-family:Monaco;
                font-size:8pt;
                padding:0px;
                margin:0px;
                }
.receiptheader
                {
                text-align:left;
                font-family:Monaco;
                font-size:10pt;
                font-weight:bold;
                }
.bankmoney
{
	text-align:right;
}

td
       			{
					font-family:Monaco;
					font-size:8pt;
					padding:0px;
				}
		
p
		{
			font-family:Monaco;
			font-size:7pt;
			line-height:90%;
			margin:0px;
			padding:0px;
		}
@page { margin:0px;top 0px; margin-top:0px; margin-bottom:0px; }
		
</style>
EOF;
if ($direction<>'html')
{
	echo "<h2 align=center>Coco Rose</h2>";
	if ($result['transamnt']<0)
	{
		echo "<h2 align=center>Petty Cash Payment</h2>";
	}
	else 
	{
		echo "<h2 align=center>Petty Cash Receipt</h2>";
	}
}

echo "<table><tr><td>User: ".$result['cashier']."</td><td align=right>Date: ".$result['dte']."</td></tr>";
echo "<tr><td>Receipt: ".$rollID."</td><td align=right>Time: ".$result['tme']."</td></tr>";
echo "<tr><td colspan=2>--------------------------------------</td></tr>";
echo "<tr><td>Reason</td><td colspan=2 align=right>Amount</td></tr>";
echo "<tr><td>".$result['Descr']."</td><td colspan=2 align=right>&pound;".number_format(abs($result['transamnt']),2)."</td></tr>";
echo "</table>";
if ($direction<>"html")
{	
	echo "<br>";
	
	echo "<p>Comments..............................</p>";
	echo "<p>......................................</p>";
	echo "<p>......................................</p>";
	echo "<br>";
	echo "<br>";
	echo "<p>Signed................................</p>";
}

if ($direction=='html')
{
	$html=ob_get_clean();
	return $html;
}

else 
{
	//$html=generic_header();
	$html=ob_get_clean();
	#Always goes to paper
	#Perform the print
	
	$print=print_action($html,$receipt_printer, false);
		if (substr($print, 1,2)=="re")
		{
			return 1;
		}
		else
		{
			return 0;
		}
}
ob_clean();

} //printPettyCash

function printBarcode($sku, $colour)
{
include '../config.php';
include '../functions/auth_func.php';



}

function printFloat($id)
{
    include '../config.php';
    include_once '../functions/auth_func.php';
    $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
    session_start();
    $sql_query="select td.description, td.amount, th.cashier, date_format(td.timestamp,'%d-%m-%Y') dte, date_format(td.timestamp,'%H:%i') tme
        , td.rollID from tillrolldetail td, tillrollheader th where td.rollID= $id and td.rollID = th.rollID";
    $results=$db_conn->query($sql_query);
    $result=mysqli_fetch_array($results);
    
    ob_start();
    echo "<html><body style=\"left:0px;margin:0px;width:370px\">";
    echo<<<EOF
<style>
body
{
        font-family:Monaco;
        font-size:9pt;
        margin:10px;
}
.receiptaddress
                {
                text-align:center;
                font-family:Monaco;
                font-size:8pt;
                padding:0px;
                margin:0px;
                }
.receiptheader
                {
                text-align:left;
                font-family:Monaco;
                font-size:10pt;
                font-weight:bold;
                }
.bankmoney
{
	text-align:right;
}

td
       			{
					font-family:Monaco;
					font-size:8pt;
					padding:0px;
				}
				
p
		{
			font-family:Monaco;
			font-size:7pt;
			line-height:90%;
			margin:0px;
			padding:0px;
		}
@page { margin:0px;top 0px; margin-top:0px; margin-bottom:0px; }

</style>
EOF;
    echo "<table width=100%><tr><td>User: </td><td>".$result['cashier']."</td><td align=right>Date: </td><td align=right>".$result['dte']."</td></tr>";
    echo "<tr><td>Receipt: </td><td>".$result['rollID']."</td><td align=right>Time: </td><td align=right>".$result['tme']."</td></tr>";
    echo "<tr><td colspan=4>--------------------------------------</td></tr>";
    echo "<tr><td colspan=2>Float Starting value</td><td colspan=2 align=right>&pound;".number_format(abs($result['amount']),2)."</td></tr>";
    echo "</table>";
    
    $html=ob_get_clean();
    return $html;
} //printFloat


function generic_header($header)
{
include '../config.php';
include_once '../functions/auth_func.php';
session_start();
$till=$_COOKIE['tillIdent'];
$tillsession=getTillSession($till);
$company=getTillCompany($till);
$orderno=$_REQUEST['orderno'];


$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

ob_start();
if ($header==2)
{
	echo "<html><body style=\"left:0px;margin:0px;width:420px;background-image:url('../images/tumbling_hearts.png');\">";
}
else {
	echo "<html><body style=\"left:0px;margin:0px;width:420px\">";
}

if ($header==1 || $header==2)
{
	echo "<p width=100% align=center><img width=290 src=\"../images/$company-logo.png\" /><br>";
}
echo <<<EOF
<style>
body
{
        font-family:Monaco;
        font-size:10pt;
        margin:10px;
}
.receiptaddress
                {
                text-align:center;
                font-family:Monaco;
                font-size:8pt;
                padding:0px;
                margin:0px;
                }
.receiptheader
                {
                text-align:left;
                font-family:Monaco;
                font-size:10pt;
                font-weight:bold;
                }
.bankmoney
{
	text-align:right;
}

td
       			{
					font-family:Monaco;
					font-size:10pt;
					padding:0px;
				}
		
p
		{
			font-family:Monaco;
			font-size:10pt;
			line-height:100%;
			margin:0px;
			padding:0px;
		}
@page { margin:0px;top 0px; margin-top:0px; margin-bottom:0px; }

</style>
EOF;

if ($header==1)
{
	$sql_query="select nicename,addr1, addr2, addr3, postcode, telephone, VATno from companies where conum = $company";
	$results=$db_conn->query($sql_query);
	
	while ($result=mysqli_fetch_array($results))
	{
			echo "<p><br></p>";
	        echo "<p class=receiptaddress>".$result['addr1']."</p>";
	        echo "<p class=receiptaddress>".$result['addr2']."</p>";
	        echo "<p class=receiptaddress>".$result['postcode']."</p>";
	        echo "<p class=receiptaddress>".$result['telephone']."</p><br>";
	}
}

return ob_get_clean();

}


function generic_footer($orderno, $footer="true")
{
include '../config.php';
include_once '../functions/auth_func.php';

session_start();
$company=getTillCompany($_COOKIE['tillIdent']);

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="select nicename,addr1, addr2, addr3, postcode, telephone, VATno from companies where conum = $company";
$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);
$vat=$result['VATno'];
$telephone=$result['telephone'];
$customer=getCustomer($orderno);
ob_start();
if ($footer=="true")
{
	$sql_query="select message from receipt_messages where company = ".$company;
	$messages=$db_conn->query($sql_query);
	$message=mysqli_fetch_array($messages);
	
	echo "<p width=100% align=center><hr></p>";
	if ($customer['forename']=='')
	{
		echo "<p width=100% align=center style=\"font-size:14pt;\">".$customer['title']." ".$customer['lastname']."</p>";	
	}
	else {
		echo "<p width=100% align=center style=\"font-size:14pt;\">".$customer['forename']." ".$customer['lastname']."</p>";
	}
}

echo "<br>"; 
echo "<p width=100% align=center>".$message['message']."</p>";
echo "<p align=center width=100%><img src=\"$base_url/pos/order/barcode.php?orderno=$orderno&false=.png\" /></p>";
echo "<p width=100% align=center><br></p>";
echo "<p width=100% align=center><hr></p>";
echo "<p class=receiptaddress>VAT: ".$vat."</p>";
echo "<p width=100% align=center><br></p>";

echo "<table width=100%><tr><td align=left>w:cocorose.co.uk</td><td align=right>f:cocorosewarwick</td></tr>";
echo "<tr><td align=left>i:cocorosewarwick</td><td align=right>p:cocorosewarwick</td></tr>";
echo "<tr><td colspan=2><br></td></tr>";
echo "<tr><td align=left>w:kokua.co.uk</td><td align=right>w:kokuasale.co.uk</td></tr>";
echo "<tr><td colspan=2><hr></td></tr>";
echo "</table>";
echo "</html>";


return ob_get_clean();
}

function print_action($html,$printer, $dupe)
{

include '../config.php';
include_once '../functions/auth_func.php';
include_once '../functions/barcode_func.php';
require_once '../functions/dompdf/dompdf_config.inc.php';

	if ($dupe=="true")
	{
		$orightml=$html;
		$html=$orightml."<p align=center>Customer Copy</p>";
		
		$dompdf= new DOMPDF();
		$dompdf->set_paper(array(0,0,(3*72),(1*72)),"portrait");
		$dompdf->load_html($html);
		$dompdf->render();
		$page_count = $dompdf->get_canvas( )->get_page_count( );
		unset($dompdf);
		$dompdf= new DOMPDF();
		$dompdf->set_paper(array(0,0,(3*72),(1*72)*($page_count+0.1)),"portrait");
		$dompdf->load_html($html);
		$dompdf->render();
		$pdf=$dompdf->output();

		file_put_contents($receipt_tmp."/printing.pdf",$pdf);
		exec('lp -h '.$receipt_host.' -d '.$printer.' '.$receipt_tmp.'/printing.pdf');
		unset($pdf);
		
		
		$html=$orightml."<p align=center>Shop Copy</p>";
		$dompdf= new DOMPDF();
		$dompdf->set_paper(array(0,0,(3*72),(1*72)),"portrait");
		$dompdf->load_html($html);
		$dompdf->render();
		$page_count = $dompdf->get_canvas( )->get_page_count( );
		unset($dompdf);
		$dompdf= new DOMPDF();
		$dompdf->set_paper(array(0,0,(3*72),(1*72)*($page_count+0.1)),"portrait");
		$dompdf->load_html($html);
		$dompdf->render();
		$pdf=$dompdf->output();
		
		file_put_contents($receipt_tmp."/printing.pdf",$pdf);
		return exec('lp -h '.$receipt_host.' -d '.$printer.' '.$receipt_tmp.'/printing.pdf');
		
	}
	
	else 
	{
	
		$dompdf= new DOMPDF();
		$dompdf->set_paper(array(0,0,(3*72),(1*72)),"portrait");
		$dompdf->load_html($html);
		$dompdf->render();
		$page_count = $dompdf->get_canvas( )->get_page_count( );
		unset($dompdf);
		$dompdf= new DOMPDF();
		$dompdf->set_paper(array(0,0,(3*72),(1*72)*($page_count+0.1)),"portrait");
		$dompdf->load_html($html);
		$dompdf->render();
		$pdf=$dompdf->output();
		
		file_put_contents($receipt_tmp."/printing.pdf",$pdf);
		
		#Execute O/S command to print
		return exec('lp -h '.$receipt_host.' -d '.$printer.' '.$receipt_tmp.'/printing.pdf');
	}
}
?>