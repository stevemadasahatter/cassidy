<?php 

include '../config.php';
include '../functions/auth_func.php';
ini_set('DISPLAY_ERRORS',1);
session_start();
$auth=check_auth();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$action=$_REQUEST['action'];

#We need to adjust the entered data to keep the customer database clean
$_REQUEST['title']=ucwords($_REQUEST['title']);
$_REQUEST['forename']=ucwords($_REQUEST['forename']);
$_REQUEST['lastname']=ucwords($_REQUEST['lastname']);
$_REQUEST['addr1']=ucwords($_REQUEST['addr1']);
$_REQUEST['addr2']=ucwords($_REQUEST['addr2']);
$_REQUEST['addr3']=ucwords($_REQUEST['addr3']);
$_REQUEST['addr4']=ucwords($_REQUEST['addr4']);
$_REQUEST['addr5']=ucwords($_REQUEST['addr5']);
$_REQUEST['postcode']=strtoupper($_REQUEST['postcode']);


if ($action=="edit")
{	
	$custref=$_SESSION['custref'];
	$sql_query="select title, forename, lastname, addr1, addr2, addr3, addr4, addr5, postcode, landline, mobile, 
				date_format(dob, '%d/%m/%Y') dob, email, emailmkt, textmkt from customers where 1=1 and  custid =".$custref;
	$company=getTillCompany($_COOKIE['tillIdent']);
	
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);
}

elseif ($action=="add")
{
	$company=getTillCompany($_COOKIE['tillIdent']);
}

elseif ($action=="clear")
{
	unset($_SESSION['custref']);
	$sql_query="update orderheader set custref = 4 where transno = '".$_SESSION['orderno']."'";
	$do_it=$db_conn->query($sql_query);
	echo "<script>javascript:location.reload();</script>";
}

elseif ($action=="update")
{
	$company=getTillCompany($_COOKIE['tillIdent']);
	if ($_REQUEST['custref']=="")
	{
		$setClause="'".$_REQUEST['title']."',\"".$_REQUEST['forename']."\",\"".$_REQUEST['lastname']."\",\"".$_REQUEST['addr1']."\",\"".$_REQUEST['addr2']."\",\"".$_REQUEST['addr3']."\",'".$_REQUEST['addr4']."','".$_REQUEST['addr5']."','".$_REQUEST['postcode']
		  ."','".$_REQUEST['landline']."','".$_REQUEST['mobile']."','".$_REQUEST['email']."','".$company."',".$_REQUEST['textmkt'].",".$_REQUEST['emailmkt'];
		$sql_query="insert into customers (title, forename, lastname, addr1, addr2, addr3
		, addr4, addr5, postcode, landline, mobile,  email, company, textmkt, emailmkt";
		if ($_REQUEST['dob']<>"")
		{
			$setClause.=" ,str_to_date('".$_REQUEST['dob']."','%d/%m/%Y')";
			$sql_query.=" ,dob";
		}
		$sql_query.=")
			values(".$setClause.")";
		$result=$db_conn->query($sql_query);
		$newcustref=mysqli_insert_id($db_conn);
		$_SESSION['custref']=$newcustref;
		#If existing order, then dont create. Else do
		if ($_SESSION['orderno']=="")
		{
			createOrder($_COOKIE['tillIdent'], $newcustref);
		}
		else
		{
			changeCust($_SESSION['orderno'], $newcustref);
		}
	}
	else
	{
		$setClause=" title='".$_REQUEST['title']."',forename=\"".$_REQUEST['forename']."\",lastname=\"".$_REQUEST['lastname']."\",addr1=\"".$_REQUEST['addr1']."\",addr2=\"";
		$setClause.=$_REQUEST['addr2']."\",addr3=\"".$_REQUEST['addr3']."\",addr4='".$_REQUEST['addr4']."',addr5='".$_REQUEST['addr5']."',postcode='".$_REQUEST['postcode']."',landline='";
		$setClause.=$_REQUEST['landline']."',mobile='".$_REQUEST['mobile']."',email='".$_REQUEST['email']."',company=".$company.",textmkt=".$_REQUEST['textmkt'].",emailmkt=".$_REQUEST['emailmkt'];
		if ($_REQUEST['dob']<>"")
		{
			$setClause.=" ,dob=str_to_date('".$_REQUEST['dob']."','%d/%m/%Y')";
		}
		$sql_query="update customers set ".$setClause." where custid=".$_REQUEST['custref'];
		$result=$db_conn->query($sql_query);
	}
	
	if ($newcustref && $_SESSION['orderno']=="")
	{
		$neworder=createOrder($_COOKIE['tillIdent'], $newcustref);
	}
	elseif ($newcustref && $_SESSION['orderno']<>"")
	{
		$change=changeCust($_SESSION['orderno'], $newcustref);
	}
	echo "<script type=text/javascript>location.reload();</script>";
}

# Draw up table for record
echo "<h2>Customer Record</h2>";
echo "<table>";
echo "<tr><td>Title</td><td><input class=custinfo type=text name=title value=".$detail['title']." ></td></tr>";
echo "<tr><td>First Name</td><td><input class=custinfo  type=text name=forename value=\"".$detail['forename']."\" ></td></tr>";
echo "<tr><td>Last Name</td><td><input  class=custinfo type=text name=lastname value=\"".$detail['lastname']."\" ></td></tr>";
echo "<tr><td>Address1</td><td><input class=custinfo  type=text name=addr1 value=\"".$detail['addr1']."\" ></td></tr>";
echo "<tr><td>Address2</td><td><input class=custinfo type=text  name=addr2 value=\"".$detail['addr2']."\" ></td></tr>";
echo "<tr><td>Address3</td><td><input class=custinfo  type=text  name=addr3 value=\"".$detail['addr3']."\" ></td></tr>";
echo "<tr><td>Address4</td><td><input class=custinfo type=text  name=addr4 value=\"".$detail['addr4']."\" ></td></tr>";
echo "<tr><td>Address5</td><td><input class=custinfo type=text  name=addr5 value=\"".$detail['addr5']."\" ></td></tr>";
echo "<tr><td>PostCode</td><td><input class=custinfo type=text  name=postcode value=\"".$detail['postcode']."\" ></td></tr>";
echo "<tr><td>Landline</td><td><input class=custinfo type=text  name=landline value=\"".$detail['landline']."\" ></td></tr>";
echo "<tr><td>Mobile</td><td><input class=custinfo type=text  name=mobile value=\"".$detail['mobile']."\" ></td></tr>";
echo "<tr><td>DoB (DD/MM/YYYY)</td><td><input  class=custinfo type=text  name=dob value=\"".$detail['dob']."\" ></td></tr>";
echo "<tr><td>Email address</td><td><input  class=custinfo type=text  name=email value=\"".$detail['email']."\" ></td></tr>";
echo "<tr><td>Email Mkt <input type=checkbox name=emailmkt ";

if ($detail['emailmkt']==1 || $detail['emailmkt']=="" ){ echo " checked ";}

echo "</input></td><td align=right>Text Mkt <input type=checkbox name=textmkt ";
if ($detail['textmkt']==1 || $detail['textmkt']=="" ){ echo " checked "; }

echo "</input></td></tr>";

echo "<input type=hidden name=action value=update>";
echo "<input type=hidden name=custref value=".$custref.">";
echo "<td align=left><button onclick=\"javascript:Close();\" >Cancel</button></td><td align=right><button onclick=\"javascript:subForm();\" >Save<br>Select</button></td>";
echo "</table>";
?>
<script type="text/javascript">

$(document).ready(function(){
			$('button').button();
	});

function Close()
{
	$('#temp').load('./customer/updateCustomer.php?action=clear');
}

function subForm()
{
	var getString="";
	$('input[type!=checkbox]').each(function(){
		if (this.value!="")
		{
		getString=getString+this.name+"="+encodeURIComponent(this.value);
		getString=getString+'&';
		}

	});
	$('input[type=checkbox]').each(function(){
		if(this.checked)
		{
			getString=getString+this.name+"=1&";
		}
		else
		{
			getString=getString+this.name+"=0&";
		}
	});
	$('#temp').load('./customer/updateCustomer.php?'+getString);
}
</script>
