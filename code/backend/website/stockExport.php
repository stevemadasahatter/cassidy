<?php

include '../config.php';
include './config.php';
include '../functions/auth_func.php';
include '../functions/stock_func.php';
include '../functions/web_func.php';
include '../functions/field_func.php';
include '../functions/m2_func.php';

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$action=$_REQUEST['action'];
$method=$_POST['method'];


if ($action=="" && $method=="")
{
	echo "<div id=slct>";
	$brands=getSelect('brands');
	echo "<h2>Select Brand for Export &nbsp; <button onclick=\"launchClear();\">Clear<br>Down</button></h2>";
	echo "<table><tr><td>Brands</td><td><select id=brands>$brands</select></td><td><button onclick=\"javascript:select();\">Search</td></tr></table>";
	echo "</div>";
	echo "<div id=results></div>";
}

elseif ($action=="list")
{
	# No action means show the list outstanding
	$sql_query = "select stk.Stockref, stk.colour, stk.brand, stk.season, stk.seas, stk.category, webDetails.sku mag_rec, webDetails.categories mag_cats
from 
(select s.Stockref, s.colour, b.nicename brand
	, sea.nicename season, sea.season seas, cat.nicename category
			  from stock s
			  , brands b, styleDetail sd, seasons sea, category cat
			  where web_status = 1 and web_complete = 0
			  and sd.sku=s.Stockref
			  and sd.season=sea.id
			  and sd.brand = b.id
			  and sd.category = cat.id
			  and b.id = ".$_REQUEST['brand'].") stk
	left join webDetails on stk.Stockref = webDetails.sku
            and stk.colour = webDetails.colour";
	$results=$db_conn->query($sql_query);
 	echo "<h3>Export Stock to Website &nbsp;</h3>";	
	echo "<table><tr><th>SKU</th><th>Colour Variant</th><th>Brand</th><th>Season</th><th>Remove</th><th>Match</th><th>Complete</th></tr>";
	
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr id=\"".$result['Stockref']."-".$result['colour']."\"><td>".$result['Stockref']."</td><td>".$result['colour']."</td><td>".$result['brand']."</td>";
		echo "<td>".$result['season']."</td><td><a onclick=\"javascript:removeSKU('".$result['Stockref']."','".$result['colour']."');\">Remove</a></td>";
		echo "<td><a href='#' onclick=\"javascript:editWeb('".$result['Stockref']."'
				,'".$result['colour']."','".$result['brand']."','".$result['seas']."','".$result['category']."');\">";
			if ($result['mag_rec']<>'')
			{
				echo "Re-Match Product</a></td>";
				if ($result['mag_rec']<>"")
				{
				    echo "<td><img src=./images/ok.png onclick=\"javascript:completeWeb('".$result['Stockref']."','".$result['colour']."');\" /></td></tr>";
				}
				else
				{
				    echo "<td><img src=./images/red-cross.jpg  /></td></tr>";
				}
			}
			else
			{
				echo "Match Product</a></td><td></tr>";
			}
	}
	echo "</table>";
}

elseif ($action == "remove")
{
	$sql_query="update stock set web_status = 0 where Stockref = '".$_REQUEST['sku']."' and colour = '".$_REQUEST['colour']."'";
	$do_it=$db_conn->query($sql_query);

}

elseif ($action == "complete")
{
    #We cannot upload with zero stock associated
    $stock=stockBalance($_REQUEST['sku'], $_REQUEST['colour'],'');
    for ($i=1;$i<21;$i++)
    {
        $some_stock+=$stock['physical'.$i];
    }
    if ($some_stock > 0)
    {
 	     $sql_query="update stock set web_complete = 1 where Stockref = '".$_REQUEST['sku']."' and colour = '".$_REQUEST['colour']."'";
	     $do_it=$db_conn->query($sql_query);
	       echo "<script type=text/javascript>$('#results').load('./website/stockExport.php?action=list&brand='+".$_REQUEST['brand'].");</script>";
    }
    else
    {
        echo "<script type=text/javascript>alert('There is no stock against this item, so we cannot upload');</script>";
    }
}

elseif ($action=="load")
{
	#We need to pull out the web details. If there aren't any we'll make a dummy record
	$sql_query="select sku, colour,name, photo, description, brand, season, sizekeydescription, sizegroup from webDetails w
				left join sizes on sizes.sizekey = w.sizegroup
				where 1=1
				and sku = '".$_REQUEST['sku']."'
				and colour = '".$_REQUEST['colour']."'";
	
	$results=$db_conn->query($sql_query);
	$num_rows=mysqli_num_rows($results);
	if ($num_rows <> 0)
	{
		#We have a row in the table, so we can display it
		$result=mysqli_fetch_array($results);
	}
	
	
	else 
	{
		$sql_query="insert into webDetails (sku,colour) values ('".$_REQUEST['sku']."','".$_REQUEST['colour']."')";
		$do_it=$db_conn->query($sql_query);
		
		
	}


	$sizedesc=getItemSizeDesc($_REQUEST['sku']);
	echo "<h2>Web Details Entry Form</h2>";
	
	echo "<table>";
	echo "<tr><td>SKU is ".$_REQUEST['sku']." and colour is ".$_REQUEST['colour']."</td><input type=hidden id=sku value='".$_REQUEST['sku']."' >
				<input type=hidden id=colour value='".$_REQUEST['colour']."' /></tr>";
	echo "<tr><td>Product Name</td><td><input type=text id=prodname value=\"".$result['name']."\"></input></td></tr>";
	echo "<tr><th colspan=2>Description</th></tr>";
	echo "<tr><td colspan=2><textarea style=\"width:600px;height:150px;\" id=description>".$result['description']."</textarea></td></tr>";
	echo "<tr><td>Sizing Type</td><td>";
	$sizegroup_array['ids']=array(503,498,519,1182,558,580,111);
	$sizegroup_array['names']=array('Dress Sizes (8/10/12)'
	                           ,'Shoes (36/37/38)'
	                           , 'Clothes Sizes (S/M/L)'
	                           , 'JQ Size (9/10/11/12)'
	                           , 'Trouser Sizes (28/29/30)'
	                           , 'Mac Trousers (12/12L/14/14L)'
	                           , 'No Size (ANY)'
	                           );
	
	
	echo "<select id=size_type>";
	if (!$result['sizegroup'])
	{
	    echo "<option selected value=sel>[Select]</option>";
	}
	
	for ($i=0;$i<100;$i++)
	{
	    if ($result['sizegroup']==$sizegroup_array['ids'][$i])
	    {
	        echo "<option selected value=".$sizegroup_array['ids'][$i].">".$sizegroup_array['names'][$i]."</option>";
	    }
	    else
	    {
	        echo "<option value=".$sizegroup_array['ids'][$i].">".$sizegroup_array['names'][$i]."</option>";
	    }
	    if ($sizegroup_array['ids'][$i+1]=="")
	    {
	        $i=1000;
	    }
	}

    echo "</select><p>Cassidy Size : ".$sizedesc."</p></td></tr>";
	echo "</table>";
	
	
	#Start of photos
	echo photoDisplayGrid($_REQUEST['sku'],$_REQUEST['colour']);
	echo "<table>";
	echo "<tr><td colspan=2 align=right><button onclick=\"javascript:upld();\" >Save Match</button><input type=hidden id=method value=upload /></td></tr>";
	echo "</table>";
	
	
}


if ($method=="upload")
{
    #Call Photouploader - uses $_FILES
    $photo_update=photoUploader();
    
    
    $escDescr=mysqli_real_escape_string($db_conn,$_POST['description']);
    #Now deal with other data and update table
    $sql_query="update webDetails set photo='".$photo_update."', description=\""
        .$escDescr."\", sizeGroup='".$_POST['size_type']."'
    				, name=\"".$_POST['name']."\"
    		where 1=1
    		and   sku='".$_POST['sku']."'
    	    and  colour='".$_POST['colour']."'";	
    $do_it=$db_conn->query($sql_query);
    $return=1;

}


?>

<script type="text/javascript">
$(document).ready(function(){
	$('button').button();
});

function select()
{
	var brand=$('#brands').val();
	$('#results').load('./website/stockExport.php?action=list&brand='+brand);

}
</script>
