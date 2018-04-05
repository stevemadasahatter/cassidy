<?php

include '../config.php';
include '../functions/auth_func.php';

$page=$_REQUEST['page'];
if ($page=="")
{
	$page=0;
}

$limit=($page*15).',15';

$search=urldecode($_REQUEST['s']);
$search=addslashes($search);
if ($search=="")
{
	exit();
}
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($_REQUEST['type']=='name')
{
	$sql_query="select forename, lastname, custid, addr1, postcode 
			from customers 
			where concat(forename,' ',lastname) like '%".$search."%'
			order by forename asc limit $limit ";
}
else
{
	$sql_query="select forename, lastname, custid, addr1, postcode
			from customers
			where (addr1 like  '%".$search."%'
					or postcode like '%".$search."%')
			order by forename asc limit $limit ";
}

$results=$db_conn->query($sql_query);
$num_rows=mysqli_affected_rows($db_conn);

echo "<ul>";
if ($page>=1)
{
	echo "<li onclick=\"javascript:nextPage(".($page-1).",'".$_REQUEST['s']."','".$_REQUEST['type']."');\" class=result style=\"text-align:center;padding:0px;background:#777;\" ><img src=./images/up.png /></li>";
}
while ($person=mysqli_fetch_array($results))
{
	echo "<li class=result onclick=\"javascript:selectCust(".$person['custid'].")\" >".$person['forename']." ".$person['lastname']." ".$person['addr1']." ".$person['postcode']."</li>";
}
if ($num_rows >= 15)
{
	echo "<li onclick=\"javascript:nextPage(".($page+1).",'".$_REQUEST['s']."','".$_REQUEST['type']."');\" class=result style=\"text-align:center;padding:0px;background:#777;\" ><img src=./images/down.png /></li>";
}
echo "</ul>";
?>

<script type="text/javascript">
function selectCust(cust)
{
	$('#custresult').slideUp();
	$('#custdetail').load('./customer/custDetail.php?action=select&cust='+cust);
	$('#custsearch').load('./customer/search.php');
}

function nextPage(num, stringf,type)
{
	$('#custresult').load('./customer/ajaxsearch.php?s='+stringf+'&type='+type+'&page='+num);

}
</script>