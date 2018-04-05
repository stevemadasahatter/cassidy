<?php

function authenticate($username, $password,$company)
{
	session_start();
	include '../config.php';
	$db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);

	$sql_query="select password, level from users where username ='".$username."'";
	$result=$db_conn->query($sql_query);
	$results=mysqli_fetch_array($result);
	
	#random authenticator
	$random=rand(1,1000000);
	if ($results[0]==md5($password))
	{
		#Authenticated	
		$_SESSION[$username]=$random;
		$_SESSION['BE']=$username;
		$_SESSION['LEVEL']=$results['level'];
		$_SESSION['CO']=$company;
		$sql_query="insert into sessions values ('".$random.
		"','".$username."','Backoffice','".$company."',0)";
		$update=$db_conn->query($sql_query);
		$done=mysqli_commit($db_conn);
		syslog(LOG_INFO,"User $username authenticated OK");
		
		return 0;
	}
	else 
	{
		return 1;
	}
}

function deauthenticate()
{
    include '../config.php';
    $db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);
	$username=$_SESSION['POS'];
	$session=$_SESSION[$username];
	unset($_SESSION['BE']);
	unset($_SESSION[$username]);
	unset($_SESSION['LEVEL']);
	unset($_SESSION['CO']);
	$sql_query="update sessions set deauth=1 where id='".$session."' and till='Backoffice'";
	$result=$db_conn->query($sql_query);
	$done=mysqli_commit($db_conn);
	syslog(LOG_INFO,"User $username logged out");
}

function check_auth()
{
	include '../config.php';
	$username=$_SESSION['BE'];
	$session=$_SESSION[$username];
    $db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);
    $sql_query="select deauth from sessions where id='".$session."' and till='Backoffice'";
    $result=$db_conn->query($sql_query);
    $results=mysqli_fetch_array($result);
    $num_rows=mysqli_affected_rows($db_conn);
	if ($results['deauth']==0 && $num_rows>0)
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

function getTillSession($till)
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$sql_query="select max(session_number) as session from till_sessions where till='".$till."' and active=1";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$num_rows=mysqli_affected_rows($db_conn);
	if ($num_rows>0)
	{
		return $result['session'];
	}
	else
	{
		return 0;
	}
}

function getTillCompany($till)
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$sql_query="select company from tills where tillname = '".$till."'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$num_rows=mysqli_affected_rows($db_conn);
	if ($num_rows>0)
	{
		return $result['company'];
	}
	else
	{
		return 0;
	}
	
	
}

function getTillType()
{
        include '/var/www/pos/config.php';
        $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
        $sql_query="select type, size from tills where tillname ='".$_COOKIE['tillIdent']."'";
        $results=$db_conn->query($sql_query);
        $result=mysqli_fetch_array($results);
        return array('size' => $result['size'], 'type' => $result['type']);
}

function setTillSession($status)
{
	include '/var/www/pos/config.php';
	$till=$_SERVER['REMOTE_ADDR'];
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	if ($status==1)
	{
		$sql_query="insert into till_sessions (till, active) values ('".$till."',1)";
	}
	else 
	{
		$sql_query="update till_sessions set active=0 where till='".$till."'";
	}
	$results=$db_conn->query($sql_query);
	
}

function getOrderDetail($orderno, $detail, $till)
{
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$tillsession=getTillSession($till);
	$sql_query="select custref, transno, netTot, vatTot, grandTot from orderheader where transno = $orderno and till = '".$till."' and till_session = '".$tillsession."'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	return $result[$detail];
}

function createOrder($till, $custref)
{
	session_start();
	$custref=$_SESSION['custref'];
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$sql_query="select discount from customers where custid = $custref";
	$results=$db_conn->query($sql_query);
	$discount=mysqli_fetch_array($results);
	$tillsession=getTillSession($till);
	$tillcompany=getTillCompany($till);
	$sql_query="insert into orderheader (company, till, till_session, custref,cashierid, discount) values ($tillcompany, '".$till."','".$tillsession."','".$custref."','DUMMY',".$discount['discount'].")";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$_SESSION['orderno']=mysqli_insert_id($db_conn);
	return mysqli_insert_id($db_conn);
	
}

function changeCust($orderno, $custref)
{
	session_start();
	$custref=$_SESSION['custref'];
    include '../config.php';
    $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
    $sql_query="select discount from customers where custid = $custref";
    $results=$db_conn->query($sql_query);
    $discount=mysqli_fetch_array($results);
    
	$till=$_SERVER['REMOTE_ADDR'];
    $tillsession=getTillSession($till);
    $tillcompany=getTillCompany($till);
	$sql_query="update orderheader set custref='".$custref."', discount=".$discount['discount']." where till='".$till."' and till_session = '".$tillsession."' and transno=$orderno";
	$results=$db_conn->query($sql_query);
}

function getOrderLinesCnt()
{
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	session_start();
	$till=$_SERVER['REMOTE_ADDR'];
	$tillsession=getTillSession($till);
	$orderno=$_SESSION['orderno'];
	$sql_query="select max(lineno) lineno from orderdetail, orderheader where orderheader.transno=$orderno and orderheader.transno=orderdetail.transno 
		and till_session ='".$tillsession."' and till='".$till."'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	return $result['lineno'];
}

function getItemSize($sizeindex, $sku)
{
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);	
	$sql_query="select size".$sizeindex." size from style, sizes where style.sizekey=sizes.sizekey and style.sku='".$sku."'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	return $result['size'];
}

function getItemPrice($sku)
{
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$sql_query="select style.onsale, stock.saleprice, stock.retailprice, vatrates.rate from stock,style, vatrates where style.sku='".$sku."' and style.vatkey = vatrates.vatkey and stock.Stockref=style.sku";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	if ($result['onsale']==1)
	{
		$returnprice=$result['saleprice'];
	}
	else
	{
		$returnprice=$result['retailprice'];
	}	
	return array('price'=>$returnprice, 'rate'=>$result['rate'], 'sale'=>$result['onsale']);
}

function bagTotals($order)
{
	session_start();
	include '../config.php';
	$orderno=$_SESSION['orderno'];
	if ($orderno=="")
	{
		$orderno=$order;
	}
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	#Get discount level
	$sql_query="select discount from orderheader where transno = $orderno";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$custDiscount=$result['discount'];
	
	#Get totals
	$sql_query="select coalesce(actualnet, nettot) nettot,coalesce(actualvat,vattot) vattot, coalesce(actualgrand,grandTot) grandTot, onsale from orderdetail where transno = ".$orderno." and (status = 'N' or status = 'P')";
	$results=$db_conn->query($sql_query);

	$count=0;
	while ($totitems=mysqli_fetch_array($results))
	{
		$total+=$totitems['grandTot'];
		$vat+=$totitems['vattot'];
		$net+=$totitems['nettot'];
		$count++;
		if ($totitems['onsale']==1)
		{
			$totalOutstanding+=$totitems['grandTot'];
		}
		elseif ($totitems['onsale']<>1)
		{
			$totalOutstanding+=($totitems['grandTot']/100*(100-$custDiscount));
		}
		
	}
	
	$sql_query="select grandTot from orderheader where transno = ".$orderno;
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	
	$paid=$result['grandTot'];
	

		
	return array('total' =>$total, 'count'=>$count, 'vat'=>$vat, 'net'=>$net, 'paid' => $paid, 'discount' => $custDiscount, 'outstanding'=> $totalOutstanding);
}


function receiptTotals($order)
{
	session_start();
	include '../config.php';
	$orderno=$_SESSION['orderno'];
	if ($orderno=="")
	{
		$orderno=$order;
	}
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

	#Get discount level
	$sql_query="select discount from orderheader where transno = $orderno";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$custDiscount=$result['discount'];

	#Get totals
	$sql_query="select coalesce(actualnet, nettot) nettot,coalesce(actualvat,vattot) vattot, coalesce(actualgrand,grandTot) grandTot, onsale from orderdetail where transno = ".$orderno." and (status = 'C')";
	$results=$db_conn->query($sql_query);

	$count=0;
	while ($totitems=mysqli_fetch_array($results))
	{
	$total+=$totitems['grandTot'];
	$vat+=$totitems['vattot'];
			$net+=$totitems['nettot'];
			$count++;
			if ($totitems['onsale']==1)
			{
				$totalOutstanding+=$totitems['grandTot'];
			}
			elseif ($totitems['onsale']<>1)
			{
				$totalOutstanding+=($totitems['grandTot']/100*(100-$custDiscount));
			}

	}

	$sql_query="select grandTot from orderheader where transno = ".$orderno;
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);

	$paid=$result['grandTot'];



	return array('total' =>$total, 'count'=>$count, 'vat'=>$vat, 'net'=>$net, 'paid' => $paid, 'discount' => $custDiscount, 'outstanding'=> $totalOutstanding);
}

function getCustomer($orderno)
{
	session_start();
    include '../config.php';
	$till=$_SERVER['REMOTE_ADDR'];
	$company=getTillCompany($till);
	if ($_SESSION['orderno']<>"")
	{
		$orderno=$_SESSION['orderno'];
	}
    $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
    $custref=getOrderDetail($orderno, 'custref', $till);
	$sql_query="select forename, lastname, email, custid from customers where company=$company and custid=$custref";
    $results=$db_conn->query($sql_query);
    $result=mysqli_fetch_array($results);
    return $result;
}

function appro($lineno)
{
	session_start();
	include '../config.php';
	$orderno=$_SESSION['orderno'];
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$sql_query="select status from orderdetail where transno = ".$orderno." and lineno = $lineno";

	$results=$db_conn->query($sql_query);

	$whichway=$result=mysqli_fetch_array($results);

	if ($whichway['status']=='A')
	{
		#Create a negative record first and then a processing record
		$sql_query="select transno, StockRef, colour, size, sizeindex, qty, netTot, vatTot, grandTot from orderdetail where transno = $orderno and lineno =$lineno";
		$newrecords=$db_conn->query($sql_query);
		$newrecord=mysqli_fetch_array($newrecords);
		$nextline=getOrderLinesCnt();	
		$sql_query="insert into orderdetail (transno, StockRef, colour, size, sizeindex, lineno, qty, status, netTot, vatTot, grandTot) values ($orderno,'".$newrecord['StockRef']."','".$newrecord['colour']
				."','".$newrecord['size']."',".$newrecord['sizeindex'].",".($nextline+1).",".($newrecord['qty']*-1).",'X',".$newrecord['netTot'].",".$newrecord['vatTot'].",".$newrecord['grandTot'].")";
		$negrecord=$db_conn->query($sql_query);
		
		#Take original line off appro
		$sql_query="update orderdetail set status = 'X' where transno = $orderno and lineno = $lineno";
		$doit=$db_conn->query($sql_query);
		
		$sql_query="insert into orderdetail (transno, StockRef, colour, size, sizeindex, lineno, qty, status, netTot, vatTot, grandTot) values ($orderno,'".$newrecord['StockRef']."','".$newrecord['colour']
				."','".$newrecord['size']."',".$newrecord['sizeindex'].",".($nextline+2).",".($newrecord['qty']*1).",'P',".$newrecord['netTot'].",".$newrecord['vatTot'].",".$newrecord['grandTot'].")";
		$doit2=$db_conn->query($sql_query);			
	}
	else 
	{
		$sql_query="update orderdetail set status = 'A' where transno = $orderno and lineno = $lineno";
		$results=$db_conn->query($sql_query);
	}
	return 0;
}


function getTenderTotals()
{
        session_start();
        include '../config.php';
        $orderno=$_SESSION['orderno'];
        $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
        $sql_query="select sum(coalesce(actualgrand,grandTot)) payValue, sum(coalesce(actualvat,vatTot)) vatTot, sum(coalesce(actualnet,netTot)) netTot, count(*) count from orderdetail where transno = ".$orderno." and status='C'";

        $results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);

	$payamount=$result['payValue'];
	$paycount=$result['count'];
	$vatamount=$result['vatTot'];
	$netamount=$result['netTot'];
        return array('paid' =>$payamount, 'count'=>$paycount, 'vat'=>$vatamount, 'net'=>$netamount);
}

function stockBalance($sku, $colour, $date)
{
	#Date must be yyyy-mm-dd
	session_start();
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	
	#Build starting point picture for stock
	$sql_query="select physical1, physical2, physical3, physical4, physical5, physical6, physical7, physical8, physical9, physical10 
			, physical11 , physical12 , physical13 , physical14 , physical15 , physical15 , physical16 , physical17 , physical18 , physical19 
			from stock where Stockref='".$sku."' 
			and colour = '".$colour."'";

	$results=$db_conn->query($sql_query);
	$stockrecord=mysqli_fetch_array($results);

	#Build adjustments per stock at datetrack
	$sql_query="select  orderdetail.status, orderdetail.sizeindex,  sum(qty) qty from orderdetail, orderheader where orderdetail.transno = orderheader.transno and 
			orderdetail.Stockref = '".$sku."' and orderdetail.colour='".$colour."' and orderdetail.status in ('W','C','J')";
	
	if ($date<>"")
	{
		$sql_query.=" and orderheader.transDate< str_to_date('".$date."','%Y-%m-%d') ";
	}
	$sql_query.=" group by status, sizeindex";
	#build stock picture per stock
	$results=$db_conn->query($sql_query);
	
	while ($stockchange=mysqli_fetch_array($results))
	{
		$stockrecord['physical'.$stockchange['sizeindex']]=$stockrecord['physical'.$stockchange['sizeindex']]-$stockchange['qty'];

	}

	#Apply Stock Adjustments
	$sql_query="select sa.qty, sa.sizeid, sr.polarity from stkAdjustments sa, stkadjreason sr where sa.reasonid = sr.id and sa.sku = '".$sku."' and sa.colour = '".$colour."'";
	
	if ($date<>"")
	{
		$sql_query.=" and datetrack < str_to_date('".$date."','%Y-%m-%d') ";
	}
	
	$stkadjs=$db_conn->query($sql_query);
	while ($stkadj=mysqli_fetch_array($stkadjs))
	{
		$stockrecord['physical'.$stkadj['sizeid']]=$stockrecord['physical'.$stkadj['sizeid']]-($stkadj['qty']*$stkadj['polarity']);
	}
	
	#Will output $stockrecord['physical[sizeid]']
	
	$sql_query="select  orderdetail.status, sizeindex,  sum(qty) qty from orderdetail, orderheader where orderdetail.transno = orderheader.transno and
			orderdetail.Stockref = '".$sku."' and orderdetail.colour='".$colour."' and orderdetail.status = 'A'";
	
	if ($date<>"")
	{
	    $sql_query.=" and orderheader.transDate< str_to_date('".$date."','%Y-%m-%d') ";
	}
	$sql_query.=" group by status, sizeindex";
	$results=$db_conn->query($sql_query);
	while ($stockAppro=mysqli_fetch_array($results))
	{
	    $stockrecord['appro'.$stockAppro['sizeindex']]=$stockAppro['qty'];
	}
	return $stockrecord; 
	
}

function getPurchasedStock($sku, $colour)
{
	session_start();
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	
	#Build starting point picture for stock
	$sql_query="select physical1, physical2, physical3, physical4, physical5, physical6, physical7, physical8, physical9, physical10 from stock where Stockref='".$sku."'
	and colour = '".$colour."'";
	
	$results=$db_conn->query($sql_query);
	$stockrecord=mysqli_fetch_array($results);
	return $stockrecord;
	
}

function discountLine($sku,$amount)
{
	session_start();
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$orderno=$_SESSION['orderno'];
	#get VAT rate
	$sql_query="select vatrates.rate from vatrates, style where style.sku='".$sku."' and style.vatkey=vatrates.vatkey";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$sql_query="update orderdetail set actualgrand=$amount, actualnet=".($amount/(100+$result['rate'])*100).", actualvat=".($amount-($amount/(100+$result['rate'])*100))." where transno=$orderno and StockRef='$sku'";
	$results=$db_conn->query($sql_query);
}

function updateReadout()
{
        session_start();
        include '../config.php';
        $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

	$orderno=$_SESSION['orderno'];
	$custref=$_SESSION['custref'];
	$sql_query="delete from readout";
	$do_it=$db_conn->query($sql_query);
	$sql_query="insert into  readout values ($orderno, $custref, 1)";
	$do_it=$db_conn->query($sql_query);
	return 0;
}

function clearReadout()
{
        session_start();
        include '../config.php';
        $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

        $sql_query="update readout set action=0";
        $do_it=$db_conn->query($sql_query);
	return 0;
}

function getPettyCash($till, $tillsession)
{
	session_start();
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	if ($till=="")
	{
		$till=$_SERVER['REMOTE_ADDR'];
	}

	if ($tillsession=="")
	{
		$tillsession=getTillSession($till);
	}
	$sql_query="select session_number from till_sessions where till='$till' order by session_date desc";
	$results=$db_conn->query($sql_query);
	$session=mysqli_fetch_array($results);
	
	if ($tillsession=="")
	{
		$sql_query="select startVal from tilldrawer where till='$till' and tillsession=".$session['session_number'];
	}
	else
	{	
		$sql_query="select startVal from tilldrawer where till='$till' and tillsession=$tillsession";
	}
		
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	
	return array('startval'=>$result['startVal']);	
}

function getConfig($value)
{
	session_start();
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$sql_query="select value from config where config = '".$value."'";
	$configs=$db_conn->query($sql_query);
	$config=mysqli_fetch_array($configs);
	
	return $config['value'];
}
?>

