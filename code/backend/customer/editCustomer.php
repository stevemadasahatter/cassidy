<?php 

include '../config.php';
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="")
{
	echo "<h2>Search for a Customer to Edit</h2>";
	#Must be searching for a size then
	echo "<table>";
	echo "<tr><td>Name</td><td>Address</td><td>Phone number</td></tr>";
	echo "<tr><td><input autocomplete=off onkeyup=\"javascript:srch(this.value,'n');\" name=name></td>
        <td><input autocomplete=off onkeyup=\"javascript:srch(this.value,'a');\" name=name></td>
        <td><input autocomplete=off onkeyup=\"javascript:srch(this.value,'p');\" name=name></td>
        <td><button onclick=\"javascript:select();\" >Add</button></td></tr>";
	echo "</table>";
	
	echo "<div id=searchresults2></div>";
	echo "<div id=updater></div>";
	echo "<div id=message></div>";

}
elseif ($action=="results")
{
	$searchterm=$_REQUEST['term'];
	if ($searchterm=="")
	{
		exit();
	}
	
	if ($_REQUEST['type']=='n')
	{
	   $sql_query="select custid,title, forename, lastname, addr1, addr2, postcode from customers where concat(forename,' ',lastname) 
            like '%".$searchterm."%' limit 30";
	}
	elseif ($_REQUEST['type']=='p')
	{
	    $sql_query="select custid,title, forename, lastname, addr1, addr2, postcode,mobile, landline from customers where (mobile like '%".$searchterm."%' 
            or landline like  '%".$searchterm."%')
            limit 30";
	}
	else 
	{
	    $sql_query="select custid,title, forename, lastname, addr1, addr2, postcode from customers where concat(addr1, addr2, postcode)
            like '%".$searchterm."%' limit 30";
	}
	$results=$db_conn->query($sql_query);
	echo "<table  width=100%>";
	
	if ($_REQUEST['type']=='p')
	{
	    echo "<tr><th>Customer</th><th>Landline</th><th>Mobile</th></tr>";
	    while ($result=mysqli_fetch_array($results))
	    {
	        echo "<tr onclick=\"javascript:select('".$result['custid']."');\" >
                <td>".$result['title']." ".$result['forename']." ".$result['lastname']."</td>
                <td align=center>".$result['landline']."</td><td align=center>".$result['mobile']."</td>";
	        echo "</tr>";
	    }
	    
	}
	
	else {
        	echo "<tr><th>Customer</th><th>Address</tr>";
        	while ($result=mysqli_fetch_array($results))
        	{
        		echo "<tr onclick=\"javascript:select('".$result['custid']."');\" >
                <td>".$result['title']." ".$result['forename']." ".$result['lastname']."</td>
                <td>".$result['addr1']." ".$result['addr2']." ".$result['postcode']."</td>";
        		echo "</tr>";
        	}
	}
}
elseif ($action=="select")
{

	$term=$_REQUEST['term'];
	$sql_query="select title, forename, lastname, addr1, addr2, addr3, addr4, addr5, postcode,
			landline, mobile, date_format(dob,'%d/%m/%Y') dob, email, company, discount, emailmkt, textmkt from customers where custid = '".$term."'";
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);
}

elseif ($action=="save")
{
	if ($_REQUEST['term']=="undefined")
	{
		$setClause="'".$_REQUEST['title']."',\"".$_REQUEST['forename']."\",\"".$_REQUEST['lastname']."\",\"".$_REQUEST['addr1']."\",\"".$_REQUEST['addr2']."\",\"".$_REQUEST['addr3']."\"";
		$setClause.=",\"".$_REQUEST['addr4']."\",\"".$_REQUEST['addr5']."\",\"".$_REQUEST['postcode']."\",\"".$_REQUEST['landline']."\",\"".$_REQUEST['mobile']."\"";
		if ($_REQUEST['dob']<>"")
		{
			$setClause.=",str_to_date(\"".$_REQUEST['dob']."\",'%d/%m/%Y')";
		}
		else
		{
			$setClause.=",NULL";
		}
		$setClause.=",\"".$_REQUEST['email']."\",".$_REQUEST['discount'];
		
		if ($_REQUEST['emailmkt']==1)
		{
			$setClause.=", 1";
		}
		else
		{
			$setClause.=", 0";
		}
		
		if ($_REQUEST['textmkt']==1)
		{
			$setClause.=", 1";
		}
		else
		{
			$setClause.=", 0";
		}
				
	

		$sql_query="insert into customers (title, forename, lastname, addr1, addr2, addr3, addr4, addr5, postcode,landline, mobile, dob, email, discount, emailmkt, textmkt)
			values(".$setClause.")";
		
		$result=$db_conn->query($sql_query);
	}
	else
	{
		$setClause=" title='".$_REQUEST['title']."',forename=\"".$_REQUEST['forename']."\",lastname=\"".$_REQUEST['lastname']."\",addr1=\"".$_REQUEST['addr1']."\",addr2=\"".$_REQUEST['addr2']."\",addr3=\"".$_REQUEST['addr3']."\"";
		$setClause.=",addr4=\"".$_REQUEST['addr4']."\",addr5=\"".$_REQUEST['addr5']."\",postcode=\"".$_REQUEST['postcode']."\",landline=\"".$_REQUEST['landline']."\",mobile=\"".$_REQUEST['mobile']."\"";
		if ($_REQUEST['dob']<>"")
		{
			$setClause.=",dob=str_to_date(\"".$_REQUEST['dob']."\",'%d/%m/%Y')";
		}
		
		$setClause.=",email=\"".$_REQUEST['email']."\",discount=".$_REQUEST['discount'];
		
		if ($_REQUEST['emailmkt']==1)
		{
			$setClause.=", emailmkt=1";
		}
		else
		{
			$setClause.=", emailmkt=0";
		}
		
		if ($_REQUEST['textmkt']==1)
		{
			$setClause.=", textmkt=1";
		}
		else
		{
			$setClause.=", textmkt=0";
		}
		$sql_query="update customers set ".$setClause." where custid='".$_REQUEST['term']."'";
		$result=$db_conn->query($sql_query);
	}
	echo "<p class=message>Customer saved</p>";
}



elseif ($action=="delete")
{	
		$term=$_REQUEST['term'];
		$sql_query="delete from customers where custid = '".$term."'";
	
		$do_it=$db_conn->query($sql_query);
		echo "<p class=message>Customer Deleted</p>";
}


if ($action=="select" )
{
# Draw up table for record
echo "<table>";
echo "<h2>Customer Record</h2>";
echo "<tr><td>Title</td><td><input  type=text name=title value='".$detail['title']."' ></td></tr>";
echo "<tr><td>Fore Name</td><td><input  type=text name=forename value='".$detail['forename']."' ></td></tr>";
echo "<tr><td>Last Name</td><td><input  type=text name=lastname value='".$detail['lastname']."' ></td></tr>";
echo "<tr><td>Address1</td><td><input  type=text name=addr1 value='".$detail['addr1']."' ></td></tr>";
echo "<tr><td>Address2</td><td><input  type=text name=addr2 value='".$detail['addr2']."' ></td></tr>";
echo "<tr><td>Address3</td><td><input  type=text name=addr3 value='".$detail['addr3']."' ></td></tr>";
echo "<tr><td>Address4</td><td><input  type=text name=addr4 value='".$detail['addr4']."' ></td></tr>";
echo "<tr><td>Address5</td><td><input  type=text name=addr5 value='".$detail['addr5']."' ></td></tr>";
echo "<tr><td>Postcode</td><td><input  type=text name=postcode value='".$detail['postcode']."' ></td></tr>";
echo "<tr><td>Land Line</td><td><input  type=text name=landline value='".$detail['landline']."' ></td></tr>";
echo "<tr><td>Mobile Phone</td><td><input  type=text name=mobile value='".$detail['mobile']."' ></td></tr>";
echo "<tr><td>Date of Birth (DD/MM/YYYY)</td><td><input  type=text name=dob value='".$detail['dob']."' ></td></tr>";
echo "<tr><td>E-mail</td><td><input  type=text name=email value='".$detail['email']."' ></td></tr>";
echo "<tr><td>Discount</td><td><input  type=text name=discount value='".$detail['discount']."' ></td></tr>";
echo "<tr><td>Marketing Emails? : <input name=emailmkt type=checkbox";

if ($detail['emailmkt']=="1")
{
	echo " checked ";
}

echo "></td><td>Marketing Texts? :<input type=checkbox name=textmkt ";

if ($detail['textmkt']=="1")
{
	echo " checked ";
}

echo "></td></tr>";

echo "</table>";
echo "</div>";
echo "<input type=hidden name=term value='".$term."'>";
echo "<p width=100% align=right><button onclick=\"javascript:subForm();\" >Save</button><button onclick=\"javascript:delterm('".$_REQUEST['term']."');\" >Delete</button><button onclick=\"javascript:location.reload();\" >Close</button></p>";

#Output sales
echo "<div>";

$sql_query="select date_format(od.timestamp, '%d/%m/%Y %H:%i') datetime
	, ELT(FIELD(od.status,
       'A','X', 'C', 'J', 'K'),'OnAppro','OnAppro Return','Sold','Returned','Returned') Status
	, od.StockRef, od.colour,  bra.nicename brand, od.size
    , sea.season
    , od.qty
	, if (od.actualgrand>0, od.actualgrand,od.grandtot) paid
    , if (od.actualgrand>0,od.grandtot - od.actualgrand, 0) discount
    , round((if (od.actualgrand>0,od.grandtot - od.actualgrand, 0))/od.grandTot*100,1) discountage
from orderdetail od, orderheader oh, seasons sea, brands bra, styleDetail sd
where oh.custref = $term
and od.transno = oh.transno
and sea.id = sd.season
and bra.id = sd.brand
and od.StockRef = sd.sku
order by od.timestamp desc
";


$results=$db_conn->query($sql_query);

echo "<table><tr><th align=left>Date Time</th><th align=left>SKU</th><th align=left>Colour</th><th align=left>Size</th><th align=left>Brand</th>
		<th align=left>Season</th><th align=right>Qty</th><th align=right>Paid</th><th align=right>Discount</th><th align=center>%age<br>Discount</th><th align=left>Status</th></tr>";
while ($result=mysqli_fetch_array($results))
{
	echo "<tr><td>".$result['datetime']."<td>".$result['StockRef']."<td>".$result['colour']."<td>".$result['size']."</td><td>".
	$result['brand']."<td>".$result['season']."<td align=right>".$result['qty'].
	"<td align=right>".$result['paid']."</td><td align=right>".$result['discount']."</td><td align=right>".$result['discountage']."</td><td>".$result['Status']."</td></tr>";
}

echo "</table>";
}
?>
<script type="text/javascript">

$(document).ready(function(){
			$('button').button();
	});

function srch(value, type)
{
	$('#searchresults2').show();
	var search=encodeURIComponent(value);
	$('#searchresults2').load('./customer/editCustomer.php?action=results&type='+type+'&term='+search);
}

function select(value)
{
	$('#searchresults2').hide();
	var search2=encodeURIComponent(value);
	$('#updater').load('./customer/editCustomer.php?action=select&term='+search2);
}

function delterm(term)
{
	if (term=="")
	{
		alert('No Customer searched for');
		exit();
	}
	var areyousure=confirm('Care should be taken in deleting Customers. Previous orders will be associated with them');
	if (areyousure==true)
	{
		$('#message').load('./customer/editCustomer.php?action=delete&term='+term);
	}
	$('#output').load('./customer/editCustomer.php');
	
}
function subForm()
{
	var getString="action=save&";
	$('input[type!=checkbox]').each(function(){
		if (this.value!="")
		{
		getString=getString+this.name+"="+encodeURIComponent(this.value);
		getString=getString+'&';
		}
	});
		$('select').each(function(){
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

	$('#message').load('./customer/editCustomer.php?'+getString);
}

</script>
