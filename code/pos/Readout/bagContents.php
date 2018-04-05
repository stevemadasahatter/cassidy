<?php

include '../config.php';
include '../functions/auth_func.php';

session_start();
$auth=check_auth();

$till=$_COOKIE['tillIdent'];
$tillsession=getTillSession($till);

$sku=$_REQUEST['sku'];
$colour=$_REQUEST['colour'];
$sizeindex=$_REQUEST['sizeindex'];
$orderno=$_SESSION['orderno'];
$action=$_REQUEST['action'];
$lineno=$_REQUEST['lineno'];

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

#Get order lines
$sql_query="select StockRef,colour, size, qty, lineno, status, grandtot, onsale from orderdetail where transno=$orderno and status<>'X' order by lineno";
$results=$db_conn->query($sql_query);
echo "<div style\"width:100%;\">";
echo "<p width=60% align=center>";
echo "<table width=80%><tr class=bagtablehead><td class=bagtablehead></td><td class=bagtablehead>Product Code</td><td class=bagtablehead>Price</td><td align=center class=bagtablehead>Qty</td>
		</tr>";
while ($bagitem=mysqli_fetch_array($results))
{
    $photo=getWebImage($bagitem['StockRef'], $bagitem['colour']);
    if ($photo[0]=="")
    {
        echo "<tr><td></td>";
    }
    else
    {
        echo "<tr><td><img width=80px src='".$pics_path."/".$photo[0]."' /></td>";
    }
        
	echo "<td>".$bagitem['StockRef']."-".$bagitem['colour']."-".$bagitem['size']."</td>";
	echo "<td id=price onclick=\"javascript:discount('".$bagitem['StockRef']."');\"";
	if ($bagitem['onsale']==1)
	{
		echo "class=sale";
	}
			
	echo ">&pound;".$bagitem['grandtot']."</td><td align=center>".$bagitem['qty']."</td>";
}

echo "</table></p>";
echo "</div>";


?>
