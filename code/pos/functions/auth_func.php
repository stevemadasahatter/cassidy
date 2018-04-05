<?php
        /**
        * Generates a session for a user signing on to the till
        *
        * The till session is created. This is not a financial EOD type
        * session but just to provide the code with authentication contect
        */


function authenticate($username, $password,$company, $till)
{
	session_start();
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);

	$sql_query="select password, level from users where username ='".$username."'";
	$result=$db_conn->query($sql_query);
	$results=mysqli_fetch_array($result);
	
	#random authenticator
	$random=rand(1,100000);
	if ($results[0]==md5($password))
	{
		#Authenticated	
		$_SESSION[$username]=$random;
		$_SESSION['POS']=$username;
		$_SESSION['level']=$results[1];
		$sql_query="insert into sessions values ('".$random.
		"','".$username."','".$till."','".$company."',0)";
		$update=$db_conn->query($sql_query);
		$done=mysqli_commit($db_conn);
		syslog(LOG_INFO,"User $username authenticated OK");
		$_SESSION['rollID']=newTillRoll();
		return 0;
	}
	else 
	{
		return 1;
	}
}

function deauthenticate()
{
    include '/var/www/pos/config.php';
    $db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);
	$username=$_SESSION['POS'];
	$session=$_SESSION[$username];
	closeTillRoll();
	unset($_SESSION['POS']);
	unset($_SESSION[$username]);
	unset($_SESSION['custref']);
	unset($_SESSION['rollID']);
	unset($_SESSION['orderno']);
	session_destroy();
	$sql_query="delete from sessions set where id='".$session."'";
	$result=$db_conn->query($sql_query);
	$done=mysqli_commit($db_conn);
	syslog(LOG_INFO,"User $username logged out");
	

}

function check_auth()
{
	include '/var/www/pos/config.php';
	$username=$_SESSION['POS'];
	$session=$_SESSION[$username];
    $db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);

    $sql_query="select deauth from sessions where id='".$session."'";
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
	$company=getTillCompany($till);
	$EODID=getConfig('EODID-'.$company);
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$sql_query="select session_number as session from till_sessions where till='".$till."' and active=1 and EODID=$EODID";
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

function getTillCoName($till)
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$sql_query="select c.coname from tills t, companies c where t.tillname = '".$till."' and c.conum=t.company";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$num_rows=mysqli_affected_rows($db_conn);
	if ($num_rows>0)
	{
		return $result['coname'];
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
	session_start();
	$till=$_COOKIE['tillIdent'];
	$company=getTillCompany($_COOKIE['tillIdent']);
	$EODID=getConfig('EODID-'.$company);
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	if ($status==1)
	{
		$sql_query="insert into till_sessions (till, active, company, EODID) values ('".$till."',1,$company, $EODID)";
		$results=$db_conn->query($sql_query);
		$tillsession_in=mysqli_insert_id($db_conn);
		$EODID=getConfig('EODID-'.$company);
		
		#Is there already a tilldrawer for a previous till
		$sql_query="select tillsession, startval, closeval from tilldrawer where EODID=".$EODID;
		$tillsessions=$db_conn->query($sql_query);
		$till_session=mysqli_fetch_array($tillsessions);
		$num_rows=mysqli_affected_rows($db_conn);
		
		if ($num_rows>0)
		{	
			$sql_query="insert into tilldrawer (till, tillsession, EODID, startval) values ('".$till."',".$tillsession_in.",$EODID,".$till_session['startval'].")";
			echo $sql_query;
		}
		else 
		{
			#This is the first of the day, so we need to take the previous value
			$prevEOD=$EODID-1;
			$sql_query="select tillsession, startval, closeval from tilldrawer where EODID=".$prevEOD;
			echo $sql_query;
			$tillsessions=$db_conn->query($sql_query);
			$till_session=mysqli_fetch_array($tillsessions);
			$sql_query="insert into tilldrawer (till, tillsession, EODID, startval) values ('".$till."',".$tillsession_in.",$EODID,".$till_session['closeval'].")";
			echo $sql_query;
		}
		$doit=$db_conn->query($sql_query);
	}
	else 
	{
		$sql_query="update till_sessions set active=0 where till='".$till."'";
		$results=$db_conn->query($sql_query);
	}
	$results=$db_conn->query($sql_query);
	
}

function getOrderDetail($orderno, $detail, $till)
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$tillsession=getTillSession($till);
	$sql_query="select custref, transno, netTot, vatTot, grandTot from orderheader where transno = $orderno and till = '".$till."'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	return $result[$detail];
}

function createOrder($till, $custref)
{
	session_start();
	$custref=$_SESSION['custref'];
	$cashier=$_SESSION['POS'];
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$sql_query="select discount from customers where custid = $custref";
	$results=$db_conn->query($sql_query);
	$discount=mysqli_fetch_array($results);
	$tillsession=getTillSession($till);
	$tillcompany=getTillCompany($till);
	$sql_query="insert into orderheader (company, till, till_session, custref,cashierid, discount) values ($tillcompany, '".$till."','".$tillsession."','".$custref."','".$cashier."',".$discount['discount'].")";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$_SESSION['orderno']=mysqli_insert_id($db_conn);
	return mysqli_insert_id($db_conn);
	
}

function changeCust($orderno, $custref)
{
	session_start();
	$custref=$_SESSION['custref'];
    include '/var/www/pos/config.php';
    $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
    $sql_query="select discount from customers where custid = $custref";
    $results=$db_conn->query($sql_query);
    $discount=mysqli_fetch_array($results);
    
	$till=$_COOKIE['tillIdent'];
    $tillsession=getTillSession($till);
    $tillcompany=getTillCompany($till);
	$sql_query="update orderheader set custref='".$custref."', discount=".$discount['discount']." where till='".$till."' and till_session = '".$tillsession."' and transno=$orderno";
	$results=$db_conn->query($sql_query);
}

function getOrderLinesCnt()
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	session_start();
	$till=$_COOKIE['tillIdent'];
	$tillsession=getTillSession($till);
	$orderno=$_SESSION['orderno'];
	$sql_query="select max(lineno) lineno from orderdetail, orderheader where orderheader.transno=$orderno and orderheader.transno=orderdetail.transno";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	return $result['lineno'];
}

function getItemSize($sizeindex, $sku)
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);	
	$sql_query="select size".$sizeindex." size from style, sizes where style.sizekey=sizes.sizekey and style.sku='".$sku."'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	return $result['size'];
}

function getItemPrice($sku2, $colour)
{
    $sku=urldecode($sku2);
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$sql_query="select style.onsale, stock.saleprice, stock.retailprice, vatrates.rate, stock.vatable , stock.costprice from stock,style, vatrates 
			where stock.Stockref='".$sku."' and stock.colour='".$colour."' and style.vatkey = vatrates.vatkey and stock.Stockref=style.sku";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
		if ($result['onsale']==1 && $result['saleprice']==0)
		{	
			$returnprice['sale']="";
		}
		elseif ($result['onsale']==1 && $result['saleprice']<>0)
		{
			$returnprice['sale']=$result['saleprice'];
		}
		else
		{
			$returnprice['sale']="";
		}
		$returnprice['price']=$result['retailprice'];	
		$returnprice['costprice']=$result['costprice'];
	return array('price'=>$returnprice['price'], 'sale'=>$returnprice['sale'], 'rate'=>$result['rate'], 'onsale'=>$result['onsale'], 'vatable'=>$result['vatable'], 'costprice'=>$returnprice['costprice']);
}

function bagTotals($order)
{
	session_start();
	include '/var/www/pos/config.php';
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
	$sql_query="select if(abs(actualnet)>0||zero_price=1,actualnet,nettot) nettot,if(abs(actualvat)>0||zero_price=1,actualvat,vattot) vattot
			, if(abs(actualgrand)>0||zero_price=1,actualgrand,grandtot) grandTot, (grandTot-actualgrand) discountamt, onsale, qty 
			from orderdetail where transno = ".$orderno." and status in ('N','P','V','C')";
	$results=$db_conn->query($sql_query);

	$count=0;
	while ($totitems=mysqli_fetch_array($results))
	{
		$total+=$totitems['grandTot']*$totitems['qty'];
		$vat+=$totitems['vattot']*$totitems['qty'];
		$net+=$totitems['nettot']*$totitems['qty'];
		$discountamt+=$totitems['discountamt']*$totitems['qty'];
		$count+=$totitems['qty'];
		if ($totitems['onsale']==1)
		{
			$totalOutstanding+=$totitems['grandTot']*$totitems['qty'];
		}
		elseif ($totitems['onsale']<>1)
		{
			$totalOutstanding+=(($totitems['grandTot']/100)*(100-$custDiscount))*$totitems['qty'];
		}
		
	}
	
	#Get credit note
	$sql_query="select if(abs(actualnet)>0||zero_price=1,actualnet,nettot) nettot,if(abs(actualvat)>0||zero_price=1,actualvat,vattot) vattot
			, if(abs(actualgrand)>0||zero_price=1,actualgrand,grandtot) grandTot, (grandTot-actualgrand) discountamt, onsale, qty 
			from orderdetail where transno = ".$orderno." and (status in ('K','J'))";
	$results=$db_conn->query($sql_query);
	

	while ($totitems=mysqli_fetch_array($results))
	{
		$total-=$totitems['grandTot']*$totitems['qty'];
		$vat-=$totitems['vattot']*$totitems['qty'];
		$net-=$totitems['nettot']*$totitems['qty'];
		$discountamt-=$totitems['discountamt']*$totitems['qty'];
		//$count+=$totitems['qty'];
		if ($totitems['onsale']==1)
		{
			$totalOutstanding-=$totitems['grandTot']*$totitems['qty'];
		}
		elseif ($totitems['onsale']<>1)
		{
			$totalOutstanding-=(($totitems['grandTot']/100)*(100-$custDiscount))*$totitems['qty'];
		}
	
	}
			
	$sql_query="select sum(PayValue) grandTot from tenders where transno = ".$orderno;
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	
	$paid=$result['grandTot'];
	
	$totalOutstanding=round(($totalOutstanding-$paid),2);
		
	return array('total' =>$total, 'count'=>$count, 'vat'=>$vat, 'net'=>$net, 'paid' => $paid, 'discount' => $custDiscount, 'discountamt' => $discountamt, 'outstanding'=> $totalOutstanding);
}


function receiptTotals($order)
{
	session_start();
	include '/var/www/pos/config.php';
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
	$sql_query="select (if (od.actualnet > 0, od.actualnet,od.netTot)) nettot,(if (od.actualvat > 0, od.actualvat,od.vatTot)) vattot
			, (if (od.actualgrand > 0, od.actualgrand,od.grandTot)) grandTot, onsale from orderdetail od where transno = ".$orderno." and (status = 'C')";
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
    include '/var/www/pos/config.php';
	$till=$_COOKIE['tillIdent'];
	$company=getTillCompany($till);
	if ($_SESSION['orderno']<>"")
	{
		$orderno=$_SESSION['orderno'];
	}
    $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
    $custref=getOrderDetail($orderno, 'custref', $till);
	$sql_query="select title,forename, lastname, email, custid from customers where company=$company and custid=$custref";
    $results=$db_conn->query($sql_query);
    $result=mysqli_fetch_array($results);
    return $result;
}

function appro($lineno)
{
	session_start();
	include '/var/www/pos/config.php';
	$orderno=$_SESSION['orderno'];
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$sql_query="select StockRef, colour, size, qty, status, onsale from orderdetail where transno = ".$orderno." and lineno = $lineno";

	$results=$db_conn->query($sql_query);

	$whichway=mysqli_fetch_array($results);

	if ($whichway['status']=='A')
	{
		#Create a negative record first and then a processing record
		$sql_query="select transno, StockRef, colour, size, sizeindex, qty, netTot, vatTot, grandTot, actualnet, actualvat, actualgrand from orderdetail where transno = $orderno and lineno =$lineno";
		$newrecords=$db_conn->query($sql_query);
		$newrecord=mysqli_fetch_array($newrecords);
		if ($newrecord['actualgrand']=="")
		{
			$newrecord['actualgrand']='NULL';
			$newrecord['actualvat']='NULL';
			$newrecord['actualnet']='NULL';
		}
		$nextline=getOrderLinesCnt();
		#get costprice
		$sql_query="select costprice from stock where Stockref = '".$newrecord['StockRef']."' and colour = '".$newrecord['colour']."'";
		$costprices=$db_conn->query($sql_query);
		$costprice=mysqli_fetch_array($costprices);
		
		$sql_query="insert into orderdetail (onsale,transno, StockRef, colour, size, sizeindex, lineno, qty, status, netTot, vatTot, grandTot, costprice, actualNet, actualVat, actualgrand) values (".$newrecord['onsale'].",$orderno,'".$newrecord['StockRef']."','".$newrecord['colour']
				."','".$newrecord['size']."',".$newrecord['sizeindex'].",".($nextline+1).",".($newrecord['qty']*-1).",'X',".$newrecord['netTot'].",".$newrecord['vatTot'].",".$newrecord['grandTot'].",".$costprice['costprice']."
						,".$newrecord['actualnet'].",".$newrecord['actualvat'].",";
		if ($newrecord['actualgrand']=="")
		{
				$sql_query.="NULL)";
		}
		else
		{
			$sql_query.=$newrecord['actualgrand'].")";
		}
		$negrecord=$db_conn->query($sql_query);
		
		#Take original line off appro
		$sql_query="update orderdetail set status = 'X' where transno = $orderno and lineno = $lineno";
		$doit=$db_conn->query($sql_query);
		
		$sql_query="insert into orderdetail (transno, StockRef, colour, size, sizeindex, lineno, qty, status, netTot, vatTot, grandTot,costprice, actualNet, actualVat, actualgrand) values ($orderno,'".$newrecord['StockRef']."','".$newrecord['colour']
				."','".$newrecord['size']."',".$newrecord['sizeindex'].",".($nextline+2).",".($newrecord['qty']*1).",'P',".$newrecord['netTot'].",".$newrecord['vatTot'].",".$newrecord['grandTot'].",".$costprice['costprice']."
						,".$newrecord['actualnet'].",".$newrecord['actualvat'].",";
		if ($newrecord['actualgrand']=="")
		{
			$sql_query.="NULL)";
		}
		else
		{
			$sql_query.=$newrecord['actualgrand'].")";
		}
		$doit2=$db_conn->query($sql_query);			
	}
	else 
	{
		$sql_query="update orderdetail set status = 'A' where transno = $orderno and lineno = $lineno";
		$results=$db_conn->query($sql_query);
	}
	return array('sku'=>$whichway['StockRef'], 'colour'=>$whichway['colour'], 'size'=>$whichway['size'], 'qty'=>$whichway['qty']);
}


function getTenderTotals()
{
        session_start();
        include '/var/www/pos/config.php';
        $orderno=$_SESSION['orderno'];
        $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
        $sql_query="select sum(abs(if(abs(actualgrand)>0||zero_price=1, actualgrand, grandTot))*qty) payValue, sum(abs(if(abs(actualvat)>0||zero_price=1, actualvat, vatTot))*qty) vatTot, sum(abs(if(abs(actualnet)>0||zero_price=1, actualnet, netTot))*qty) netTot, count(*) count 
        		from orderdetail where transno = ".$orderno." and status in ('C','J','K')";

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
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	#Build starting point picture for stock
	$sql_query="select physical1, physical2, physical3, physical4, physical5, physical6, physical7, physical8, physical9, physical10, physical11, physical12, physical13
			, physical14, physical15, physical16, physical17, physical18, physical19, physical20
			from stock where Stockref='".$sku."' 
			and colour = '".$colour."'";

	$results=$db_conn->query($sql_query);
	$stockrecord=mysqli_fetch_array($results);

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
	#Build adjustments per stock at datetrack
	$sql_query="select  orderdetail.status, sizeindex,  sum(qty) qty from orderdetail, orderheader where orderdetail.transno = orderheader.transno and 
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

/**
	* Apply discount to a line
	*
	* Called by discount.php this code reduces the price of a line by an amount. Although the panel takes a percentage, this function works with amounts
	* and so $amount must be the cash value reduction
	*
	*/
function discountLine($sku,$amount,$overridePrice,$lineno)
{
	//session_start();
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$orderno=$_SESSION['orderno'];
	#get VAT rate
	$sql_query="select vatrates.rate from vatrates, style where style.sku='".$sku."' and style.vatkey=vatrates.vatkey";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	
	$sql_query="select vatable from stock where StockRef = '$sku'";
	$vatables=$db_conn->query($sql_query);
	$vatable=mysqli_fetch_array($vatables);
	
	
	#Set VAT, actual and net
	if ($overridePrice==0)
	{
		$actualgrand=$amount;
		if ($vatable['vatable']==1)
		{
			$actualnet=($amount/(100+$result['rate'])*100);
			$actualvat=($amount-($amount/(100+$result['rate'])*100));
		}
		else
		{
			$actualnet=$amount;
			$actualvat=0;
		}
		if ($actualgrand==0)
		{
			#We have overidden the price to zero
			$zero_price=1;
		}
		else {
			$zero_price=0;
		}
		$sql_query="update orderdetail set actualgrand=$actualgrand, actualnet=".$actualnet.", actualvat=".$actualvat.", zero_price=$zero_price where transno=$orderno and StockRef='$sku' and lineno=$lineno";

	}
	
	else 
	{
		$grandTot=$amount;
		if ($vatable['vatable']==1)
		{
			$netTot=($amount/(100+$result['rate'])*100);
			$vatTot=($amount-($amount/(100+$result['rate'])*100));
		}
		else
		{
			$netTot=$amount;
			$vatTot=0;
		}
		if ($grandTot==0)
		{
			#We have overidden the price to zero
			$zero_price=1;
		}
		else {
			$zero_price=0;
		}
		$sql_query="update orderdetail set grandTot=$grandTot, netTot=".$netTot.", vatTot=".$vatTot.", actualgrand=NULL, actualvat=NULL, actualnet=NULL, zero_price=$zero_price where transno=$orderno and StockRef='$sku' and lineno=$lineno";

	}
	echo $sql_query;
	$results=$db_conn->query($sql_query);
}

function updateReadout()
{
        session_start();
        include '/var/www/pos/config.php';
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
        include '/var/www/pos/config.php';
        $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

        $sql_query="update readout set action=0";
        $do_it=$db_conn->query($sql_query);
	return 0;
}

function getPettyCash($till, $EODID=NULL)
{
	session_start();
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	if ($till=="")
	{
		$till=$_COOKIE['tillIdent'];
	}
	$company=getTillCompany($till);
	
	$active=0;
	if ($EODID==NULL)
	{
	      $EODID=getConfig('EODID-'.$company);
	      $active=1;
	}
	$sql_query="select ts.session_number,td.startval,td.closeval
		from till_sessions ts, tilldrawer td
		where ts.session_number = (select max(session_number) from till_sessions where EODID=".($EODID-1).")
		and td.tillsession=ts.session_number";
	
	$closevals=$db_conn->query($sql_query);
	$closeval=mysqli_fetch_array($closevals);
	
	$sql_query="select ts.session_number,td.startval,td.closeval
		from till_sessions ts, tilldrawer td
		where ts.session_number = (select max(session_number) from till_sessions where active=$active and EODID=".($EODID).")
		and td.tillsession=ts.session_number";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	
	return array('startval'=>$result['startval'], 'closeval'=>$result['closeval'], 'prevcloseval'=>$closeval['closeval']);
	
}

function decodeBarcode($barcode)
{
	session_start();
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	#parse barcode
	$stkbarcode=substr($barcode,0,7);
	$colourbarcode=substr($barcode,7,4);
	$sizebarcode=substr($barcode,11,3);
	
	#Get sku and sizekey
	$sql_query="select sku,sizekey from style where barcode=$stkbarcode";
	$stkdetails=$db_conn->query($sql_query);
	$stkdetail=mysqli_fetch_array($stkdetails);

	if (substr($sizebarcode,0,1)=="A")
	{
		#Newly encoded using new barcode technique
		$sizeindex=substr($sizebarcode,1,2);
	}
	else
	{
		#Inherited barcode encoding
		#Get the size lookup value
		$sql_query="select size from oldbarcodesizes where BarCodeNo=$sizebarcode";
		$oldbarcodesizes=$db_conn->query($sql_query);
		$oldbarcodesize=mysqli_fetch_array($oldbarcodesizes);
	
		#get size index
		$sql_query="select size1,size2,size3,size4,size5,size6,size7,size8,size9,size10 from sizes where sizekey = ".$stkdetail['sizekey'];
		$sizes=$db_conn->query($sql_query);
		$size=mysqli_fetch_array($sizes);
		$sizeindex=array_search($oldbarcodesize['size'],$size)+1;

	}
	
	#Get colour
	$sql_query="select colour from colours where barcode = $colourbarcode";
	$colours=$db_conn->query($sql_query);
	$colour=mysqli_fetch_array($colours);
	return array('sizeindex'=>$sizeindex,'sku'=>$stkdetail['sku'],'colour'=>$colour['colour']);
}

function openDrawer($receipt)
{
        session_start();
        include '/var/www/pos/config.php';
        $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	if ($receipt==0)
	{
		#Must be an sale - sale will write the tillroll
		$sql_query="insert into drawer_activity (receipt, orderno) values (0,".$_SESSION['orderno'].")";
	}
	else
	{
		#Adhoc open request, so generate receipt
		$sql_query="insert into drawer_activity (receipt) values (1)";

	}

	$doit=$db_conn->query($sql_query);
	
	#Open the drawer
	$return=exec('/bin/echo -e -n "\x1b\x70\x30\x40\x50" | lp -o raw -h '.$receipt_host.' -d '.$receipt_printer);
				
}

function getNextBarcode()
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$sql_query="select max(barcode) barcode from style";
	$maxes=$db_conn->query($sql_query);
	
	$max=mysqli_fetch_array($maxes);
	
	return $max['barcode']+1;
}

function createSpendPot($type, $action, $amount, $custref, $orderno, $reason, $id, $tenderNo)
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	if ($action=="create")
	{
		if ($type<>"C")
		{	
			$sql_query="insert into spendPots (orderno, custref, reason, type, amount, tenderNo, expireDate)
						values (".$orderno.",".$custref.",'".$reason."', '".$type."', $amount, '".$tenderNo."', CURDATE() + INTERVAL 1 YEAR)";
			$doit=$db_conn->query($sql_query);
			$return_code=mysqli_insert_id($db_conn);
			if ($reason=="Overflow")
			{
				$till=$_COOKIE['tillIdent'];
				$tillsession=getTillSession($till);
				
				$company=getTillCompany($till);
				$tenderNo=getMaxSpendTender()+1;
				#Remove the tender amount
				$sql_query="insert into spendPotTenders (company, till, till_session, transno, PayType, PayValue, tenderNo, PayMethod)
				values ($company, '$till','$tillsession',".$_SESSION['orderno'].",2
                        ,".$amount.",$tenderNo, 8)";
				$doit=$db_conn->query($sql_query);
			}
		}
		else
		{
			$sql_query="insert into spendPots (orderno, custref, reason, type, amount, tenderNo, expireDate)
						values (".$orderno.",".$custref.",'".$reason."', '".$type."', $amount, '".$tenderNo."', CURDATE() + INTERVAL 6 MONTH)";
			$doit=$db_conn->query($sql_query);
			$return_code=mysqli_insert_id($db_conn);
			if ($reason=="Overflow")
			{
				$till=$_COOKIE['tillIdent'];
				$tillsession=getTillSession($till);
			
				$company=getTillCompany($till);
				$tenderNo=getMaxSpendTender()+1;
				#Remove the tender amount
				$sql_query="insert into spendPotTenders (company, till, till_session, transno, PayType, PayValue, tenderNo, PayMethod)
				values ($company, '$till','$tillsession',".$_SESSION['orderno'].",2
                        ,".$amount.",$tenderNo, 9)";
				$doit=$db_conn->query($sql_query);
			}
				
		}
		#Print code
		return $return_code;
	}
	
	elseif ($action=="clear")
	{
		$usedDate=date('d/m/Y H:i:s');
		
		$sql_query="update spendPots set usedDate=str_to_date('".$usedDate."','%d/%m/%Y %H:%i:%s') , orderno=".$_SESSION['orderno']."
			where id=$id";
		$doit=$db_conn->query($sql_query);
		return mysqli_insert_id($db_conn);
	}
}

function getSpendPot($id, $type="")
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	if ($type=="")
	{
		$sql_query="select amount, date_format(expireDate,'%d/%m/%Y') expireDate, usedDate, type from spendPots where id =$id";
	}
	else
	{
		$sql_query="select amount, date_format(expireDate,'%d/%m/%Y') expireDate, usedDate, type from spendPots where id =$id and type='".$type."'";
	}
	$results=$db_conn->query($sql_query);
	$voucher=mysqli_fetch_array($results);
	
	return $voucher;
}

function getMaxSpendTender()
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$sql_query="select max(tenderNo) tenderno from spendPotTenders";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	
	return $result['tenderno'];
}

function newTillRoll()
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$till_session=getTillSession($_COOKIE['tillIdent']);
	#Perhaps we already have a rollID that we didn't use yet
	$sql_query="select max(rollID) rollID from tillrollheader";
	$rollIDmaxs=$db_conn->query($sql_query);
	$rollIDmax=mysqli_fetch_array($rollIDmaxs);
	
	$sql_query="select count(*) cnt from tillrolldetail where rollID = ".$rollIDmax['rollID'];
	$do_it=$db_conn->query($sql_query);
	$cnt=mysqli_fetch_array($do_it);
	
	if ($cnt['cnt']>0)
	{
		#We need a new tillroll
		$sql_query="insert into tillRollHeader (cashier,till, till_session) values ('".$_SESSION['POS']."','".$_COOKIE['tillIdent']."',$till_session)";
		$do_it=$db_conn->query($sql_query);
		
		return mysqli_insert_id($db_conn);
		
	}
	else
	{
		#Reuse existing
		return $rollIDmax['rollID'];
	}
}

function closeTillRoll()
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	#Close down tillroll ID
	$sql_query="update tillRollHeader set active=0 where rollID=".$_SESSION['rollID'];
	$do_it=$db_conn->query($sql_query);
}

function createRollEntry($custID,$descr,$qty,$amnt,$action)
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	session_start();
	#create till roll entry
	
	if ($custid=="")
	{
		$custid=0;
	}
	if ($_SESSION['orderno']=="")
	{
		$_SESSION['orderno']=0;
	}
	if ($qty=="")
	{
		$qty=0;
	}
	$sql_query="insert into tillRollDetail (rollID,custid, description, qty, amount, action,orderno) 
			values (".$_SESSION['rollID'].",coalesce('".$custid."',0),'".$descr."',$qty,'$amnt',coalesce('".$action."'),coalesce('".$_SESSION['orderno']."',0))";
	$do_it=$db_conn->query($sql_query);
}

function getWebImage($sku, $colour)
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$sql_query="select photo from webdetails where sku='".$sku."' and colour = '".$colour."'";
	$results=$db_conn->query($sql_query);
	
	#Only one value is returned
	$result=mysqli_fetch_array($results);
	$photos=preg_split('/\|/', $result['photo']);
	
	return $photos;
}

function getConfig($value)
{
	include '/var/www/pos/config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$sql_query="select value from config where config = '".$value."'";
	$results=$db_conn->query($sql_query);
	
	#Only one value is returned
	$result=mysqli_fetch_array($results);
	
	return $result['value'];
	
}
?>

