<?php
include '../config.php';
include '../functions/auth_func.php';
include '../functions/print_func.php';

session_start();
$auth=check_auth();
if ($auth<>1)
{
	exit();
}
ob_start();
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$till=$_COOKIE['tillIdent'];
$company=getTillCompany($till);

$tillsession=getTillSession($till);
$action=$_REQUEST['action'];
if ($action=="print")
{
	$sql_query="select tills.nicename, till_sessions.session_date, till_sessions.session_number from tills, till_sessions where tills.tillname= till_sessions.till and till_sessions.session_date > (now() - interval 30 day) and company=$company order by till_sessions.session_number desc";	
	$results=$db_conn->query($sql_query);
	
	echo "<table  width=100%>";
	echo "<tr><th>Till</th><th>Session Number</th><th>Date</th></tr>";
	while ($session=mysqli_fetch_array($results))
	{
		echo "<tr onclick=\"javascript:loadSession(".$session['session_number'].");\"><td>".$session['nicename']."</td><td>".$session['session_number']."</td><td>".$session['session_date']."</td></tr>";

	}
	echo "</table>";

}
if ($action=="reprint")
{
	exec('lp -h '.$receipt_host.' -d '.$printer.' '.$receipt_tmp.'/printing.pdf');
	echo "<script>location.reload();</script>";
	exit();
}

if ($action=="load")
{
	$sql_query="select till_sessions.till, tills.company from tills, till_sessions where tills.tillname=till_sessions.till  and company=$company";
	$results=$db_conn->query($sql_query);

	$session=mysqli_fetch_array($results);

	$till=$session['till'];
	$tillsession=$_REQUEST['session'];
	

	$action="read";
}
if ($action=="read")
{
	echo "<table width=100% class=receipt>";

	$conum=getTillCompany($_COOKIE['tillIdent']);
	$sql_query="select nicename from companies where conum = $conum";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	
	echo "<tr><td class=left colspan=3>End of Day Report</td></tr>";
	echo "<tr><td class=left>Company: </td><td  colspan=2 align=right>".$result['nicename']."</td></tr>";
	
	$today_date=date('d/m/Y');
	$today_time=date('H:i');
	$sql_query="select nicename from tills where tillname= '".$_COOKIE['tillIdent']."'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	echo "<tr><td class=left>Till: ".$result['nicename']."</td><td colspan=2 align=right>Date: $today_date</td></tr>";
	echo "<tr><td class=left>User: ".$_SESSION['POS']."</td><td colspan=2 align=right>Time: $today_time</td></tr>";
	echo "<tr><td colspan=3>----------------------------------------</td></tr>";
	echo "<tr><th colspan=3>Sales</th></tr>";


#Sales Value

$all_tills="select tills.nicename, till_sessions.till, till_sessions.session_number, min(till_sessions.session_date) session_date from tills, till_sessions 
where till_sessions.company=$company and active=1 and tills.tillname = till_sessions.till group by 1,2,3";
$tills=$db_conn->query($all_tills);
while ($onetill=mysqli_fetch_array($tills))
{
    if ($till_id<>"")
    {
        $till_id.=",";
        $till_session.=",";
    }
    $till_id.="'".$onetill['till']."'";
    $till_session.="'".$onetill['session_number']."'";
    if ($onetill['session_date']<$till_date || $till_date=="")
    {
        $till_date=$onetill['session_date'];
    }
}
	#Echo out Value of Sales
	echo "<tr><td class=left>Till Summary</td><td></td><td></td></tr>";
	
	$sql_query="select 'Sales' type, st.vatkey vat,sum(if(od.zero_price=1,0, (if (abs(od.actualgrand)>0, od.actualgrand*od.qty, od.grandTot*od.qty)))) total
			,sum(if(od.zero_price=1,0, (if (abs(od.actualvat)>0, od.actualvat*od.qty, od.vatTot*od.qty)))) vattotal
			, count(*) cnt 
			from orderdetail od, orderheader oh, style st
            where od.transno = oh.transno and st.sku = od.Stockref 
            and oh.company =$company and till in (".$till_id.") 
			and (if (abs(od.actualgrand)>0, od.actualgrand, od.grandTot)) >= 0 and od.status not in ('A','X','P','N') 
            and od.timestamp >= '$till_date' and od.timestamp < date_add(date_add(current_date(), interval 1 day), interval 6 hour) group by 1,2
	union select 'Returns' type , st.vatkey vat,sum(if(od.zero_price=1,0,if (abs(od.actualgrand)>0, od.actualgrand*abs(od.qty), od.grandTot*abs(od.qty)))) total
			, sum(if(od.zero_price=1,0,if (abs(od.actualvat)>0, od.actualvat*abs(od.qty), od.vatTot*abs(od.qty)))) vattotal
			, count(*) cnt 
			from orderdetail od, orderheader oh, style st
            where od.transno = oh.transno and st.sku = od.Stockref 
            and oh.company =$company and till in (".$till_id.") 
			and (if (abs(od.actualgrand)>0, od.actualgrand, od.grandTot)) < 0 
            and od.timestamp >= '$till_date' and od.timestamp < date_add(date_add(current_date(), interval 1 day), interval 6 hour)
            group by 1,2";
	
	$results=$db_conn->query($sql_query);
	unset($summary);
	$summary=array();
	while ($row = mysqli_fetch_array($results)) {
    	$summary[$row['vat']][$row['type']."tot"] = $row['total'];
    	$summary[$row['vat']][$row['type']."vat"] = $row['vattotal'];
    	$summary[$row['vat']][$row['type']."cnt"] = $row['cnt'];
	}
	$vats=array(1,2,3,4);
	foreach ($vats as $vat)
	{
	    $valueofsales+=$summary[$vat]['Salestot']+$summary[$vat]['Returnstot'];
	    $valueofvat+=$summary[$vat]['Salesvat']+$summary[$vat]['Returnsvat'];
	    $salesonly+=$summary[$vat]['Salestot'];
	    $salescnt+=$summary[$vat]['Salescnt'];
	    $returnsonly+=$summary[$vat]['Returnstot'];
	    $returnscnt+=$summary[$vat]['Returnscnt'];
	}

	echo "<tr><td class=left>Sales</td><td align=right>".$salescnt."<td align=right>&pound;".number_format($salesonly,2)."</td></tr>";
	echo "<tr><td class=left>Returns</td><td align=right>".$returnscnt."<td align=right>&pound;".number_format($returnsonly,2)."</td></tr>";

	echo "<tr><td colspan=3>----------------------------------------</td></tr>";
	echo "<tr><td class=left><b>Value Of Goods</b></td><td></td><td align=right><b>&pound;".number_format($valueofsales,2)."</b></td></tr>";
	echo "<tr><td colspan=3>----------------------------------------</td></tr>";
	echo "<tr><td class=left> </td><td></td></tr>";
	echo "<tr><td class=left> </td><td></td></tr>";
	echo "<tr><td class=left> </td><td></td></tr>";
	echo "<tr><td class=left> </td><td></td></tr>";

	#SpendPots
	$sql_query=" 
	select t2.type, issued.total, issued.cnt
	from
	(select sp.type,abs(sum(PayValue)) total, count(*) cnt
			from spendPotTenders spt, spendPots sp 
			where 1=1
			and sp.tenderNo = spt.tenderNo
			and company =$company and till in (".$till_id.")
			and till_session in (".$till_session.")  group by 1) issued 
	        right join (select distinct(sp.type) type from spendpots sp) t2 on t2.type=issued.type
	;";	
	$results=$db_conn->query($sql_query);

	$summarysp=array();
	while ($row = mysqli_fetch_array($results)) {
    	$summarysp[$row['type']."tot"] = $row['total'];
    	$summarysp[$row['type']."cnt"] = $row['cnt'];
	}
	echo "<tr><td class=left>G/V Issued</td><td align=right>".$summarysp['Gcnt']."</td><td align=right>&pound;".number_format($summarysp['Gtot'],2)."</td></tr>";
	echo "<tr><td class=left>C/N Issued</td><td align=right>".$summarysp['Ccnt']."</td><td align=right>&pound;".number_format($summarysp['Ctot'],2)."</td></tr>";
	echo "<tr><td class=left>D/N Issued</td><td align=right>".$summarysp['Dcnt']."</td><td align=right>&pound;".number_format($summarysp['Dtot'],2)."</td></tr>";
	
	$valueofpots+=$summarysp['Gtot']+$summarysp['Ctot']+$summarysp['Dtot'];
	echo "<tr><td class=left><b>TOTAL</b></td><td></td><td align=right><b>&pound;".number_format($valueofpots+$valueofsales,2)."</b></td></tr>";
	echo "<tr><td colspan=3>----------------------------------------</td></tr>";

	
	echo "<tr><td colspan=3></td></tr>";
	#Show OnAppro for the day
	$sql_query="select c.forename, c.lastname
			, sum(if (od.actualgrand>0, od.actualgrand*od.qty, od.grandTot*od.qty)) amnt
    		, count(*) qty
			from customers c, orderdetail od, orderheader oh
			where od.transno = oh.transno and oh.company =1 and c.custid = oh.custref
			and oh.company = $company and oh.till in (".$till_id.") and oh.till_session in (".$till_session.")
            and od.timestamp >= '$till_date'
			and od.status ='A' 
			group by c.custid ";
	$results=$db_conn->query($sql_query);
	$rownum=mysqli_affected_rows($db_conn);
	
	if ($rownum>0)
	{
		echo "<tr><th colspan=3>OnAppro Approved</th></tr>";
		while ($result=mysqli_fetch_array($results))
		{
			echo "<tr><td class=left>".$result['forename']." ".$result['lastname']."</td><td align=right>".$result['qty']."<td align=right>&pound;".$result['amnt']."</td></tr>";
		}
	}
	#Get pettycash from tills
	$sql_query="select pettycashtype.Descr descr, pettycash.transamnt amount from pettycash, pettycashtype where pettycash.transtype=pettycashtype.typeid 
    and till in (".$till_id.") and tillsession in (".$till_session.")";
	$results=$db_conn->query($sql_query);
	$totalpcashin=0;
	$totalpcashout=0;
	$pcashcntin=0;
	$pcashcntout=0;
	while ($pcash=mysqli_fetch_array($results))
	{
		if ($pcash['amount']>0)
		{
			$totalpcashin+=$pcash['amount'];
			$pcashcntin++;
		}
		else
		{
			$totalpcashout-=$pcash['amount'];
			$pcashcntout++;
		}
		
	}
	echo "<tr><td class=left> </td><td></td></tr>";
	echo "<tr><td class=left> </td><td></td></tr>";
	echo "<tr><td class=left>Petty Cash Out</td><td align=right>$pcashcntout</td><td align=right>&pound;".number_format($totalpcashout,2)."</td></tr>";
	echo "<tr><td class=left>Petty Cash In</td><td align=right>$pcashcntin</td><td align=right>&pound;".number_format($totalpcashin,2)."</td></tr>";
	
	
	#Takings breakdown
	$sql_query="select descr, sum(value) value, sum(cnt) cnt from
                        (select TenderTypes.payDescr descr, sum(tenders.PayValue) value, count(*) cnt
from TenderTypes,  tenders
        where tenders.payMethod = TenderTypes.payId
        and tenders.till in (".$till_id.") and tenders.company=$company and tenders.till_session in (".$till_session.") group by TenderTypes.payDescr
union
select TenderTypes.payDescr descr, sum(tenders.PayValue) value, count(*) cnt
from TenderTypes,  spendpottenders tenders
        where tenders.payMethod = TenderTypes.payId
        and tenders.PayValue > 0
        and tenders.till in (".$till_id.") and tenders.company=$company and tenders.till_session in (".$till_session.") group by TenderTypes.payDescr
) theunion
group by 1";
	$results=$db_conn->query($sql_query);
	
	echo "<tr><th colspan=3>Takings</th></tr>";
	while ($detail=mysqli_fetch_array($results))
	{
		echo "<tr><td class=left>".$detail['descr']."</td><td align=right>".$detail['cnt']."</td><td align=right>&pound;".$detail['value']."</td></tr>";
		if ($detail['descr']=='Cash')
		{
			$cash=$detail['value'];
		}
		$totaltakings+=$detail['value'];
		$cnttakings+=$detail['cnt'];
	}
	
	$sql_query="select 'Discounts' type, sum(od.grandtot-od.actualgrand) total, count(*) cnt from orderdetail od, orderheader oh where oh.company = $company 
    and oh.till in (".$till_id.")  and oh.till_session in (".$till_session.") and od.transno = oh.transno and od.status = 'C' and od.actualgrand is not null group by 1";
	$summaries2=$db_conn->query($sql_query);
	$summary2=mysqli_fetch_array($summaries2);
	echo "<tr><td class=left><b>TOTAL</b></td><td></td><td align=right><b>&pound;".number_format($totaltakings,2)."</b></td></tr>";
	
	echo "<tr><td colspan=3><br></td></tr>";
	echo "<tr><td class=left><b>".$summary2['type']."</b></td><td align=right><b>".$summary2['cnt']."</b><td align=right><b>&pound;".$summary2['total']."</b></td></tr>";
	echo "<tr><td class=left><b>Transactions</b></td><td></td><td align=right><b>$cnttakings</b></td></tr>";
	echo "<tr><td class=left><b>Avg Trans Value</b></td><td></td><td align=right><b>".round(($totaltakings/$cnttakings),2)."</b></td></tr>";
	$pettycash=getPettyCash('192.168.1.2');
	echo "<tr><td class=left><b>Float Amount</b></td><td></td><td align=right><b>&pound;".number_format($pettycash['closeval'],2)."</b></td></tr>";
	


echo "</table><br><br>";

}

#VAT Summary
#Get VAT rate
$sql_query="select rate, vatkey, nicename from vatrates where active=1";
$results=$db_conn->query($sql_query);

echo "<table class=receipt width=100%><tr><td colspan=3>----------------------------------------</td></tr>";
echo "<tr><td align=right>%Rate</td><td align=right>Gross</td><td align=right>Net</td><td align=right>Vat</td></tr>";
while ($vatrate=mysqli_fetch_array($results))
{
    $valueofsales=0;
    $valueofvat=0;
    $valueofsales+=$summary[$vatrate['vatkey']]['Salestot']+$summary[$vatrate['vatkey']]['Returnstot'];
    $valueofvat+=$summary[$vatrate['vatkey']]['Salesvat']+$summary[$vatrate['vatkey']]['Returnsvat'];
    echo "<tr><td align=right>".round($vatrate['rate'],0)."%</td><td align=right>&pound;".number_format($valueofsales,2)."</td><td align=right>&pound;".number_format(($valueofsales-$valueofvat),2)."</td>
			<td align=right>&pound;".number_format($valueofvat,2)."</td></tr>";
}
echo "<tr><td colspan=3>----------------------------------------</td></tr>";
echo "</table>";
echo "</body></html>";
$html2=ob_get_clean();
ob_end_clean();

if ($_REQUEST['actionfinal']=="print")
{
//	#close all till sessions for the day
	$sql_query="update till_sessions set active=0 where active=1 and company=".$company;
	$doit=$db_conn->query($sql_query);
	$sql_query="delete from sessions where company = $company";
	$doit=$db_conn->query($sql_query);
	openDrawer(0);
	$EODID=getConfig('EODID-'.$company)+1;
	$sql_query="update config set value = $EODID where config='EODID-".$company."'";
	$doit=$db_conn->query($sql_query);
	$html=generic_header(0);
	$html.=$html2;
	echo $html;
	print_action($html,$receipt_printer,'false');
	session_destroy();
	deauthenticate();
	unset($_SESSION);
	echo "<script>javascript:location.reload();</script>";
	exit();
}


else { echo $html2;}

echo "<p width=100% align=right><button id=cancel onclick=\"javascript:location.reload();\">Cancel</button>
		<button id=print onclick=\"javascript:printSession();\">Print</button>
		<button id=reprint disabled onclick=\"javascript:reprintpdf();\">Re-Print</button></p>";

?>
<script type=text/javascript>
$(document).ready(function(){
	$('button').button();
});

function loadSession(session)
{
	$('#temp').load('./report/eod.php?action=load&session='+session);
}

function printSession()
{


	$('#dialog-confirm').html('<div id=temp>Are your figures correct?<br>Confirming this report cannot be reversed</div>');
    $("#dialog-confirm" ).dialog({
        resizable: false,
        height:250,
        autoOpen: true,
        modal: true,
        title: "End of Day",
        buttons: {
          "OK": function() {
        	fnalfloat=$('#bankcash').text();
        	$('#dialog-confirm').load('./report/eod.php?action=read&actionfinal=print');
            $( this ).dialog( "close" );
            $('#cancel').text('Close');
        	$('#reprint').button({
        		disabled: false
        	});
        	$('#print').button({
        		disabled: true
        	});
          },
          "Cancel": function(){
        	location.reload();
          },
        }
 	});
}

function reprintpdf()
{
	$('#temp').load('./report/eod.php?action=reprint');
}

</script>
