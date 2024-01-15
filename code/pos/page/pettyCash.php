<?php
include '../config.php';
include '../functions/auth_func.php';
session_start();
$auth=check_auth();
$till=$_COOKIE['tillIdent'];
$action=$_REQUEST['action'];
$tillsession=getTillSession($till);


if ($auth<>1)
{
	exit();
}
$db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);

if ($action=="add")
{
	$sql_query="update tilldrawer set startval = ".$_REQUEST['startval']." where till='$till' and tillsession=$tillsession";
	$doit=$db_conn->query($sql_query);
	
	echo "<script type=text/javascript>location.reload();</script>";
	createRollEntry('','Starting Float', '0', $_REQUEST['startval'], 'A');
	deauthenticate();
	echo "<script>location.reload();</script>";
}

$startval=getPettyCash($till);

#Generate selection html
$sql_query="select typeid, Descr from pettycashtype";
$results=$db_conn->query($sql_query);
$selecthtml="<option></option>";
while ($type=mysqli_fetch_array($results))
{
	$selecthtml.="<option value=".$type['typeid'].">".$type['Descr']."</option>";
}

echo "<h2>Float Control</h2>";

echo "<table class=petty>";
echo "<tr><td>Previous Closing Float</td><td>&pound; ".$startval['prevcloseval']."</td></tr>";
echo "<tr><td>Starting value</td><td><input type=text id=startval value=\"".$startval['startval']."\" /></td></tr>";
echo "</table>";
echo "<p width=100% align=right><button onclick=\"javascript:$('#temp').remove;$('#dialog').hide();$('#dimmer').hide();\">Close</button><button id=submit>Submit</button>";

createRollEntry('','Prev. Float', '0', $startval['prevcloseval'], 'A');

?>

<script type=text/javascript>
	$('button').button();


$('#startval').priceFormat({
        prefix: '',
         thousandsSeparator: ''
});
	
$('#submit').click(function(){
	var getString="action=add&";
	$('select').each(function(){
		if (this.value!="")
		{
			getString=getString+this.id+"="+encodeURIComponent(this.value);
			getString=getString+'&';
		}
	});
	$('input').each(function(){
		if (this.value!="")
		{
			getString=getString+this.id+"="+encodeURIComponent(this.value);
			getString=getString+'&';
		}
	});
	$('#temp').load('./page/pettyCash.php?'+getString);
});

</script>
