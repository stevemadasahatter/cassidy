<script>
function editCust(ref)
{
         $('#dialog').append('<div id=temp></div>');
         $('#dialog').css('left','45%');
         $('#dialog').css('margin-left','-10%');
	 $('#temp').load('./customer/updateCustomer.php?action=edit&custref='+ref);
         $('#dimmer').show();
         $('#dialog').show();
}
</script>
<?php

include '../config.php';
include '../functions/auth_func.php';
session_start();

if ($_REQUEST['action']=="select")
{
	$_SESSION['custref']=$_REQUEST['cust'];
}

if ($_REQUEST['action']=='clear')
{
	$_SESSION['custref']=4;
}

if ($_SESSION['custref']<>"")
{
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	$sql_query="select forename, lastname, addr1, postcode, mobile, email from customers where custid='".$_SESSION['custref']."'";
	$results=$db_conn->query($sql_query);
	
	$result=mysqli_fetch_array($results);
	
	#Don't allow edit of walkin customer
	if ($_SESSION['custid']<>4)
	{
	   echo "<table class=custdetail>
		<tr><td style=\"font-weight:bold;text-decoration:underline;\" onclick=\"javascript:launchStats('".$_SESSION['custref']."', '$kibana_host');\">".$result['forename']." ".$result['lastname']."</td>
		<td rowspan=4><img onclick=\"javascript:editCust(".$_SESSION['custref'].");\" src=./images/edit.png /></td>
		</tr><tr><td>Mobile: ".$result['mobile']." </td></tr>
		<tr><td>Address: ".$result['addr1']."</td></tr>
		<tr><td>Email: ".$result['email']."</td></tr>";
	   echo "</table>";
	}

}

if ($_SESSION['custref']<>"" && $_REQUEST['action']=="select")
{
	echo "<script type=text/javascript>editCust(".$_SESSION['custref'].");</script>";
}

if ($_SESSION['orderno']=="" && $custref<>"")
{
	echo "<script type=text/javascript>location.reload();</script";
}

elseif ($_SESSION['orderno']<>"" && $_SESSION['custref']<>"")
{
	$update=changeCust($_SESSION['orderno'],$custref);
	updateReadout();
}
?>

<script type="text/javascript">

	$(document).ready(function(){
		$('#custresult').hide;
<?php if($_REQUEST['action']=='clear')
{
		echo "$('#custsearch').load('./customer/search.php?action=clear');";
}
else 
{
	echo "$('#custsearch').load('./customer/search.php');";
	echo "$('#itemsearchbox').load('./order/itemsearch.php');";
	echo "$('#custinfo').load('./customer/custinfo.php');";
}
	?>
	});

function launchStats(custid,kibana_host)
{
	var url=kibana_host+"/s/cassidy/app/kibana#/dashboard/f1e3b1b0-1a5e-11e9-b2d2-334acb7be06a?_g=(refreshInterval:(pause:!t,value:0),time:(from:now-2y,mode:quick,to:now))&_a=(description:'',filters:!(('$state':(store:appState),meta:(alias:!n,disabled:!f,index:'21c4ea10-1a5b-11e9-b2d2-334acb7be06a',key:custid,negate:!f,params:(query:"+custid+",type:phrase),type:phrase,value:'"+custid+"'),query:(match:(custid:(query:"+custid+",type:phrase))))),fullScreenMode:!t,options:(darkTheme:!f,hidePanelTitles:!t,useMargins:!t),panels:!((embeddableConfig:(),gridData:(h:15,i:'1',w:24,x:0,y:0),id:e6b3a5f0-1a5b-11e9-b2d2-334acb7be06a,panelIndex:'1',type:visualization,version:'6.5.1'),(embeddableConfig:(),gridData:(h:15,i:'2',w:24,x:24,y:0),id:b31beab0-1a5e-11e9-b2d2-334acb7be06a,panelIndex:'2',type:visualization,version:'6.5.1'),(embeddableConfig:(),gridData:(h:13,i:'3',w:48,x:0,y:15),id:dc208ec0-1a5e-11e9-b2d2-334acb7be06a,panelIndex:'3',type:visualization,version:'6.5.1')),query:(language:lucene,query:''),timeRestore:!f,title:'Customer%20Dashboard',viewMode:view)";
	var uri=encodeURI(url);
	$('#stats').append('<iframe style=\"width:100%; height:100%; border:0px solid #ffffff;\" id="commentiframe" />');
	$('#commentiframe').attr('src', uri);
	$('#dimmer').show();
	$('#stats').show();
}
</script>
