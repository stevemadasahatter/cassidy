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

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$till=$_COOKIE['tillIdent'];
$company=getTillCompany($till);
$tillsession=getTillSession($till);
$action=$_REQUEST['action'];
$type=$_REQUEST['type'];
$date=date('d/m/Y');
$time=date('H:i');

if ($action=="read")
{
	ob_start();

	echo "<table width=100%>";
	
	$conum=getTillCompany($_COOKIE['tillIdent']);
	$sql_query="select nicename from companies where conum = $conum";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	$today_date=date('d/m/Y');
	$today_time=date('H:i');
	
	echo "<tr><td class=left colspan=2>Banking Report</td></tr>";
	echo "<tr><td class=left>Company: </td><td align=right>".$result['nicename']."</td></tr>";
	echo "<tr><td class=left></td><td align=right>Date: $today_date</td></tr>";
	echo "<tr><td class=left>User: ".$_SESSION['POS']."</td><td align=right>Time: $today_time</td></tr>";
	echo "<tr><td colspan=2>----------------------------------------</td></tr></table>";
	
	echo "<table width=100%>";
	
	#Takings breakdown
	$sql_query="select tt.payId, tt.payDescr descr, sum(theunion.cnt) cnt, sum(theunion.value) value from
					(select TenderTypes.payDescr descr, TenderTypes.payId payId, sum(tenders.PayValue) value, count(*) cnt from tenders, TenderTypes, till_sessions ts
					where tenders.payMethod = TenderTypes.payId
					and tenders.till=ts.till and tenders.company=$company and tenders.till_session=ts.session_number
					and ts.active=1
					group by TenderTypes.payId,TenderTypes.payDescr
					union
					select TenderTypes.payDescr descr, TenderTypes.payId payId, abs(sum(spendpottenders.PayValue)) value
                    , sum( IF(spendpots.reason = 'Overflow', 0, 1))  cnt from spendpottenders, spendpots, TenderTypes, till_sessions ts
					where spendpottenders.payMethod = TenderTypes.payId
					and spendpottenders.tenderno = spendpots.tenderno
					and spendpottenders.till=ts.till and spendpottenders.company=$company and spendpottenders.till_session=ts.session_number
					and ts.active=1
					and spendpots.type in ('G','D','C')
                    	and spendpottenders.PayValue > 0
					group by TenderTypes.payId,TenderTypes.payDescr
					) theunion
                    right join 
                    (select payId, payDescr from Tendertypes
                    where payId in (1,2,3,4,5,6,7,8,9,10)) tt on tt.payId = theunion.payId
					group by tt.payId, tt.payDescr
";
	$results=$db_conn->query($sql_query);
	
	$detail_html="";
	while ($detail=mysqli_fetch_array($results))
	{
		$detail_html.="<tr><td>".$detail['descr']."</td><td  class=bankmoney>".$detail['cnt']."</td><td class=bankmoney>&pound;".number_format($detail['value'],2)."___</td></tr>";
		if ($detail['descr']=='Cash')
		{
			$cash=$detail['value'];
			$cashcnt=$detail['cnt'];
		}
	
		$totaltakings+=$detail['value'];
	}
	
	$detail_html.="<tr><td><b>TOTAL</b></td><td></td><td class=bankmoney><b>&pound;".number_format($totaltakings,2)."</b>&nbsp;&nbsp;&nbsp;</td></tr>";
	
	#Get pettycash from tills
	$sql_query="select pettycashtype.Descr descr, pettycash.transamnt amount from pettycash, pettycashtype, till_sessions 
where 1=1 and till_sessions.active=1 and till_sessions.session_number=pettycash.tillsession 
and pettycash.transtype=pettycashtype.typeid";
	
			$results=$db_conn->query($sql_query);
			$totalpcashin=0;
			$totalpcashout=0;
			$totalpcashincnt=0;
			$totalpcashoutcnt=0;
	
			while ($pcash=mysqli_fetch_array($results))
			{
				if ($pcash['amount']>0)
				{
					$totalpcashin+=$pcash['amount'];
					$totalpcashincnt++;
				}
				else
				{
					$totalpcashout-=$pcash['amount'];
					$totalpcashoutcnt++;
				}
			}
	
	#handle cash
	$pettycashStart=getPettyCash($till);
//	echo "<table width=100%>";
	echo "<tr><td>Banking</td><td></td></tr>";
	echo "<tr><td>Cash Taken</td><td class=bankmoney>$cashcnt</td><td class=bankmoney>&pound;".number_format($cash,2)."</td></tr>";
	echo "<tr><td>Petty Cash Out</td><td class=bankmoney>$totalpcashoutcnt</td><td class=bankmoney>&pound;".number_format($totalpcashout,2)."</td></tr>";
	echo "<tr><td>Petty Cash In</td><td class=bankmoney>$totalpcashincnt</td><td class=bankmoney>&pound;".number_format($totalpcashin,2)."</td></tr>";
	echo "<tr><td><b>TOTAL</b></td><td></td><td class=bankmoney>&pound;".number_format($cash-$totalpcashout+$totalpcashin,2)."</td></tr>";
	echo "<tr><td colspan=3><hr></td></tr>";
	echo "<tr><td>Starting Float</td><td></td><td class=bankmoney>&pound;".number_format($pettycashStart['startval'],2)."</td></tr>";
	
	if ($type<>"print")
	{
		echo "<tr style=\"font-weight:bold;\"><td>Closing float</td><td></td><td class=bankmoney>&pound;<span id=bankcash>".number_format(($pettycashStart['startval']+$cash-$totalpcashout-$totalpcashin),2)."</span></td></tr>";
		echo "<tr style=\"font-weight:bold;\"><td>Total Cash to bank</td><td></td><td class=bankmoney>&pound;<input onkeyup=\"javascript:updatetot();\" style=\"width:70px;\" type=text id=banking value=".number_format(0.00,2)."></input></td></tr>";
	}
	else 
	{
		echo "<tr style=\"font-weight:bold;\"><td>Closing float</td><td></td><td class=bankmoney>&pound;<span id=bankcash>".number_format(($pettycashStart['startval'])+$cash-$totalpcashout+$totalpcashin-$_REQUEST['bank'],2)."</span></td></tr>";
		echo "<tr style=\"font-weight:bold;\"><td>Total Cash to bank</td><td></td><td class=bankmoney>&pound;".number_format($_REQUEST['bank'],2)."</td></tr>";
	}
	echo "<input type=hidden id=starting value=".($cash-$totalpcashout+$totalpcashin)."></input>";
	echo "<input type=hidden id=cash value=".$pettycashStart['startval']."></input>";
	echo "</table>";	
		
	echo "<table width=100%>";
	echo "<tr><td colspan=3><hr></td></tr>";
	echo $detail_html;
	echo "</table>";

	
	if ($type<>"print")
	{
		echo "<div id=but><p width=100% align=right><button id=cancelbut>Cancel</button><button id=print>Print</button></p></div>";
	}
	
	$html=ob_get_clean();
	ob_end_clean();
	
	if ($type=="print")
	{
		$html2=generic_header(0);
		$html2.=$html;
		print_action($html2,$receipt_printer, 'false');
		$EODID=getConfig('EODID-'.$company);
		$sql_query="update tilldrawer set closeval=".($pettycashStart['startval']+$cash-$totalpcashout+$totalpcashin-$_REQUEST['bank'])." where EODID=$EODID";
		echo $sql_query;
		$doit=$db_conn->query($sql_query);
		deauthenticate();
		echo "<script type=text/javascript>location.reload();</script>";
	}
	else
	{
		echo $html;
	}
}


echo "<div id=dialog-confirm></div>";

?>
<script type=text/javascript>

$(document).ready(function(){
		$('button').button();
});

function loadSession(session)
{
	$('#temp').load('./report/banking.php?action=load&session='+session);
}

function updatetot()
{
	value=$('#banking').val();
	start=$('#starting').val();
	cash=$('#cash').val();
	flat=+cash+(start-value);
	flt=flat.toFixed(2);
	$('#bankcash').text(flt);
	$('#bankcash').val(flt);
}


$('#cancelbut').click(function(){
	$('#temp').remove();
	$('#dimmer').hide();
	$('#dialog').hide();
	window.open('./pos.php?action=deauth', '_self',false);
});

$('#print').click(function(){
	var bank=$('#banking').val();
	$('#temp').load('./report/banking.php?action=read&type=print&bank='+bank);
});

function print(){
	var bank=$('#banking').val();
	$('#temp').load('./report/banking.php?action=read&type=print&bank='+bank);
};

</script>
