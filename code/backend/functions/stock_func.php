<?php

function applStockAdj($sku,$colour,$sizeid,$stocklevel,$reason)
{
	include '../config.php';
	include './auth_func.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	#get the stock levels for the variant
	$stocklevels=stockBalance($sku, $colour,'');
	if ($stocklevels['physical'.$sizeid]==$stocklevel)
	{
		#nothing to do
		return 0;
	}
	else
	{
		#work out the difference
		echo "in stock".$stocklevels['physical'.$sizeid];
		echo "requested".$stocklevel."<br>";
		echo "size id=".$sizeid;
		unset($delta);
		$delta=$stocklevels['physical'.$sizeid]-$stocklevel;	
		
		#now create an orderdetail record for it
		$sql_query="insert into stkAdjustments (company, sku, colour, qty, reasonid,sizeid)  values ";
		$sql_query.="(".$_SESSION['CO'].",'$sku','$colour',$delta,$reason,$sizeid)";
		#execute
		$execute=$db_conn->query($sql_query);
	}	
	return 1;


}

function getSizeArray($sizekey)
{	
        include '../config.php';
        include_once '../functions/auth_func.php';
        $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

	$sql_query="select size1,size2, size3, size4, size5, size6, size7, size8, size9, size10,size11,size12, size13, size14, size15, size16, size17, size18, size19, size20 from sizes where sizekey = $sizekey";
	$sizes=$db_conn->query($sql_query);

	return mysqli_fetch_array($sizes); 
}

function getColourways($sku)
{
	include '../config.php';
	include './auth_func.php';
	include './field_func.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$colours=getSelect('colours2','');
	echo "<table>";
	echo "<tr><td style=\"width:190px;\" >Add Colour</td><td>Colour Ways</td><td>Sale Price</td><td>On Web</td><td>Delete</td></tr>";
	echo "<tr><td style=\"vertical-align:top;\" rowspan=20><select name=colours onchange=\"javascript:addColour('".$sku."');\" >".$colours."</select></td><td></td><td></td></tr>";
	
	$sql_query="select colour, saleprice, web_uploaded from stock where Stockref = '".$sku."'";
	$results=$db_conn->query($sql_query);
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr><td>".$result['colour']."</td><td><input name='sp-".$result['colour']."' onblur=\"javascript:salePrice('".$result['colour']."','$sku', this.value);\"  
				type=text value='".$result['saleprice']."' /></td>";
		if ($result['web_uploaded']==1)
		{
			echo "<td>Yes</td>";
	
		}
		else
		{
			echo "<td>No</td>";
		}
		echo "<td><button onclick=javascript:delVariant('".$sku."','".$result['colour']."');>Delete</button></td></tr>";
	}
	
	echo "</table>";
	echo "<div id=colmessage></div>";
}

function addColourway($sku, $colour, $costprice, $retailprice)
{
	include '../config.php';
	include './auth_func.php';
	session_start();
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	#Does it exist
	$sql_query="select colour from stock where Stockref = '".$sku."' and colour ='".$colour."'";
	$results=$db_conn->query($sql_query);
	$result=mysqli_fetch_array($results);
	if ($result['colour']==$colour)
	{
		#Do nothing, already exists
	}
	else 
	{
		$sql_query="insert into stock (Stockref, colour, company, costprice, retailprice) values ('".strtoupper($sku)."','".$colour."',".$_SESSION['CO'].",$costprice, $retailprice)";
		$do_it=$db_conn->query($sql_query);
	}
}

function createStkAdj($sku, $colour, $sizeindex, $qty, $reason, $date, $reference )
{
	include '../config.php';
	include './auth_func.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	$sql_query="insert into stkadjustments values (".$_SESSION['CO'].",'".$sku."','".$colour."',".$qty.",".$reason.",'".$date."',".$sizeindex.",'".$reference."')";
	$do_it=$db_conn->query($sql_query);
	
}


?>

