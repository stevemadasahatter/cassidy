<?php

include '../config.php';
include '../functions/auth_func.php';
include '../functions/barcode_func.php';
include '../functions/print_func.php';

session_start();
$action=$_REQUEST['action'];
$company=$_SESSION['CO'];

#Active till session?
$active=getTillSession($_COOKIE['tillIdent']);
if ($active==0)
{
        exit();
}

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=='cancel')
{
	# Need to join to tenders because if there is a tender record we can't throw it away
	$sql_query="select status from orderheader, tenders where orderheader.transno = tenders.transno and orderheader.transno = ".$_SESSION['orderno'];
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$numrows=mysqli_affected_rows($db_conn);
	if ($numrows==0)
	{
		# Assess whole order
		$sql_query="select status from orderdetail where transno = ".$_SESSION['orderno'];
		$onappros=$db_conn->query($sql_query);
		$orphaned_rows=mysqli_affected_rows($db_conn);
		
		while ($onappro=mysqli_fetch_array($onappros))
		{
			if ($onappro['status']=="A")
			{
				$isAppro=1;
			}
			elseif($onappro['status']=="X")
			{
				$isAppro=1;
				$isApproBuy=1;
			}
			elseif ($onappro['status']=="P" || $onappro['status']=="C")
			{
				$prevSale=1;
			}
			elseif ($onappro['status']=="N")
			{
				$drop=1;	
			}
			else
			{
				$keep=1;
			}
		}
		if ($prevSale==1 && ($isAppro<>1 || $isApproBuy<>1))
		{
			#Sale was loaded from previous sale. So update back to C and move on
			echo "<p>Clearing Sale...</p>";
			$sql_query="update orderheader set status = 'C' where transno=".$_SESSION['orderno'];
			$result=$db_conn->query($sql_query);
			clearReadout();
			deauthenticate();
			echo "<script type=text/javascript>location.reload();</script>";
			exit();
		}
		elseif ($keep)
		{
			echo "<p>Cannot clear this sale. There are semi-returned or voided items.<br>Undo or process<br>Press refresh to clear this message</p>";
		}
		elseif($drop==1)
		{
			#Throw it away - no tender. User is abandoning
			$sql_query="delete from orderheader where orderheader.transno = ".$_SESSION['orderno'];
			$do_it=$db_conn->query($sql_query);
			$sql_query="delete from orderdetail where orderdetail.transno = ".$_SESSION['orderno'];
			$do_it=$db_conn->query($sql_query);
			$_SESSION['orderno']="";
			$_SESSION['custref']="";
			clearReadout();
			deauthenticate();
			echo "<script type=text/javascript>location.reload();</script>";	
			exit();
		}
		elseif($isAppro==1)
		{
			if($isApproBuy<>1)
			{
				#Leave it
				$_SESSION['orderno']="";
				$_SESSION['custref']="";
				clearReadout();
				deauthenticate();
				echo "<script type=text/javascript>location.reload();</script>";
				exit();
			}
			else {
				$sql_query="update orderdetail set status = 'A' where status = 'X' and transno = ".$_SESSION['orderno'];
				$do_it=$db_conn->query($sql_query);
				
				$sql_query="delete from  orderdetail where status <> 'A' and transno = ".$_SESSION['orderno'];
				$do_it=$db_conn->query($sql_query);

				$sql_query="update orderheader set status = 'C' where transno=".$_SESSION['orderno'];
				$result=$db_conn->query($sql_query);
				 
				$_SESSION['orderno']="";
				$_SESSION['custref']="";
				clearReadout();
				deauthenticate();
				echo "<script type=text/javascript>location.reload();</script>";
				exit();
			}
		  
		  }
		  else 
		  {
		      #No rows at all
		      if ($orphaned_rows==0)
		      {
		          #Nothing going on, drop it like its hot
		          $_SESSION['orderno']="";
		          $_SESSION['custref']="";
		          clearReadout();
		          deauthenticate();
		          echo "<script type=text/javascript>location.reload();</script>";
		          exit();
		      }
		  }
	}
	else 
	{
	    $sql_query="update orderheader set status = 'C' where transno=".$_SESSION['orderno'];
	    $result=$db_conn->query($sql_query);
		#Nothing going on, drop it like its hot
		$_SESSION['orderno']="";
		$_SESSION['custref']="";
		clearReadout();
		deauthenticate();
		echo "<script type=text/javascript>location.reload();</script>";
		exit();
	}
}

elseif ($action=="complete")
{
		#Commit order and Orderlines. Set status to P. Take payment. 
		$sql_query="update orderheader set status = 'P' where transno = ".$_SESSION['orderno'];
		$result=$db_conn->query($sql_query);
		
		$sql_query="update orderdetail set status='P' where transno = ".$_SESSION['orderno']." and status = 'N'";
		$result=$db_conn->query($sql_query);
		echo "<table><tr class=bagheader><td class=bagheader>Customer Name</td><td class=bagheader>Amount Due<td><td rowspan=2>";
		#Is customer eligible?
		$custref=getCustomer($_SESSION['orderno']);
		$sql_query="select email, mobile from customers where custid = ".$custref['custid'];
		$eligs=$db_conn->query($sql_query);
		$elig=mysqli_fetch_array($eligs);
		if ($elig['mobile']<>"")
		{
			echo "<input type=hidden id=depnoteval />
					<button id=appro style=\"font-size:8pt;\" onclick=\"javascript:onAppro();\">Put order On Appro</button>
						<span style=\"vertical-align:center;\"><input style=\"width:25px;\" type=checkbox id=depnote onclick=\"javascript:depNote();\" /><label for=\"depnote\" style=\"font-size:16pt;\">Accept Deposit</label></span>
						";
		}
			
		else
		{
			echo "<input type=hidden id=depnoteval />
					<button id=appro style=\"font-size:8pt;\" onclick=\"javascript:noAppro();\">Put order On Appro</button>
						<span style=\"vertical-align:center;\"><input style=\"width:25px;\" type=checkbox id=depnote onclick=\"javascript:nodepNote();\" /><label for=\"depnote\" style=\"font-size:16pt;\">Accept Deposit</label></span>";
		}
		echo "</td></tr>";
		$totals=bagTotals();
		$customer=getCustomer('');
		#$outstanding=round($totals['total']-($totals['total']*$totals['discount']/100),2);
		$outstanding=$totals['outstanding'];
		if ($outstanding<0)
		{
			#Its a refund or credit note
			$refund=1;
		}
		else 
		{
			$refund=0;
		}
		echo "<tr><td class=totalhead>".$customer['forename']." ".$customer['lastname']."</td><td class=totalhead>&pound;".number_format($outstanding,2)."</td><input type=hidden id=amntout value=\"".$outstanding."\" />";
		echo "</table>";
		if ($refund==0)
		{
			#Voucher code
			echo "<div id=voucherform style=display:none; >";
			echo "<table><tr><td colspan=4><hr></td></tr>";
			echo "<tr><th colspan=3 align=left>Credit Note or Gift Voucher</th><td rowspan=4><button id=vouchconfirm>Confirm<br>Voucher</button><button id=vouchsearch>Search<br> </button>
					<input type=hidden id=voucherid /><input type=hidden id=voucheridtype />";
			$custref=getCustomer($_SESSION['orderno']);

						
			echo "</td></tr>";
			echo "<tr><td>Scan or enter Voucher ID</td><td><input type=text id=vouchercode onkeyup=\"javascript:entervoucher(this.value);\"></td></tr>";
			echo "<tr><td><h2>Voucher Value</h2></td><td id=voucheramnt>&pound;</td><input type=hidden id=vouchamntconfirm /><input type=hidden id=vouchertype /></tr>";
			echo "<tr><td colspan=6></td></tr>";
			echo "<tr><td colspan=6><hr></td></tr>";
				
			echo "</table>";
			echo "</div>";
			echo "<div id=lookupdiv></div>";
		}
		

		echo "<table><tr>";
		$split_html="<table><tr>";
		$sql_query="select spendPot,payDescr, payid from TenderTypes where active=1 and paytype = 1";
		$results=$db_conn->query($sql_query);
		while ($type=mysqli_fetch_array($results))
		{	
			if ($type['spendPot']<>1)
			{
				echo "<td><img class=paymnts class=reduce onclick=\"javascript:cardType(".$type['payid'].", '".$type['payDescr']."','".$refund."' );\" src=\"./images/".$type['payDescr'].".png\" /></td>";
				$split_html.="<td class=paymnts onkeyup=\"javascript:updateSplit(".$outstanding.",'".$refund."');\" class=paymnts style=\"text-align:left;\">&pound;<input type=text value=\"0.00\" style=\"text-align:center;width:90px;\" id=pt".$type['payid']." /></td>";
			}
			else
			{
				echo "<td><img class=paymnts class=reduce onclick=\"javascript:spendType(".$type['payid'].", '".$type['payDescr']."','".$refund."' );\" src=\"./images/".$type['payDescr'].".png\" /></td>";
				$split_html.="<td id=txtpt".$type['payid']."  class=paymnts onkeyup=\"javascript:updateSplit(".$outstanding.",'".$refund."' );\" style=\"text-align:left;\" value=\"0.00\" >&pound;</td>
						<input type=hidden name=pt".$type['payid']." id=pt".$type['payid']." />";
			}
		}
		echo "</tr></table>";
		
		if ($refund==0)
		{
			$split_html.="<td><button id=splittot style=\"font-size:10pt;\" onclick=\"javascript:split_pay($outstanding, $refund);\" disabled>Enter<br>Amounts</button>
			<br><button id=canbut onclick=\"javascript:cancelDiag();\">Cancel</button><br><span style=font-size:9pt;>Outstanding</span><br><input style=width:55px;text-align:center; id=outamnt type=text value=$outstanding disabled></input></td></tr></table>";
			
		}
		elseif ($refund==1)
		{
			$split_html.="<td><button id=splittot  style=\"font-size:10pt;\" onclick=\"javascript:split_pay($outstanding, $refund);\" disabled>Enter<br>Refund</button>
			<br><button id=voucher onclick=\"javascript:creditNote();\" >Credit Note</button>
			<br><button id=canbut style=\"width:85px;\" onclick=\"javascript:cancelDiag();\">Cancel</button></td></tr></table>";
			//$split_html.="<td><input id=voucher type=checkbox onclick=\"javascript:creditNote();\" /><label style=\"font-size:10pt;\" for=\"voucher\">Produce Credit Note</label>";
		}
		
		echo "<div id=splitdiv>";		
		
		#Split payment 
		echo $split_html;
		echo "</div>";

		echo "<div id=creditNt style=\"display:none;\">";
		
		$sql_query="select forename, lastname from customers where custid =".$_SESSION['custref'];
		$customers=$db_conn->query($sql_query);
		$customer=mysqli_fetch_array($customers);
		
		$sql_query="select id, description from cn_reasons";
		$results=$db_conn->query($sql_query);
		echo "<h2>Credit note information</h2>";
		echo "<table><tr><th>Order Number</th><th>Customer Number</th><th>Amount</th><th>Reason</th><th></th></tr>";
		echo "<tr><td>".$_SESSION['orderno']."</td><td>".$customer['forename']." ".$customer['lastname']."</td><td>&pound;".$outstanding."</td><td>
			<select id=reason ><option value=0></option>";
		
		while ($reasons=mysqli_fetch_array($results))
		{
			echo "<option value=".$reasons['id'].">".$reasons['description']."</option>";
		}
			echo "</select></td>";
			echo "<td><button id=confirmC onclick=javascript:createC('$outstanding');>Confirm</button></td></tr>";
		echo "</table>";
		echo "</div>";
		
		echo "<div id=finish></div>";
		echo "<script type=text/javascript>$('button, #check, #appro, #voucher').button();</script>";
}

elseif ($action=="appro")
{
	$custref=getCustomer($_SESSION['orderno']);
	$tenderno=getMaxSpendTender()+1;
	$till=$_COOKIE['tillIdent'];
	$tillsession=getTillSession($till);
	$company=getTillCompany($till);

	if ($_REQUEST['depnote']>0)
	{
		for ($i=0;$i<15;$i++)
		{
			if ($_REQUEST['pt'.$i]>0)
			{
				#Only one payment type is allowed, so when we find it break out
				$sql_query="select payid, paytype from tendertypes where payid=$i";
				$results=$db_conn->query($sql_query);
				$result=mysqli_fetch_array($results);
				$i=20;
			}
		}
		#Create Deposit note
		$success=createSpendPot('D', 'create', $_REQUEST['depnote'], $custref['custid'], $_SESSION['orderno'], 'OnAppro Deposit Note', '', $tenderno);
		createRollEntry($custref['custid'], 'Created Depnote '.$success,'' ,'', 'A');
		printSpendPot($success);
		
		$sql_query="insert into spendPotTenders (company, till, till_session, PayMethod, PayType, PayValue, tenderNo)
		values ($company, '$till','$tillsession',".$result['payid'].",".$result['paytype']."
							,".$_REQUEST['depnote'].", $tenderno)";
		
		$insert_tender=$db_conn->query($sql_query);
	}
	
	#Find lines which need putting on appro
	$sql_query="select lineno from orderdetail where status = 'P' and transno =".$_SESSION['orderno'];
	$lines=$db_conn->query($sql_query);
	while ($line=mysqli_fetch_array($lines))
	{
		$onappro=appro($line['lineno']);
	}
	$sql_query="update orderheader set status = 'C' where transno = ".$_SESSION['orderno'];
	$results=$db_conn->query($sql_query);
	echo "<h2>Order placed on Approval</h2>";
	echo "<p>Print out receipt for customer signature</p>";
	if ($_REQUEST['depnote']>0)
	{
		echo "<p>Deposit of £".number_format($_REQUEST['depnote'],2)." has been taken</p>";
	}
	
	echo "<button style=\"font-size:9pt;\" onclick=\"receipt('print', ".$_SESSION['orderno'].")\">Print Receipt</button>";
	$custDetail=getCustomer($_SESSION['orderno']);
	if ($custDetail['email']<>"")
	{
		echo "<button style=\"font-size:9pt;\" onclick=\"receipt('email', ".$_SESSION['orderno'].")\">Email Receipt</button>";
	}
	echo "<button onclick=\"javascript:closeDiag();\">Close</button><br>";
	echo "<script type=text/javascript>receipt('print', ".$_SESSION['orderno'].");</script>";
	echo "<div id=receipt></div>";
	echo "<script type=text/javascript>$('button').button();</script>";
	
	//$_SESSION['orderno']="";
	//$_SESSION['custref']="";
	//$deauth=deauthenticate();
	updateReadout();
}

elseif ($action=="pay")
{
	$paymethod=$_REQUEST['id'];
	$till=$_COOKIE['tillIdent'];
	$tillsession=getTillSession($till);
	$company=getTillCompany($till);
	if ($_REQUEST['voucherid']=='undefined')
	{
		$_REQUEST['voucherid']="NULL";
	}
	$totals=bagTotals();
	if ($totals['discount']=="")
	{
		$totals['discount']=0;
	}
	$givechange=0;
	$opendrawer=0;
	$voucher=0;
	$types=array();
		#populate an array with the payids and values
		for ($i=1;$i<20;$i++)
		{
		
			if ($_REQUEST['pt'.$i]<>0)	
			{
				$sql_query="select payDescr,payid paymethod,givechange, opendrawer, payid, spendPot from TenderTypes where payid=$i";
				$results=$db_conn->query($sql_query);
				$result=mysqli_fetch_array($results);
				if ($_REQUEST['pt'.$i] > 0)
				{
					if ($result['givechange']==1)
					{
						$givechange=1;
					}
					$tenderamount+=$_REQUEST['pt'.$i];
					
					array_push($types,$i);
					$amounts[$i]=$_REQUEST['pt'.$i];
					$paytypes[$i]=$result['paymethod'];
				}
				if ($result['opendrawer']==1)
				{
					$opendrawer=1;
				}
				
				if ($result['spendPot']==1)
				{
					$voucher=1;
				}
				if ($_REQUEST['voucherid']=="")
				{
					$_REQUEST['voucherid']=0;
					$vouchers[$i]=0;
				}
				elseif ($_REQUEST['voucherid']<>"" && $result['spendPot']==1) {
					if ($vouchers[1]=="")
					{
						$vouchers=preg_split('/,/',$_REQUEST['voucherid']);
						$voucherstypes=preg_split('/,/',$_REQUEST['voucheridtype']);
					}
				}
				elseif ($_REQUEST['voucherid']<>"" && $result['spendPot']==0)
				{
					//$vouchers=0;
				}
			}
			
		}
	
		#Break up spendpots	
		$p=0;
		foreach ($vouchers as $voucherd)
		{

			if ($voucherstypes[$p]=="G")
			{
				$spendpotvalue=getSpendPot($voucherd,'G');
				$spamounts['8'][]=$spendpotvalue['amount'];
				$spnums['8'][]=$voucherd;
			}
			elseif($voucherstypes[$p]=="C")
			{
				$spendpotvalue=getSpendPot($voucherd,'C');
				$spamounts['9'][]=$spendpotvalue['amount'];
				$spnums['9'][]=$voucherd;
			}
			elseif($voucherstypes[$p]=="D")
			{
				$spendpotvalue=getSpendPot($voucherd,'D');
				$spamounts['10'][]=$spendpotvalue['amount'];
				$spnums['10'][]=$voucherd;
			}
			$p++;
		}

		foreach ($types as $type)
		{
			$changedue=0.00;
			if ($tenderamount>$_REQUEST['outstanding'])
			{
				$amounts['1']=$amounts['1']-($tenderamount-$_REQUEST['outstanding']);
				$changedue = ($tenderamount-$_REQUEST['outstanding']);
			}
			if ($paytypes[$type]=='1')
			{
				#Not a spend pot so set to 0
				$sql_query="insert into tenders (company, till, till_session, transno, PayMethod, PayType, PayValue, discount, spendPot, changedue)
				values ($company, '$till','$tillsession',".$_SESSION['orderno'].",".$type.",".$paytypes[$type]."
						,".$amounts[$type].",".$totals['discount'].",0,$changedue)";
				$insert_tender=$db_conn->query($sql_query);
			}
			elseif ($paytypes[$type]=='8' || $paytypes[$type]=='9' || $paytypes[$type]=='10')
			{
				for ($g=0;$g<10;$g++)
				{
				if ($spamounts[$type][$g]<>"")
				{
				$sql_query="insert into tenders (company, till, till_session, transno, PayMethod, PayType, PayValue, discount, spendPot)
				values ($company, '$till','$tillsession',".$_SESSION['orderno'].",".$type.",".$paytypes[$type]."
						,".$spamounts[$type][$g].",".$totals['discount'].",".$spnums[$type][$g].")";
								$insert_tender=$db_conn->query($sql_query);
				}
				else {$g=11;}
				}
			
			}
			else 
			{
				$sql_query="insert into tenders (company, till, till_session, transno, PayMethod, PayType, PayValue, discount, spendPot)
				values ($company, '$till','$tillsession',".$_SESSION['orderno'].",".$type.",".$paytypes[$type]."
						,".$amounts[$type].",".$totals['discount'].",0)";
				$insert_tender=$db_conn->query($sql_query);
				if (mysqli_error($db_conn)!="")
				{
				    echo "SQL failed. Error was ".mysqli_error($db_conn);
				}
			}


			if ($changedue==0.00)
			{
				#No change due, so must have given perfect cash
				createRollEntry($_SESSION['custref'], $paytypes[$type], 0, $amounts[$type], 'P');
			}
			else
			{
				createRollEntry($_SESSION['custref'], $paytypes[$type], 0, $tenderamount, 'P');
			}	
			
		}		
		
	if ($givechange==1)
	{	
		#Cash payment, do we need change?
		echo "<table>";
		echo "<tr><td>Change Due</td><td>£".number_format($changedue,2)."</td></tr>";
		echo "</table>";
		createRollEntry($_SESSION['custref'], 1, 0, $changedue, 'C');
	}
	
	echo "<div id=finishdetail >";
	
	#Close down any giftvouchers or credit notes
	if ($voucher==1)
	{	
		$vouchertypes=preg_split("/,/",$_REQUEST['voucheridtype']);
		$n=0;
		foreach (preg_split("/,/",$_REQUEST['voucherid']) as $voucherid)
		{
			#Is the voucher greater than the outstanding amount?
			$voucher=getSpendPot($voucherid,$vouchertypes[$n]);
			$cleardown=createSpendPot($voucher['type'], 'clear', NULL, $_SESSION['custref'], $_SESSION['orderno'], '', $voucherid,'');
			
			if ($totals['outstanding']<$voucher['amount'])
			{ 
				#Create new voucher for the remainder
				$overflow=($voucher['amount'] - $totals['outstanding']);
				#Create new voucher for the remainder
				$tenderNo=getMaxSpendTender()+1;

				$newvoucher=createSpendPot($voucher['type'],'create', $overflow, $_SESSION['custref'], $_SESSION['orderno'], 'Overflow', '',$tenderNo);
				#Adjust tender to the amount we actually tendered
				if ($voucher['type']=='G')
				{
					$tenderType=8;
				}
				elseif ($voucher['type']=='C')
				{
					$tenderType=9;
				}
				
				$sql_query="update tenders set payValue=".($voucher['amount']-$overflow)." where transno = ".$_SESSION['orderno']." and PayMethod = $tenderType";
				$doit=$db_conn->query($sql_query);
				printSpendPot($newvoucher);
			}
			$n++;
		}
	}
	echo "<button style=\"font-size:8pt;\" onclick=\"receipt('print', ".$_SESSION['orderno'].",$changedue)\">Re-print Receipt</button>";
	echo "<button onclick=\"receipt('gift', ".$_SESSION['orderno'].", $changedue)\">Gift Receipt</button>";
	
	echo "<script type=text/javascript>receipt('print', ".$_SESSION['orderno'].", $changedue);</script>";
	
	$custDetail=getCustomer($_SESSION['orderno']);
	if ($custDetail['email']<>"")
	{
		echo "<button onclick=\"receipt('email', ".$_SESSION['orderno'].")\">Email Receipt</button>";
	}
	echo "<button onclick=\"javascript:closeDiag();\">Close</button><br>";
	if ($opendrawer==1)
	{
		openDrawer(1);
	}
	
	$sql_query="update orderdetail set status='C' where transno = ".$_SESSION['orderno']." and status = 'P'";
	$result=$db_conn->query($sql_query);
	
	
	$paid=getTenderTotals();	
	$lines=bagTotals();
	$tillsession=getTillSession($_COOKIE['tillIdent']);
	$actualpaid=$paid['paid']-($paid['paid']/100*$lines['discount']);
	$actualnet=$paid['net']-($paid['net']/100*$lines['discount']);
	$actualvat=$paid['vat']-($paid['vat']/100*$lines['discount']);
	$sql_query="update orderheader set till_session=$tillsession,status='C', 
			till='".$_COOKIE['tillIdent']."', cashierid = '".$_SESSION['POS']."',
			grandTot = ".$actualpaid." , discount= ".$lines['discount']."
			, netTot = ".$actualnet." , vatTot = ".$actualvat." , transDate = now()
			where transno = ".$_SESSION['orderno'];
	$result=$db_conn->query($sql_query);
	
//	$_SESSION['orderno']="";
//	$_SESSION['custref']="";
//	$deauth=deauthenticate();
	updateReadout();
	clearReadout();
	echo "<div id=receipt></div>";
               echo "<script type=text/javascript>$('button').button();</script>";
     echo "</div>";
}

elseif ($action=="credit")
{
	if ($_REQUEST['voucherid']=='undefined')
	{
		$_REQUEST['voucherid']="NULL";
	}
	$paymethod=$_REQUEST['id'];
	$till=$_COOKIE['tillIdent'];
	$tillsession=getTillSession($till);
	$company=getTillCompany($till);
	$totals=bagTotals();
	$givechange=0;
	$opendrawer=0;
	$voucher=0;
	#Are we a split payment
	if ($_REQUEST['split']=='yes')
	{
		#populate an array with the payids and values
		for ($i=1;$i<20;$i++)
		{

		if ($_REQUEST['pt'.$i]<>0)
		{
		$sql_query="select payDescr,paytype,givechange, opendrawer, payid from TenderTypes where payid=$i";
		$results=$db_conn->query($sql_query);
		$result=mysqli_fetch_array($results);
		if ($result['givechange']==1)
		{
			$givechange=1;
			$tenderamount+=$_REQUEST['pt'.$i];
		}
		if ($result['opendrawer']==1)
			{
					$opendrawer=1;
		}

		if ($result['paytype']==2)
		{
			$voucher=1;
		}
		if ($_REQUEST['voucherid']=="")
		{
			$_REQUEST['voucherid']=0;
		}
		//$tenderamount=($totals['total']-(($totals['total']*$totals['discount']/100)));
			$sql_query="insert into tenders (company, till, till_session, transno, PayMethod, PayType, PayValue, discount, spendPot)
			values ($company, '$till','$tillsession',".$_SESSION['orderno'].",".$result['payid'].",".$result['paytype']."
			,".($_REQUEST['pt'.$i]*-1).",".($totals['discount']*-1).",".$_REQUEST['voucherid'].")";
			$insert_tender=$db_conn->query($sql_query);
			createRollEntry($_SESSION['custref'], $result['payDescr'], '', $_REQUEST['pt'.$i], '');
		}

		}
		if ($opendrawer==1)
		{
			openDrawer(0);
		}
	}

	else
	{
		$sql_query="select payDescr,paytype, spendPot, payid, givechange, opendrawer from TenderTypes where payid=$paymethod";
		$results=$db_conn->query($sql_query);
		$result=mysqli_fetch_array($results);


		if ($result['givechange']==1)
		{
			$givechange=1;
		}
		if ($result['opendrawer']==1)
		{
			$opendrawer=1;
		}
		if ($result['spendPot']==1)
		{
			$voucher=1;
		}
		if ($_REQUEST['voucherid']=="")
		{
			$_REQUEST['voucherid']=0;
		}
		$tenderamount=($totals['total']-(($totals['total']*$totals['discount']/100)));
				#Commit the tender
				$sql_query="insert into tenders (company, till, till_session, transno, PayMethod, PayType, PayValue, discount, spendPot)
				values ($company, '$till','$tillsession',".$_SESSION['orderno'].",".$result['payid'].",".$result['paytype']."
				,".(($totals['total']-(($totals['total']*$totals['discount']/100)))*-1).",".($totals['discount']*-1).",".$_REQUEST['voucherid'].")";
				
		$results=$db_conn->query($sql_query);
		createRollEntry($_SESSION['custref'], $result['payDescr'], '', $_REQUEST['pt'.$i], '');

		}

		if ($opendrawer==1)
		{
			openDrawer(0);
			createRollEntry('', 'Till Drawer Auto-open', '', '', '');
		}
		
		if ($givechange==1)
		{
		#Do we need change?
		echo "<table><tr><td>Cash Tender Due</td><td><input id=tenderdue value='$tenderamount' type=hidden>".number_format($tenderamount,2)."</td></tr>";
		echo "</table>";
		}

	echo "<div id=finishdetail ";
	if ($givechange==1)
	{
		echo "style=\"display:all;\">";

	}
	else
	{
	echo ">";
	}

	#Close down any giftvouchers
	if ($voucher==1)
	{
		$cleardown=createSpendPot('', 'clear', NULL, $_SESSION['custref'], $_SESSION['orderno'], '', $_REQUEST['voucherid']);

		#Is the voucher greater than the outstanding amount?
		$voucher=getSpendPot($_REQUEST['voucherid'],'G');

		if ($totals['outstanding']<$voucher['amount'])
		{
			#Create new voucher for the remainder
			$tenderNo=getMaxSpendTender()+1;
			$newvoucher=createSpendPot($voucher['type'],'create', ($voucher['amount'] - $totals['outstanding']), $_SESSION['custref'], $_SESSION['orderno'], 'Overflow', '',$tenderNo);
			echo "<h2>Credit note for balance of ".($voucher['amount'] - $totals['outstanding'])." will be printed</h2>";
		}
	}
	echo "<button onclick=\"receipt('print', ".$_SESSION['orderno'].")\">Re-print Receipt</button>";
	echo "<button onclick=\"receipt('gift', ".$_SESSION['orderno'].")\">Gift Receipt</button>";
	
	echo "<script type=text/javascript>receipt('print', ".$_SESSION['orderno'].", $changedue);</script>";
	$custDetail=getCustomer($_SESSION['orderno']);
	if ($custDetail['email']<>"")
	{
		echo "<button onclick=\"receipt('email', ".$_SESSION['orderno'].")\">Email Receipt</button>";
	}

	echo "<button onclick=\"javascript:closeDiag();\">Close</button>";
	
	$sql_query="update orderdetail set status='C' where transno = ".$_SESSION['orderno']." and status='P'";
	$result=$db_conn->query($sql_query);


	$paid=getTenderTotals();
	$lines=bagTotals();
	$actualpaid=$paid['paid']-($paid['paid']/100*$lines['discount']);
	$actualnet=$paid['net']-($paid['net']/100*$lines['discount']);
	$actualvat=$paid['vat']-($paid['vat']/100*$lines['discount']);
	$sql_query="update orderheader set status='C',
			grandTot = ".$actualpaid." , discount= ".$lines['discount']."
			, netTot = ".$actualnet." , vatTot = ".$actualvat."
					where transno = ".$_SESSION['orderno'];
	$result=$db_conn->query($sql_query);

//	$_SESSION['orderno']="";
//	$_SESSION['custref']="";
//	$deauth=deauthenticate();
	updateReadout();
	echo "<div id=receipt></div>";
	echo "<script type=text/javascript>$('button').button();</script>";
     echo "</div>";
}

elseif ($action=="creditnote")
{
	if ($_REQUEST['reprint']==1)
	{
		printSpendPot($_REQUEST['note']);
	}
	else 
	{
        $till=$_COOKIE['tillIdent'];
        $tillsession=getTillSession($till);

        $company=getTillCompany($till);
        $tenderNo=getMaxSpendTender()+1;
        #Remove the tender amount
        $sql_query="insert into spendPotTenders (company, till, till_session, transno, PayType, PayValue, tenderNo, PayMethod)
        values ($company, '$till','$tillsession',".$_SESSION['orderno'].",2
                        ,".$_REQUEST['amnt'].",$tenderNo, 9)";
        $doit=$db_conn->query($sql_query);
        
        if ($_REQUEST['reprint']<>1)
        {
                $success=createSpendPot('C', 'create', $_REQUEST['amnt']*-1, $_SESSION['custref'], $_SESSION['orderno'], $_REQUEST['reason'], '', $tenderNo);
        }
        else {

        }

        if ($success<>0)
        {
                printSpendPot($success);
        }
        

	
		$sql_query="update orderheader set status='C', grandTot = 0.00 , discount= 0.00
			, netTot = 0.00 , vatTot = 0.00
			where transno = ".$_SESSION['orderno'];
		$result=$db_conn->query($sql_query);
		createRollEntry($_SESSION['custref'], "Credit Note - $success", '', $_REQUEST['amnt'], 'C');
        echo "<script type=text/javascript>$('#canbut').prop('disable',true);</script><p width=100% align=right><button style=\"font-size:10pt;\" onclick=javascript:reprintC('".$success."');>Reprint Credit Note</button>
        		<button onclick=\"javascript:location.reload();\">Close</button></p>";
        
        $sql_query="update orderdetail set status='C' where transno = ".$_SESSION['orderno']." and status = 'P'";
        $result=$db_conn->query($sql_query);
        
        $_SESSION['orderno']="";
        $_SESSION['custref']="";
        $deauth=deauthenticate();
        updateReadout();
        
	}
}

echo "<div id=vouchers></div>";
echo "<div id=depnotediv></div>";
?>
<script type="text/javascript">

$(document).ready(function(){
	$('button').button();
	 $("#dialog-confirm").dialog({
	        autoOpen: false,
	        modal: true
	      });
     $('#vouchercode').focus();
     $('#splittot').prop('disabled','disabled');
});

function updateChange()
{
	var cash=$('#cashtaken').val();
	$('#changedue').val(cash - $('#pt1').val());
}
function closeDiag()
{
	$('#signin').load('./auth/login.php?action=logout');
}

function cancelDiag()
{
	location.reload();
}

function cardType(id,type, refund)
{
	$('#splittot').disable=false;
	if (id==8 || id==9 || id==10)
	{
		$('#vouchers').show();
		if (id==8)
		{
			$('#vouchers').load('./order/vouchersearch.php?type=gift');
		}
		if (id==9)
		{
			$('#vouchers').load('./order/vouchersearch.php?type=credit');
		}
		if (id==10)
		{
			$('#vouchers').load('./order/vouchersearch.php?type=deposit');
		}
		$('#vouchconfirm').button( "option", "disabled", false );
	}

	else if (id==1)
	{
		$('#pt1').focus();
		$('#pt1').val('');
		$('#pt1').val('0.00');
	}
	
	else
	{
		var total=0;
		var current=0;
		var get="";
		$('#splitdiv input[type=text], #splitdiv input[type=hidden]').each(function(){

			get=get+(this.name)+'='+(this.value)+'&';	
			if (this.id!="outamnt")
				{
					total=+(this.value)+total;
				}
		});
		var outstanding=Number($('#amntout').val());
		var rounded=Math.round((outstanding - total)*100)/100;
		var chk="";
//		var amnt=Number($('#amntout').val());
//		var amnt2=amnt.toFixed(2);
		var amnt2=rounded.toFixed(2);
		if (amnt2!=0)
		{
			$('#pt'+id).val(amnt2);
		}
		updateSplit(amnt2, refund);
	}
}

function spendType(id,type, refund)
{
	$('#splittot').disable=false;
	if (id==8 || id==9 || id==10)
	{
		$('#voucherform').slideDown('fast');
		$('#vouchercode').focus();
		if (id==8)
		{
			$('#vouchertype').val('G');
		}
		if (id==9)
		{
			$('#vouchertype').val('C');
		}
		if (id==10)
		{
			$('#vouchertype').val('D');
		}
		$('#vouchconfirm').button( "option", "disabled", false );
	}
}

$('#vouchsearch').click(function()
{
	var type=$('#vouchertype').val();
	$('#vouchers').show();
	$('#vouchers').load('./order/vouchersearch.php?type='+type);
});

function receipt(type, orderno, changedue)
{
	$('#receipt').load('./order/receipt.php?type='+type+'&orderno='+orderno+'&changedue='+changedue);
}

function split_pay(outstanding, refund)
{
	var get="split=yes&";
	var total=0;
	var current=0;
	if ($('#depnoteval').val()>0)
	{
		onAppro();
	}
	else 
	{
		$('#splitdiv input[type=text], #splitdiv input[type=hidden]').each(function(){
			get=get+(this.id)+'='+(this.value)+'&';	
			total=+(this.value)+total;
		});
		get=get+'voucherid='+$('#voucherid').val();
		get=get+'&voucheridtype='+$('#voucheridtype').val();
		var rounded=Math.round(total*100)/100;
	    if (refund==0)
	   	{
	   		$('#finish').load('./order/processOrder.php?action=pay&outstanding='+outstanding+'&'+get);
	   	}
	   	else
	   	{
	    	$('#finish').load('./order/processOrder.php?action=credit&outstanding='+outstanding+'&'+get);
	    }
	
	    //Now input inhibit stuff
		$('#splitdiv input[type=text]').each(function(){
			$(this).prop('disabled',true);
		});
		
		$( "#splittot" ).button({
			  disabled: true
			});
	
		$('#canbut').button({
			disabled: true
		});
	}
}

function updateSplit(outstanding,refund)
{
	var total=0;
	var current=0;
	var get="";
	$("#splitdiv input[type=text]:not('#outamnt'), #splitdiv input[type=hidden]").each(function(){
              $(this).priceFormat({
                        prefix: '',
                         thousandsSeparator: ''
                });

		get=get+(this.name)+'='+(this.value)+'&';	
		total=+(this.value)+total;
	});
	var rounded=Math.round((outstanding - total)*100)/100;

	if (rounded <= 0)
	{
		if (refund==0)
		{	
			var buttext="Confirm<br>Amounts";
			$('#splittot').button({
				disabled: false
			});
			$('#outamnt').val('0.00');
		}
		if (refund==1)
		{	
			var buttext="Confirm<br>Refund";
			$('#voucher').button({
				disabled: true
			});
			$('#splittot').button({
				disabled: false
			});
		}
	}
	else
	{
		$('#splittot').button({
			disabled: true
		});
		$('#outamnt').val(rounded);
	}
	$('#splittot span').html(buttext);	
}

function onAppro()
{
	var get="";
	$('#appro').button({
		disabled: true
	});
	$('#canbut').button({
		disabled: true
	});
	var depnoteval=$('#depnoteval').val();
	$('#splitdiv input[type=text], #splitdiv input[type=hidden]').each(function(){
		get=get+(this.id)+'='+(this.value)+'&';	
	});
	$('#finish').load('./order/processOrder.php?action=appro&depnote='+depnoteval+'&'+get);
}

function voucher()
{
	$('#voucherform').slideToggle('fast');
}

function entervoucher(id,type='')
{
	if (type=='')
	{
		var type=$('#vouchertype').val();
	}
	var vlen=id.length;
	if (vlen == 8)
	{
		$('#voucheramnt').load('./order/voucher.php?action=amnt&id='+id+'&type='+type);
		$('#vouchertype').load('./order/voucher.php?action=type&id='+id+'&type='+type);
	}
	else
	{
		$('#voucheramnt').text("");	
	}
}

function creditNote()
{
	$('#creditNt').slideToggle('fast');
}

function reprintC(note)
{
	$('#finish').load('./order/processOrder.php?action=creditnote&note='+note+'&reprint=1');
}

$('#vouchconfirm').click(function(){
	var pt8=($('#pt8').val())*100;
	var pt9=($('#pt9').val())*100;
	var pt10=($('#pt10').val())*100;
	var voucheramnttxt=$('#voucheramnt').text();
	var vouchlen=voucheramnttxt.length;
	var voucheramnt=voucheramnttxt.substring(2,vouchlen)*100;
	
	var voucherid=$('#voucherid').val();
	var voucheridtype=$('#voucheridtype').val();
	if (voucherid!="")
	{
		voucherid=voucherid+",";
		voucheridtype=voucheridtype+",";
	}
	if ($('#voucheramnt').text() != 'Used')
	{
		
		if ($('#vouchertype').text().trim()=='G')
		{
			$('#pt8').val(+pt8 + +(voucheramnt));
			$('#txtpt8').html('&pound; '+($('#pt8').val())/100);
		}
		else if($('#vouchertype').text().trim()=='C')
		{
			$('#pt9').val(+pt9 + +(voucheramnt));
			$('#txtpt9').html('&pound; '+($('#pt9').val())/100);
		}
		else
		{
			$('#pt10').val(+pt10 + +(voucheramnt));
			$('#txtpt10').html('&pound; '+($('#pt10').val())/100);
		}
		var outstanding=$('#amntout').val();
		updateSplit(outstanding,0);
		$('#voucherid').val(voucherid+$('#vouchercode').val());
		$('#voucheridtype').val(voucheridtype+$('#vouchertype').val());
		$('#vouchconfirm').button({
			disabled: true
		});
	}
});



$('#tender').click(function(){
	var taken=$('#tendered').val();
	var due=$('#tenderdue').val();
	var chg=+(due)-+(taken);
	var change=Math.round(chg*100)/100;
	$('#finishdetail').show();
});

function createC(amnt){
	var reason=encodeURIComponent($('#reason').val());
	$('#confirmC').button({
		disabled: true
	});
	$('#confirmC').html('Confirmed');
	$('#voucher').button({
		disabled: true
	});
	$('#canbut').button({
		disabled: true
	});
	$('#finish').load('./order/processOrder.php?action=creditnote&amnt='+amnt+'&reason='+reason);
};

function tendered(amnt)
{
	$('#splittot').disable(false);
	$('#tenderchange').priceFormat({
	    prefix: '',
	     thousandsSeparator: ''
	});
	var amnt2=$('#pt1').val();
	var due=$('#tenderdue').val();
	var chg=+((due)-+(amnt2))*-1;
	var chg2=Math.round(chg*100)/100;
	var change=chg2.toFixed(2);
	$('#tenderchange').val(change);
}

function showSplit()
{
	$('#splitdiv').slideToggle('fast');
}

function depNote()
{
	$('#splitdiv input[type=text]').each(function(){
		$(this).prop('disabled','true');
	});
	$('#depnotediv').load('./order/depnote.php');
	$('#depnotediv').show();
}

function nodepNote()
{
        $('#message p').append( "Not available - no Mobile on Customer record" );
        $('#message').show();

}

function noAppro()
{
        $('#message p').append( "Not available - no Mobile on Customer record" );
	$('#message').show();

}
</script>
