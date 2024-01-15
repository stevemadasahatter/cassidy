<?php 

include '../config.php';
include '../functions/auth_func.php';
session_start();
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

#Authorised?
$auth=check_auth();
if ($auth<>1)
{
	echo "<p><p>";
	exit();
}

#Active till session?
$active=getTillSession($_COOKIE['tillIdent']);
if ($active==0)
{
	exit();
}

if ($_REQUEST['action']=='clear')
{
	unset($custref);
	$update=changeCust($_SESSION['orderno'],'');
	$_SESSION['discount']="";
	echo "<script type=text/javascript>location.reload();</script>";
}

$custref=$_SESSION['custref'];
if ($custref>0 && $custref<>4)
{
	$sql_query="select forename, lastname from customers where custid = $custref";

	$results=$db_conn->query($sql_query);
	$names=mysqli_fetch_array($results);
	
	echo "<table><tr><td><div id=buttons onchange=\"javascript:searchCust();\" >
    <input type=radio id=searchtype1 checked=checked name=searchtype value=name>
        <label class=half for=searchtype1>Name</label>
    <input type=radio id=searchtype2 name=searchtype value=addr>
		<label class=half  for=searchtype2>Address</label>
    <input type=radio id=searchtype3 name=searchtype value=email>
        <label class=half for=searchtype3>EMail</label>
    <input type=radio id=searchtype4 name=searchtype value=phone>
        <label class=half for=searchtype4>Phone</label>
     </div></td></tr>";
	echo "<tr><td>".$names['forename']." ".$names['lastname']."</td>
          <td style=\"position:relative;\" rowspan=2><button class=half  onclick=\"javascript:clearCust();\">Clear</button></td>
          </tr></table>";
}

else 
{
	echo "<table><tr><td><div id=buttons onchange=\"javascript:searchCust();\">
    <input type=radio id=searchtype1  name=searchtype checked=checked value=name>
        <label class=half for=searchtype1>Name</label>
    <input type=radio id=searchtype2 name=searchtype value=addr>
	   <label class=half for=searchtype2>Address</label>
    <input type=radio id=searchtype3 name=searchtype value=email>
        <label class=half for=searchtype3>EMail</label>
    <input type=radio id=searchtype4 name=searchtype value=phone>
        <label class=half for=searchtype4>Phone</label>
    </div></td></tr>";
	echo "<tr><td><input style=\"width:100%;\" onkeyup=\"javascript:searchCust();\" id=cust ></input></td>
          <td style=\"position:relative;\" rowspan=2><button class=half onclick=\"javascript:newCust();\" >New</button></td>
          </tr></table>";
}

?>

<script type="text/javascript">
$(document).ready(function(){
	$('button').button();
	$('#buttons').buttonset();
});
function searchCust()
{
	var search=$('#cust').val();
	var stringf=search.replace(/ /g,'%20');
	if ($('#searchtype1').prop('checked')==true)
	{
		var type='name';
	}
	else if ($('#searchtype2').prop('checked')==true)
	{
		var type='addr';
	}
	else if ($('#searchtype3').prop('checked')==true)
	{
		var type='email';
	}
	else if ($('#searchtype4').prop('checked')==true)
	{
		var type='phone';
	}
	$('#custresult').show();
	$('#custresult').slideDown();
	$('#custresult').load('./customer/ajaxsearch.php?s='+stringf+'&type='+type);

}

function clearCust()
{

	$('#custdetail').load('./customer/custDetail.php?action=clear');
	$('#itemsearchbox').load('./order/itemsearch.php');
	$('#custsearch').load('./customer/search.php');
}

function newCust()
{
         $('#dialog').append('<div id=temp></div>');
         $('#dialog').css('top','20%');
         $('#dialog').css('left','50%');
         $('#dialog').css('margin-left','-10%');
         $('#temp').load('./customer/updateCustomer.php?action=add');
         $('#dimmer').show();
         $('#dialog').show();

}

</script>
