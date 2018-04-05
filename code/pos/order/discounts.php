<?php

include '../config.php';
include '../functions/auth_func.php';
session_start();
$sku=$_REQUEST['sku'];
$type=$_REQUEST['type'];
$amount=$_REQUEST['amount'];
$action=$_REQUEST['action'];
session_start();
$orderno=$_SESSION['orderno'];
$lineno=$_REQUEST['lineno'];
$disctype=$_REQUEST['disctype'];
$overridePrice=$_REQUEST['override'];


$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="commit")
{
	discountLine($sku, $amount,$overridePrice,$lineno);
	exit();
}

if ($action=="")
{
	echo "<div id=discount style=\"float:left;\">";
	echo "<table><tr><th></th><th>Amount</th></tr>";
	echo "<tr><td align=right>Line &pound;</td><td><input id=linecash onkeyup=\"javascript:amnt(this.value,'".$lineno."', '".$disctype."');\" style=\"width:100px;\" value=0.00 type=text /></td></tr>";
	echo "<tr><td  align=right>Line %</td><td><input style=\"width:100px;\" onkeyup=\"javascript:pct(this.value,'".$lineno."','".$disctype."');\" type=text /></td></tr>";
	echo "<tr><td  align=right>Order &pound;</td><td><input id=ordercash onkeyup=\"javascript:amnt(this.value,'','".$disctype."');\" style=\"width:100px;\" value=0.00 type=text /></td></tr>";
	echo "<tr><td  align=right>Order %</td><td><input style=\"width:100px;\" onkeyup=\"javascript:pct(this.value,'','".$disctype."');\" width=6 type=text /></td></tr>";
	echo "<tr><td  align=right><strong>Price Override</strong></td><td><input id=overcash style=\"width:100px;\" onkeyup=\"javascript:override(this.value,'".$sku."','".$disctype."','".$lineno."');\" width=6 type=text value=0.00 /></td></tr>";
	echo "</table>";
	echo "<div id=but style=\"float:left;clear:both;\">";
	echo "<p width=100% align=right><input id=orig type=hidden name=orig value=$orig /><button id=commit1>Cancel</button><button id=commit2>Confirm</button></p>";
	echo "</div>";
	echo "</div>";

	echo "<div id=basket style=\"float:left;\">";
}

if ($disctype=="full")
{
	$sql_query="select StockRef,colour, size, qty, lineno, status, grandtot total, onsale from orderdetail where transno=$orderno and status<>'X' order by lineno";
}
elseif ($disctype=="discount")
{
	$sql_query="select StockRef,colour, size, qty, lineno, status, actualgrand total , onsale from orderdetail where transno=$orderno and status<>'X' order by lineno";

}

$results=$db_conn->query($sql_query);

echo "<table><tr align=left><th>SKU</th><th>Price</th><th>Discount</th><th>New Price</th></tr>";
$total=0;
$i=0;

$skus=array();
$prices=array();
$lines=array();

while ($result=mysqli_fetch_array($results))
{
	array_push($skus,$result['StockRef']);
	array_push($prices,$result['total']);
	array_push($lines,$result['lineno']);
	$total=$total+$result['total'];
	$i++;
}

$j=0;
foreach ($lines as $line)
{
	if ($lineno=="" && $type=="pct")
	{
		$value=$prices[$j]-($prices[$j]/100*$amount);
		echo "<tr><td>".$skus[$j]."</td><input type=hidden id=sku$j value=\"".$skus[$j]."\" /></td>";
		echo "<td>".$prices[$j]."</td><td>".number_format($prices[$j]-$value,2)."</td><td>".number_format($value,2)."</td>
		<input type=hidden id=price$j value=\"".($value)."\"/><input type=hidden id=line$j value=\"".($lines[$j])."\"/></tr>";
		//discountLine($skus[$j], $value);	
	}
	elseif ($lineno=="" && $type=="amnt")
	{
		$wholeperc=$amount/$total*100;
		$value=$prices[$j]-($prices[$j]/100*$wholeperc);
		echo "<tr><td>".$skus[$j]."<input type=hidden id=sku$j value=\"".$skus[$j]."\" /></td>";
		echo "<td>".$prices[$j]."</td><td>".number_format($prices[$j]-$value,2)."</td><td>".number_format($value,2)."</td>
		<input type=hidden id=price$j value=\"".($value)."\"/><input type=hidden id=line$j value=\"".($lines[$j])."\"/></tr>";
		//discountLine($skus[$j], $value);
	}
	elseif ($lineno<>"" && $type=="pct")
	{
		//if ($sku==$skus[$j])
		if ($lineno==$j+1)
		{
			$value=$prices[$j]-($prices[$j]/100*$amount);
			echo "<tr><td>".$skus[$j]."<input type=hidden id=sku$j value=\"".$skus[$j]."\" /></td>";
			echo "<td>".$prices[$j]."</td><td>".number_format($prices[$j]-$value,2)."</td><td>".number_format($value,2)."</td>
			<input type=hidden id=price$j value=\"".($value)."\"/><input type=hidden id=line$j value=\"".($lines[$j])."\"/></tr>";
			//discountLine($skus[$j], $value);
		}
		else 
		{
			//echo "<tr><td>".$skus[$j]."</td>";
			//echo "<td>".$prices[$j]."</td><td></td></tr>";
			//discountLine($skus[$j], $value);
		}
	}
	elseif ($lineno<>"" && $type=="amnt")
	{
		if ($lineno==$j+1)
		{
			
			$value=$prices[$j]-$amount;	
			echo "<tr><td>".$skus[$j]."<input type=hidden id=sku$j value=\"".$skus[$j]."\" /></td>";
			echo "<td>".$prices[$j]."</td><td>".number_format($prices[$j]-$value,2)."</td><td>".number_format($value,2)."</td>
			<input type=hidden id=price$j value=\"".($value)."\" /><input type=hidden id=line$j value=\"".($lines[$j])."\"/></tr>";
			//discountLine($skus[$j], $value);
		}
		else
		{
			//echo "<tr><td>".$skus[$j]."</td>";
			//echo "<td>".$prices[$j]."</td><td></td></tr>";
			//discountLine($skus[$j], $value);
		}		
	}
	elseif ($lineno<>"" && $type=="override")
	{
		if ($lineno==$j+1)
		{
			$value=$amount;
			echo "<tr><td>".$skus[$j]."<input type=hidden id=sku$j value=\"".$skus[$j]."\" /></td>";
			echo "<td>".$prices[$j]."</td><td></td><td>".number_format($value,2)."</td>
			<input type=hidden id=price$j value=\"".($value)."\" /><input type=hidden id=line$j value=\"".($lines[$j])."\"/></tr>";
			//discountLine($skus[$j], $value);
		}
	}
	else 
	{
		echo "<tr><td>".$skus[$j]."</td>";
		echo "<td>".$prices[$j]."</td><td></td></tr>";
		//discountLine($skus[$j], $value);
	}	
	$j++;
}

echo "</table>";
echo "</div><div id=output></div>";
?>
<script type="text/javascript">
function pct(amount,lineno,type)
{
	$('#linecash').priceFormat({
    		prefix: '',
    		thousandsSeparator: ''
	});

	orig=$('#orig').val();	
	$('#basket').load('./order/discounts.php?action=discount&disctype='+type+'&lineno='+lineno+'&type=pct&amount='+amount+'&orig='+orig);
}

function override(amount,sku,type, lineno)
{
	$('#overcash').priceFormat({
    		prefix: '',
    		thousandsSeparator: ''
	});
	amount=$('#overcash').val();
	orig=$('#orig').val();	
	$('#basket').load('./order/discounts.php?action=discount&disctype='+type+'&lineno='+lineno+'&sku='+encodeURI(sku)+'&type=override&amount='+amount+'&orig='+orig);
}

function amnt(amount,lineno, type)
{
	if (lineno=="")
	{
                $('#ordercash').priceFormat({
                        prefix: '',
                         thousandsSeparator: ''
                });
        var amount=$('#ordercash').val();

	}

	else
	{
	        $('#linecash').priceFormat({
        	        prefix: '',
               		 thousandsSeparator: ''
	        });
	var amount=$('#linecash').val();
	}
	orig=$('#orig').val();	
	$('#basket').load('./order/discounts.php?action=discount&disctype='+type+'&lineno='+lineno+'&type=amnt&amount='+amount+'&orig='+orig);
}


$(document).ready(function(){
	$('button').button();
});

$('#commit1').click(function(amount,sku,type){
	location.reload();
});

$('#commit2').click(function(){
	$('#basket').find('input[id^="sku"][type=hidden]').each(function(){
		var request = new XMLHttpRequest();
		var skunum=$(this).attr('id').substring(3,10);
		var price=$('#price'+skunum).attr('value');
		var lineno=$('#line'+skunum).attr('value');
		var override=$('#overcash').val();
		var sku=$(this).attr('value');
		var getString='./order/discounts.php?action=commit&sku='+encodeURI(sku)+'&amount='+price+'&override='+override+'&lineno='+lineno;
		request.open('GET', getString, false); 
		request.send(null);
		//$('#output').load(getString);
	});
	location.reload();
});

</script>
