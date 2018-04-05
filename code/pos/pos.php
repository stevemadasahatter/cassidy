<?php
include './config.php';
include './functions/auth_func.php';



#Is it already open
$session=getTillSession($_COOKIE['tillIdent']);

if ($session==0)
{
	setTillSession(1);
	$_REQUEST['action']=="";

}


if ($_REQUEST['action']=="deauth")
{
	deauthenticate();
	session_destroy();
}

?>

<html>
<head>
	<title>Cassidy : Point of Sale</title>
	<?php 
	$size=getTillType();
	$company=getTillCompany($_COOKIE['tillIdent']);
	echo "<link rel=stylesheet type=text/css href=\"./style/site-$company-".$size['size'].".css\">";
	?>	
 <!-- 	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">  -->
 	<link rel="stylesheet" href="./style/jquery-cr/jquery-ui-<?php echo $company."-".$size['size'];?>.css">  
 	<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
	<!--   <script src="//code.jquery.com/jquery-1.10.2.js"></script>  -->
	<script src="./style/jquery-1.11.3.min.js"></script>
	<script src="./style/jquery-cr/jquery-ui.js"></script>
	<script src="./style/jquery.price_format.2.0.js"></script>
</head>
<body>
<div id=page>
<div id=bkgrdimage></div>
<div id=dialog></div>
<div id=message><p></p><br><button onclick="javascript:closeMessage();">Close</button></div>
<div id=dimmer></div>
<div id=custresult class=ajax></div>
<div id=header class=section>
	<div id=signin class=subsection>
	</div>

	<div id=launcher class=subsection>
	</div>

	<div id=controls class=subsection>
	</div>
</div>

<div id=itemsearch class=section>
		<div id=itemsearchbox>

		</div>
		<div id=itemsearchresults>
		</div>
		

		<div id=custsearchmaster>
			<div id=custtitle>
				<h2>Customer</h2>
			</div>
			<div id=custsearch class=subsection>
			</div>
		</div>
	
	
		<div id=custdetail class=subsection>
		</div>
	
		<div id=custinfo class=subsection>
		</div>
</div>


<div id=items class=section>
	<div id=bag class=subsection>
	<h2>&nbsp;Current Sale</h2>
		<div id=bagitems>
		</div>
			
	</div>
	
	<div id=orderclose class=section>
	<h2>&nbsp;&nbsp;Sale Summary</h2>
		<div id=orderclosebox class=section>
			<div id=totals></div>
			<div id=bagbuttons></div>
		</div>
	</div>
</div>

<!-- <div id=calendar class=calendar>
 <iframe src="https://calendar.google.com/calendar/embed?showTitle=0&amp;showDate=0&amp;showPrint=0&amp;showTabs=0&amp;showCalendars=0&amp;showTz=0&amp;mode=WEEK&amp;height=300&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=fkf4o653k38n5prpfpt33oivqc%40group.calendar.google.com&amp;color=%23ff2968&amp;ctz=Europe%2FLondon" 
	style="border-width:0" width="100%" height="200" frameborder="0" scrolling="no"></iframe>
	<iframe src="https://trello.com/b/dwpqrLBP.html"  width=100% height=300></iframe>
</div>     -->
</div>

<script type="text/javascript">

	$(document).ready(function() {
		$('#signin').load('./auth/login.php');
		$('#custsearch').load('./customer/search.php');
		$('#custdetail').load('./customer/custDetail.php');
		$('#controls').load('./page/controls.php');
		$('#itemsearchbox').load('./order/itemsearch.php');
		$('#bagitems').load('./order/bagContents.php');
		$('#launcher').load('./page/launcher.php');
		$('#custinfo').load('./customer/custinfo.php');
		$('#bagbuttons').load('./order/bagbuttons.php');
	});

function closeMessage()
{
	$('#message').hide();
}		      
</script>
