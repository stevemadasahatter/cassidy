<?php

include "../config.php";
include "../functions/auth_func.php";
include "../functions/print_func.php";

$db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);

if ($_REQUEST['action']=="")
{
	echo "<h2>Gift Voucher Purchase</h2>";

	echo "<table>";
	echo "<tr><th>Voucher Value</th></tr>";
	echo "<tr><td><input type=text id=amnt value=></input></td></tr>";
	echo "</table>";

		echo "<br><h2>Payment Options</h2>";

		echo "<table><tr>";
		$split_html="<table><tr>";
		$sql_query="select spendPot,payDescr, payid from TenderTypes where active=1 and paytype = 1";
		$results=$db_conn->query($sql_query);
		while ($type=mysqli_fetch_array($results))
		{
			echo "<td><img width=100 onclick=\"javascript:cardType(".$type['payid'].", '".$type['payDescr']."','".$refund."' );\" src=\"./images/".$type['payDescr'].".png\" /></td>";
			if ($type['spendPot']<>1)
			{	
				$split_html.="<td onkeyup=\"javascript:updateSplit(".$outstanding.");\" style=\"width:101px;text-align:left;\">&pound;<input type=text value=\"0.00\" style=\"width:70px;text-align:center;\" id=pt".$type['payid']." /></td>";
			}
			else
			{
				$split_html.="<td id=txtpt".$type['payid']." onkeyup=\"javascript:updateSplit(".$outstanding.");\" style=\"width:101px;text-align:left;\" value=\"0.00\" >&pound;</td>
						<input type=hidden name=pt".$type['payid']." id=pt".$type['payid']." />";
			}
		}
		echo "</tr></table>";
		$split_html.="<td><button id=splittot onclick=\"javascript:split_pay();\" disabled>Enter<br>Amounts</button><br><button id=canbut style=\"width:85px;\" onclick=\"javascript:cancelDiag();\">Cancel</button></td></tr></table>";

		echo "<div id=splitdiv>";
		echo $split_html;
		echo "</div>";
		echo "<div id=finish></div>";
		echo "<p width=100% align=right><button onclick=\"javascript:closeDiag();\">Close</button></p>";
		echo "<script type=text/javascript>$('button, #check').button();</script>";
}

elseif ($_REQUEST['action']=="pay")
{
	$paymethod=$_REQUEST['id'];
	$till=$_COOKIE['tillIdent'];
	$tillsession=getTillSession($till);
	$company=getTillCompany($till);
	$totals['outstanding']=$_REQUEST['outstanding'];

	$givechange=0;
	$opendrawer=0;
	$spendID[]=array();
	$types[]=array();
	
	#Get tenderno
	$tenderno=getMaxSpendTender()+1;	
	#Are we a split payment
		#populate an array with the payids and values
		for ($i=1;$i<20;$i++)
		{
			if ($_REQUEST['pt'.$i]<>0)	
			{
				$sql_query="select payDescr,paytype,givechange, opendrawer, payid, spendPot from TenderTypes where payid=$i";
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
					$paytypes[$i]=$result['paytype'];
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
					$vouchers[$i]=$_REQUEST['voucherid'];
				}
			}
		}
		
		foreach ($types as $type)
		{
			$changedue=0.00;
			if ($tenderamount>$_REQUEST['outstanding'])
			{
				$amounts['1']=$amounts['1']-($tenderamount-$_REQUEST['outstanding']);
				$changedue = ($tenderamount-$_REQUEST['outstanding']);
			}
			$sql_query="insert into spendPotTenders (company, till, till_session, PayMethod, PayType, PayValue, tenderNo)
			values ($company, '$till','$tillsession',".$type.",".$paytypes[$type]."
							,".$amounts[$type].", $tenderno)";
			$insert_tender=$db_conn->query($sql_query);
			createRollEntry($_SESSION['custref'], $result['payDescr'], '', $amounts[$i], '');
		}
		
		if ($givechange==1)
		{
			#Do we need change?
			echo "<table>";
			echo "<tr><td>Change Due</td><td>£".number_format($changedue,2)."</td></tr>";
			echo "</table>";
		}
		
		echo "<div id=finishdetail >";
		if ($_SESSION['custref']<>"")
		{
			$vouch_ref=$_SESSION['custref'];
		}
		else
		{
			$vouch_ref="NULL";
		}
		$voucherid=createSpendPot('G', 'create', $_REQUEST['outstanding'], $vouch_ref, 'NULL' , '', '', $tenderno);
	
	#Create tillroll entry
	createRollEntry($_SESSION['custref'], 'Gift Voucher - '.$voucherid, 0, $_REQUEST['outstanding'], 'G');
	openDrawer(1);

	$foo=printSpendPot($voucherid);
	$deauth=deauthenticate();
	clearReadout();
	echo "<div id=receipt>";
               echo "<script type=text/javascript>$('button').button();</script>";
               echo "<button onclick=\"receipt('print', ".$voucherid.")\">Re-print Receipt</button>";
     echo "</div>";
     
}

elseif ($_REQUEST['action']=='print')
{
	$foo=printSpendPot($_REQUEST['voucherid']);
	echo "<script type=text/javascript>$('button').button();</script>";
	echo "<button onclick=\"receipt('print', ".$_REQUEST['voucherid'].")\">Re-print Receipt</button>";
	
}


?>


<script type="text/javascript">

$('document').ready(function()
{
		$('#amnt').focus();
		$('#amnt').val('0.00');
});
function closeDiag()
{
	$('#temp').remove();
	location.reload();
}

function cardType(id,type, refund)
{
	$('#splittot').disable=false;
	if (id==8 || id==9)
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
	}

	else
	{
		var chk="";
		var amnt=Number($('#amnt').val());
		var amnt2=amnt.toFixed(2);
		$('#pt'+id).val(amnt2);
		updateSplit(amnt);
	}
}

function receipt(type, orderno)
{
	$('#receipt').load('./customer/giftVoucher.php?action=print&type='+type+'&voucherid='+orderno);
}

function showSplit()
{
	$('#splitdiv').slideToggle();
}

function cancelDiag()
{
	location.reload();
}


function split_pay()
{
	var outstanding=$('#amnt').val();
	var get="action=pay&split=yes&";
	get=get+'outstanding='+$('#amnt').val()+'&';
	var total=0;
	var current=0;
	$('#splitdiv input[type=text], #splitdiv input[type=hidden]').each(function(){
		get=get+(this.id)+'='+(this.value)+'&';	
		total=+(this.value)+total;
	});
	var rounded=Math.round(total*100)/100;
	$('#finish').load('./customer/giftVoucher.php?'+get);	
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


function updateSplit()
{
	var outstanding=$('#amnt').val();	
	$( "#splittot" ).button({
		  disabled: false
		});
	var total=0;
	var current=0;
	var get="";
	$('#splitdiv input[type=text], #splitdiv input[type=hidden]').each(function(){
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
		var buttext="Confirm<br>Amounts";
	}
	else
	{
		var buttext='Outstanding<br>&pound;'+rounded;
	}
	$('#splittot span').html(buttext);	
}

$('#tender').click(function(){
	var taken=$('#tendered').val();
	var due=$('#tenderdue').val();
	var chg=+(due)-+(taken);
	var change=Math.round(chg*100)/100;
	$('#changedue').val(change);
	$('#finishdetail').show();
});


$(document).ready(function(){
	$('button').button();
});

$('#submit').click(function(){
	var action=$('#action').val();
	var amnt=$('#amnt').val();
	$('#outcome').load('./customer/giftVoucher.php?action='+action+'&amnt='+amnt);
});

$('#close').click(function(){
	location.reload();
});

$(document).ready(function(){
	$('#amnt').priceFormat({
        prefix: '',
         thousandsSeparator: ''
	});
	$("#dialog-confirm").dialog({
		    autoOpen: false,
		    modal: true
	});
});

</script>
