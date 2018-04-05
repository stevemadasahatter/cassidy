<?php

include '../config.php';
include '../functions/auth_func.php';
session_start();
$auth=check_auth();
$action=$_REQUEST['action'];
$disc=$_REQUEST['disc'];

if ($auth<>1)
{
	exit();
}
$custref=$_SESSION['custref'];
if ($custref=="")
{
	exit();
}

if ($action=="discount");
{
	$_SESSION['discount']=$disc;
	echo "<script type=text/javascript>$('#totals').load('./order/totalsCalc.php');</script>";
}

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

#Get open sessions for this till session
$sql_query="select lifetimeTot, lifetimerank, yeartot, yearrank, sixtot, sixrank from customerRank where custref = ".$custref;
$results=$db_conn->query($sql_query);

echo "<table class=custinfo width=100%><tr><td  class=custinfo>LifeT</td><td  class=custinfo>LifeR</td><td  class=custinfo>YearT</td><td  class=custinfo>YearR</td><td class=custinfo>6T</td><td class=custinfo>6R</td></tr>";
echo "<tr>";
while ($rank=mysqli_fetch_array($results))
{
	echo "<td class=custinfo>".$rank['lifetimeTot']."</td>";
	echo "<td class=custinfo>".$rank['lifetimerank']."</td>";
	echo "<td class=custinfo>".$rank['yeartot']."</td>";
	echo "<td class=custinfo>".$rank['yearrank']."</td>";
	echo "<td class=custinfo>".$rank['sixtot']."</td>";
	echo "<td class=custinfo>".$rank['sixrank']."</td>";
	$lifetimerank=$rank['lifetimerank'];
	$yearrank=$rank['yearrank'];	
}
echo "</tr></table>";

#calc discount
if ($lifetimerank<50 && $lifetimerank>40 &&  $_SESSION['discount']=="" )
{
	$discount=10;
	$_SESSION['discount']=$discount;
}
elseif ($yearrank<50 && $yearrank >20 && $_SESSION['discount']=="")
{
	$discount=10;
	$_SESSION['discount']=$discount;
}
elseif ($yearrank<20 && $_SESSION['discount']=="")
{
	$discount=15;
	$_SESSION['discount']=$discount;
}

echo "<table class=discbut width=100%><tr><td>";
echo "<button onclick=\"setDiscount(0);\">None</button>";
echo "<button onclick=\"setDiscount(5);\">5%</button>";
echo "<button onclick=\"setDiscount(10);\">10%</button>";
echo "<button onclick=\"setDiscount(15);\">15%</button>";
echo "</td><td>Suggested : ".$discount."%<br>Actual : ".$_SESSION['discount']."%</td></tr></table>";

echo "<p width=100% align=right><button onclick=\"javascript:closeDiag();\">Close</button></p>";

?>

<script type=text/javascript>

	$(document).ready(function(){
		$('button').button();
	});


function closeDiag()
{
        $('#temp').remove();
        location.reload();
}


</script>
