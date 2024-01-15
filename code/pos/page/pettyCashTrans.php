<?php
include '../config.php';
include '../functions/auth_func.php';
include '../functions/print_func.php';
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
	for ($i=1;$i<=3;$i++)
	{
		if ($_REQUEST['out']==1)
		{
			$multiplier=-1;
			$direction="OUT";
		}
		else
		{
			$multiplier=1;
			$direction="IN";
		}
		if ($_REQUEST['value'.$i]<>"" && $_REQUEST['value'.$i] <> 0)
		{
				$sql_query="insert into pettycash (till, tillsession, transamnt, transtype, cashier) 
					values ('$till','$tillsession',".$_REQUEST['value'.$i]*$multiplier.",".$_REQUEST['select'.$i].",'".$_SESSION['POS']."')";
				$do_it=$db_conn->query($sql_query);
				#Make a note of IDs created for receipt
				openDrawer(0);
				printPettyCash(mysqli_insert_id($db_conn));
				if ($local_printer==1)
				{
				    echo "<script type=text/javascript>printJS('$local_printer_path/printing.pdf');</script>";
				}
				createRollEntry('', 'Petty Cash '.$direction, '0', $_REQUEST['value'.$i], 'A');
		}	
	}
			
	echo "<script type=text/javascript>setTimeout(function(){location.reload();},2000);</script>";
	deauthenticate();
	
}

#Generate selection html
$sql_query="select typeid, Descr from pettycashtype order by Descr";
$results=$db_conn->query($sql_query);
$selecthtml="<option></option>";
while ($type=mysqli_fetch_array($results))
{
	$selecthtml.="<option value=".$type['typeid'].">".$type['Descr']."</option>";
}

echo "<h2>Petty Cash</h2>";

echo "<table class=petty>";
echo "<tr><td>Petty cash In or out?<input type=radio name=inout value=in>In<input type=radio name=inout value=out checked>Out</td><td></td></tr>";
echo "<tr><td colspan=2>Select Petty cash in, or out above.</td></tr>";
echo "<tr><th>Select type</th><th>Amount</th></tr>";
echo "<tr><td><select id=select1>".$selecthtml."</td><td><input style=\"width:130px;\" id=value1 type=text value=0.00 /></td></tr>";
echo "<tr><td><select id=select2>".$selecthtml."</td><td><input style=\"width:130px;\"  id=value2 type=text value=0.00 /></td></tr>";
echo "<tr><td><select id=select3>".$selecthtml."</td><td><input style=\"width:130px;\"  id=value3 type=text value=0.00 /></td></tr>";
echo "</table>";
echo "<p width=100% align=right><button onclick=\"javascript:$('#temp').remove;$('#dialog').hide();$('#dimmer').hide();\">Cancel</button><button id=submit>Save</button>";

?>

<script type=text/javascript>
	$('button').button();

$('#value1').priceFormat({
		prefix: '',
		thousandsSeparator: ''
});

$('#value2').priceFormat({
	prefix: '',
	thousandsSeparator: ''
});

$('#value3').priceFormat({
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
	$('input[type=text]').each(function(){
		if (this.value!="")
		{
			getString=getString+this.id+"="+encodeURIComponent(this.value);
			getString=getString+'&';
		}
	});
	$('input[type=radio]').each(function(){
		if(this.checked)
		{
			getString=getString+this.value+"=1&";
		}
		else
		{
			getString=getString+this.value+"=0&";
		}
	});
	$('#temp').load('./page/pettyCashTrans.php?'+getString);
});

</script>
