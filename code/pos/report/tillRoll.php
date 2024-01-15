<?php

include '../config.php';
include '../functions/print_func.php';

$type=$_REQUEST['type'];
$dateFrom=$_REQUEST['dateFrom'];
$dateTo=$_REQUEST['dateTo'];

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);

#Work out the date predicate
if ($type=="now")
{
	$datePred="between current_date() and now() ";
}

if ($type=="yesterday")
{
	$datePred="between date_sub(current_date(), interval 1 day) and current_date() ";
    $dateFrom= date('Y-m-d',strtotime("-1 days"));
    $dateTo= date('Y-m-d');
}

if ($type=="range")
{
	$datePred="between STR_TO_DATE('$dateFrom', '%Y-%m-%d') and date_add(STR_TO_DATE('$dateTo', '%Y-%m-%d'), interval 1 day) ";
}

#Establish what we need to do
$sql_query="select type, id, min(transDate)
from
(
select 'Sale' type,oh.transno id, oh.transDate transDate from orderheader oh, orderdetail od 
where oh.transno = od.transno and od.status not in ( 'N','P','W','S') and od.timestamp $datePred
union
select 'Spendpot' type, id, createdDate transDate  from spendpots where createdDate $datePred
union
select 'Pettycash' type, id, timestamp transDate from pettycash where timestamp $datePred
union
select 'Float' type, rollID id, timestamp transDate from tillrolldetail where timestamp $datePred 
and description like '%Float%'
) summ
group by type, id
order by 3";

$works=$db_conn->query($sql_query);

$html=generic_header('0');
while ($work=mysqli_fetch_array($works))
{	
	if ($work['type']=='Sale')
	{
		$html.="Sale";
		$html.=printReceipt($work['id'], 'html', 'false',$dateFrom);
		$html.="<hr>";

	}
	elseif ($work['type']=='Spendpot')
	{
		$html.="Spend Pot";
		$html.=printSpendPotTR($work['id'],'html');
		$html.="<hr>";
	}
	elseif ($work['type']=="Pettycash")
	{
		$html.="Petty Cash";
		$html.=printPettyCash($work['id'], 'html');
	 	$html.="<hr>";
	}
	elseif ($work['type']=='Float')
	{
	    $html.="Float check";
	    $html.=printFloat($work['id']);
	    $html.="<hr>";
	}

}

if ($_REQUEST['print']<>"true")
{	
	echo "<p align=right><button id=print>Print</button></p>";
	echo "<input type=hidden id=type value='".$_REQUEST['type']."' />";
	echo "<input type=hidden id=dateTo value='".$dateTo."' />";
	echo "<input type=hidden id=dateFrom value='".$dateFrom."' />";
	echo $html;
}

if ($_REQUEST['print']=="true")
{
    print_action($html, $receipt_printer, "false","false");
    if ($local_printer==1)
    {
        echo "<script type=text/javascript>printJS('$local_printer_path/printing.pdf');</script>";
    }
    else
    {
        print_action($html,$receipt_printer,"false","true");
    }
    
}
?>

<script type=text/javascript>
	$('#print').button();

$('#print').click(function(){
	var type=$('#type').val();
	var dateTo=$('#dateTo').val();
	var dateFrom=$('#dateFrom').val();
	$('#tillRoll').load('./report/tillRoll.php?print=true&dateTo='+dateTo+'&dateFrom='+dateFrom+'&type='+type);
});

</script>
