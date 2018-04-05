<?php

include '../config.php';
include './functions/auth_func.php';

$sort=$_REQUEST['sort'];
$dir=$_REQUEST['dir'];
$type=$_REQUEST['type'];

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
echo "<div id=searchterm>";

if ($type=="C")
{
	echo "<h2>Valid Credit Notes</h2>";
}

if ($type=="G")
{
	echo "<h2>Valid Gift Vouchers</h2>";
}

if ($type=="D")
{
	echo "<h2>Valid Deposit Notes</h2>";
}

echo "<table><tr><th onclick=\"javascript:sort(1,'".$type."');\">Name</th><th onclick=\"javascript:sort(2,'".$type."');\">Surname</th><th onclick=\"javascript:sort(4,'".$type."');\">Purchase Date</th>
			<th onclick=\"javascript:sort(5,'".$type."');\">Amnt</th><th onclick=\"javascript:sort(3,'".$type."');\">Voucher</th></tr>";

$sql_query="select c.forename, c.lastname, v.id, date_format(createdDate, '%d-%m-%Y %H:%i') createdDate, amount 
from customers as c
right join spendPots as v
on  c.custid = v.custref
where v.usedDate is NULL
";

if ($type=="C")
{
	$sql_query.=" and type='C'";
}
elseif ($type=="G")
{
	$sql_query.=" and type='G'";
}
elseif ($type=="D")
{
	$sql_query.=" and type='D'";
}


if ($sort && $dir)
{
	$sql_query.=" order by $sort";
}
elseif ($sort && !$dir)
{
	$sql_query.=" order by $sort desc";
}
$results=$db_conn->query($sql_query);

while ($result=mysqli_fetch_array($results))
{
	if ($type=="C")
	{
		echo "<tr onclick=\"javascript:select('".$result['id']."','C');\"><td>".$result['forename']."</td>";
	}
	elseif  ($type=="G")
	{
		echo "<tr onclick=\"javascript:select('".$result['id']."','G');\"><td>".$result['forename']."</td>";
	}
	elseif ($type=="D")
	{
		echo "<tr onclick=\"javascript:select('".$result['id']."','D');\"><td>".$result['forename']."</td>";
	}
	
	echo "<td>".$result['lastname']."</td>";
	echo "<td>".$result['createdDate']."</td>";
	echo "<td align=right>&pound;".$result['amount']."</td>";
	echo "<td>".$result['id']."</td></tr>";
	
}
echo "</table>";
echo "<p width=100% align=right><input type=hidden id=dir value=$dir><button id=close>Close</button></p>";
echo "</div>";
?>
<script>
$(document).ready(function(){
	$('button').button();
});

$('#close').click(function(){
	$('#voucherform').slideUp('fast');
	$('#vouchers').hide();

});
function select(id,type)
{
	$('#voucherform').slideDown('fast');
	$('#vouchercode').val(id);
	entervoucher(id,type);
	$('#vouchers').hide();
}

function sort(no,type)
{
	var dir=$('#dir').val();
	if (dir=='')
	{
		dir="desc";
	}
	else
	{
		dir="";
	}
	$('#vouchers').load('./order/vouchersearch.php?sort='+no+'&dir='+dir+'&type='+type);
}

</script>