<?php 
include '../config.php';
include '../functions/auth_func.php';
include '../functions/print_func.php';

session_start();


if ($_REQUEST['action']=="print")
{
	
	
	$html=<<<EOF
	<style>
	body
	{
	        font-family:arial;
	        font-size:8pt !important;
	}
			
	table tr.receipt td
	{
		font-size:10pt;
		padding-top:0px;
		padding-bottom:0px;
	}
	
	tr
	{
			padding-top:0px;
			padding-bottom:0px;
	}
	
	table td
	{
			padding-left:0px;
			padding-right:0px;
			padding-top:0px;
			padding-bottom:0px;
	}
	@page { margin:0px;top 0px; margin-top:0px; margin-bottom:0px;margin-left:20px; }
	</style>
EOF;
	
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	
	#When did we last check?
	$sql_query="select value from config where config='fillups'";
	$results=$db_conn->query($sql_query);
	
	$last_check=mysqli_fetch_array($results);
	
	$html.="<table width=90%>";
	$company=getTillCoName($_COOKIE['tillIdent']);
	if ($_REQUEST['print']==1)
	{
		$html.="<tr><td colspan=3>$company</td><td colspan=3 style=\"text-align:right;\"></td></tr>";
	}
	else
	{
		$html.="<tr><td colspan=2>$company</td><td colspan=5 style=\"text-align:right;\"><button class=half 
			onclick=\"javascript:sendPrint('".$_REQUEST['interval']."','".$_REQUEST['startdate']."','".$_REQUEST['enddate']."');\">Print</button>
				<button class=half onclick=\"javascript:location.reload();\">Close</button></td></tr>";		
	}
	$html.="<tr><td colspan=7>PRODUCT SALES REPORT</td></tr>";
	
	$sql_query="select StockRef, colour, size, od.sizeindex, sea.season Season, bra.brand, od.qty
	from styleDetail style USE INDEX (search2), orderdetail od use index (PRIM), seasons sea, brands bra 
	where 1=1
	and od.StockRef = style.sku
	and style.season = sea.id
	and style.brand = bra.id
	and od.status not in ('N','P','V','X','W','S','K','J')
	and company =".getTillCompany($_COOKIE['tillIdent']);
	
	if ($_REQUEST['interval']=='last')
	{
		$sql_query.=" and timestamp >= '".$last_check['value']."'";
		$html.="<tr class=receipt><td colspan=6>Incremental List</td></tr>";
		$html.="<tr class=receipt><td colspan=3>Time Printed</td><td colspan=3>".date("d-m-Y H:i:s")."</td></tr>";
		
		$sql_query2="update config set value = current_timestamp() where config='fillups'";
	}
	elseif ($_REQUEST['interval']=='today')
	{
		$sql_query.=" and timestamp >= current_date() ";
		$html.="<tr class=receipt><td colspan=7>Today's Sales</td></tr>";
		$html.="<tr class=receipt><td colspan=2>Time Printed</td><td colspan=5>".date("d-m-Y H:i:s")."</td></tr>";
		$sql_query2="update config set value =  current_timestamp() where config='fillups'";
	}
	elseif ($_REQUEST['interval']=='interval')
	{
		$sql_query.=" and timestamp >= str_to_date('".$_REQUEST['startdate']." 00:00:00','%Y-%m-%d %H:%i:%s') and timestamp <= str_to_date('".$_REQUEST['enddate']." 23:00:00','%Y-%m-%d %H:%i:%s')";
		$html.="<tr class=receipt><td colspan=7>Historical Interval</td></tr>";
		$html.="<tr class=receipt><td colspan=2>Time Printed</td><td colspan=5>".date("d-m-Y H:i:s")."</td></tr>";
		$html.="<tr class=receipt><td>Date Start : </td><td colspan=2>".$_REQUEST['startdate']."</td><td>Date End</td><td colspan=3>".$_REQUEST['enddate']."</td></tr>";
	}
	
	$html.="</table><table width=90%>";
	$sql_query.=" order by StockRef, colour";
	$results=$db_conn->query($sql_query);	
	#Onscreen will have a different column setup
	$onscreen=$html;
	
	$html.="<tr class=receipt><td>SKU</td><td>Brand</td><td>Season</td><td>Colour</td><td>Size</td><td>Q</td><td>S</td></tr>";
	$onscreen.="<tr class=receipt><td>SKU</td><td>Brand</td><td>Season</td><td>Colour</td><td>Size</td><td>Q</td><td>S</td></tr>";
	while ($fillups=mysqli_fetch_array($results))
	{
		#We just need the balance now....
		$stocklevel=stockBalance($fillups['StockRef'], $fillups['colour'],'');
	
		#Build for print
		$html.="<tr class=receipt><td>".$fillups['StockRef']."</td><td>".$fillups['brand']."</td><td>".$fillups['Season']."</td>
				<td>".$fillups['colour']."</td><td>".$fillups['size']."</td><td align=left>".$fillups['qty']."</td><td  align=left>".$stocklevel['physical'.$fillups['sizeindex']]."</td></tr>";
		
		#Build for screen
		$onscreen.="<tr class=receipt><td>".$fillups['StockRef']."</td><td>".$fillups['brand']."</td><td>".$fillups['Season']."</td>
				<td>".$fillups['colour']."</td><td>".$fillups['size']."</td><td align=left>".$fillups['qty']."</td><td  align=left>".$stocklevel['physical'.$fillups['sizeindex']]."</td></tr>";
	}
	$html.="</table>";
	$onscreen.="</table>";
	#call print code for fillups passing html
	
	if ($_REQUEST['print']==1)
	{
		generic_header(0);
		print_action($html, $receipt_printer, false, 'true');
		$doit=$db_conn->query($sql_query2);
		deauthenticate();
		if ($local_printer==1)
		{
		    echo "<script type=text/javascript>printJS('$local_printer_path/printing.pdf');</script>";
		}
		echo "<script type=text/javascript>$('#dialog').hide();$('#dimmer').hide();</script>";
	}
	else
	{	
		echo $onscreen;

	}

}

if (!$_REQUEST['action'])
{
	#Ask what we are printing the fillups for
	echo "<table><tr><th colspan=3>Fill Up Report</th><th></th></tr>";
	echo "<tr><td>Since Last Print</td><td></td><td><button id=last>Go</button></td></tr>";
	echo "<tr><td>All Today</td><td></td><td><button id=today>Go</button></td></tr>";
	//echo "<tr><th colspan=3 align=center>Interval</th></tr>";
	echo "<tr><td align=center>Start Date</td><td  align=center>End Date</td><td></td></tr>";
	echo "<tr><td><input type=date id=startdate></td><td><input type=date id=enddate></td><td><button id=interval>Go</button></td></tr>";
	echo "<tr><td></td><td></td><td><button onclick=\"javascript:location.reload();\">Close</button></td></tr>";
	echo "</table>";
}

?>
<script type=text/javascript>
$(document).ready(function(){
	$('button').button();
	
});

function sendPrint(interval, startdate, enddate)
{
	var getstring='action=print&interval='+interval;
	$('#temp').load('./report/topUps.php?print=1&'+getstring+'&startdate='+startdate+'&enddate='+enddate);
}

$('#last').click(function(){
	var getstring='action=print&interval=last';
	$('#temp').load('./report/topUps.php?'+getstring);
});

$('#today').click(function(){
	var getstring='action=print&interval=today';
	$('#temp').load('./report/topUps.php?'+getstring);
});

$('#interval').click(function(){
	var start=$('#startdate').val();
	var end=$('#enddate').val();
	var getstring='action=print&interval=interval&startdate='+start+'&enddate='+end;
	$('#temp').load('./report/topUps.php?'+getstring);
});
</script>
