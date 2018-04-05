<?php

function getSelect($type,$selectedid)
{
	$sql_query['companies']="select coname, conum from companies";
	$sql_query['brands']="select nicename, id from brands where active=1 order by 1";
	$sql_query['category']="select nicename, id from category order by 2";
	$sql_query['pettycashtype']="select Descr,typeid from pettycashtype order by 2";
	$sql_query['seasons']="select season, id from seasons order by 1";
	$sql_query['sizekey']="select sizekeydescription, sizekey from sizes order by 2";
	$sql_query['Productgroup']="select nicename,id from ProductGroup order by 2";
	$sql_query['colours']="select nicename, id from colours order by 2";
	$sql_query['colours2']="select nicename, colour from colours order by 2";
	$sql_query['vatkey']="select nicename, vatkey from vatrates order by 2";
	$sql_query['stkadjReason']="select nicename, id from stkadjReason order by 2";
	$sql_query['paytype']="select PayDescr, payId from TenderTypes order by 2";
	
include '../config.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$results=$db_conn->query($sql_query[$type]);
$html="";
$html="<option></option>";
while ($item=mysqli_fetch_array($results))
{
	if ($item[1]==$selectedid)
	{
		$html.="<option value=".$item[1]." selected>".$item[0]."</option>";
	}
	else
	{
		$html.="<option value=".$item[1].">".$item[0]."</option>";

	}
}
return $html;
}

function fuzzygetSelect($type, $match)
{
	if (strlen($match>4))
	{
		$match=substr($match, 1,4);
	}
	$sql_query['companies']="select coname, conum from companies";
	$sql_query['brands']="select nicename, id from brands where active=1 order by 1";
	$sql_query['category']="select nicename, id from category order by 1";
	$sql_query['pettycashtype']="select Descr,typeid from pettycashtype order by 1";
	$sql_query['seasons']="select season, id from seasons order by 1";
	$sql_query['sizekey']="select sizekeydescription, sizekey from sizes order by 1";
	$sql_query['Productgroup']="select nicename,id from ProductGroup order by 1";
	$sql_query['colours']="select colour, id from colours order by 1";
	$sql_query['vatkey']="select nicename, vatkey from vatrates order by 1";
	$sql_query['stkadjReason']="select nicename, id from stkadjReason order by 1";
	$sql_query['colours2']="select colour, colour from colours order by 1";
	
	$fuzzy_query['companies']="select coname, conum from companies order by 1";
	$fuzzy_query['brands']="select nicename, id from brands where active=1 and nicename like '%".$match."%' order by 1";
	$fuzzy_query['category']="select nicename, id from category where nicename like '%".$match."%'  order by 1";
	$fuzzy_query['pettycashtype']="select Descr,typeid from pettycashtype order by 1";
	$fuzzy_query['seasons']="select season, id from seasons order by 1";
	$fuzzy_query['sizekey']="select sizekeydescription, sizekey from sizes order by 1";
	$fuzzy_query['Productgroup']="select nicename,id from ProductGroup order by 1";
	$fuzzy_query['colours']="select colour, id from colours where colour like '%".$match."%'  order by 1";
	$fuzzy_query['colours2']="select colour, colour from colours where colour like '%".$match."%'  order by 1";
	$fuzzy_query['vatkey']="select nicename, vatkey from vatrates order by 1";
	$fuzzy_query['stkadjReason']="select nicename, id from stkadjReason order by 1";
	
	include '../config.php';
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	
	#work out selectid
	$selectids=$db_conn->query($fuzzy_query[$type]);
	$selectidarr=mysqli_fetch_array($selectids);
	$selectedid=$selectidarr[1];

	$results=$db_conn->query($sql_query[$type]);
	$html="";
	while ($item=mysqli_fetch_array($results))
	{
		if ($item[1]==$selectedid)
		{
			$html.="<option value=".$item[1]." selected>".$item[0]."</option>";
		}
		else
		{
			$html.="<option value=".$item[1].">".$item[0]."</option>";
	
		}
	}
	return $html;
}
	
?>
