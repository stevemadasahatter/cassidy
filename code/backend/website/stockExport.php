<?php

include '../config.php';
include './config.php';
include '../functions/auth_func.php';
include '../functions/stock_func.php';
include '../functions/web_func.php';

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$action=$_REQUEST['action'];
$method=$_POST['method'];

if ($action=="" && $method=="")
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
			  and sd.category = cat.id) stk
	left join webDetails on stk.Stockref = webDetails.sku
            and stk.colour = webDetails.colour";
	$results=$db_conn->query($sql_query);
	
	echo "<table><tr><th>SKU</th><th>Colour Variant</th><th>Brand</th><th>Season</th><th>Match</th><th>Complete</th></tr>";
	
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr><td>".$result['Stockref']."</td><td>".$result['colour']."</td><td>".$result['brand']."</td>";
		echo "<td>".$result['season']."</td><td><a href='#' onclick=\"javascript:editWeb('".$result['Stockref']."'
				,'".$result['colour']."','".$result['brand']."','".$result['seas']."','".$result['category']."');\">";
			if ($result['mag_rec']<>'')
			{
				echo "Re-Match Product</a></td>";
				if ($result['mag_cats']<>"")
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
	       echo "<script type=text/javascript>$('#output').load('./website/stockExport.php');</script>";
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

	
	$colours=getAttribArray($_REQUEST['colour'], '76', 'colid');
	$brands=getAttribArray($_REQUEST['brand'], '66', 'brand');
	$seasons=getAttribArray($_REQUEST['season'], '556', 'season');	
	$categories=getAttribArray($_REQUEST['category'], '497', 'category');

	echo "<h2>Web Details Entry Form</h2>";
	
	echo "<table>";
	echo "<tr><td>SKU is ".$_REQUEST['sku']." and colour is ".$_REQUEST['colour']."</td><input type=hidden id=sku value='".$_REQUEST['sku']."' >
				<input type=hidden id=colour value='".$_REQUEST['colour']."' /></tr>";
	echo "<tr><td>Product Name</td><td><input type=text id=prodname value=\"".$result['name']."\"></input></td></tr>";
	echo "<tr><th colspan=2>Description</th></tr>";
	echo "<tr><td colspan=2><textarea style=\"width:600px;height:150px;\" id=description>".$result['description']."</textarea></td></tr>";
	echo "<tr><td>Colour Match</td><td>$colours</td></tr>";
	echo "<tr><td>Brand Match</td><td>$brands</td></tr>";
	echo "<tr><td>Category Match</td><td>$categories</td></tr>";
	echo "<tr><td>Season Match</td><td>$seasons</td></tr>";
	echo "<tr><td>Sizing Type</td><td>";
	echo "<select id=size_type><option selected>[Select]</option>";
    echo "<option value=503>Dress Sizes (8/10/12)</option>";
	echo "<option value=498>Shoes (36/37/38)</option>";
	echo "<option value=519>Clothes Sizes (S/M/L)</option>";
	echo "<option value=558>Trouser Sizes (28/29/30)</option>";
	echo "<option value=580>Mac Trousers (12/12L/14/14L)</option>";
	echo "<option value=111>No Size (ANY)</option>";
	echo "</select></td></tr>";
	$category_setup=file_get_contents('http://www.cocorose.co.uk/shopfront/mag_cats.php');
	echo "<tr><td><div id=catlist>$category_setup</div></td></tr>";
	#Start of photos
	echo "<tr><td colspan=2>Item photos</td></tr>";
	echo "<tr><td><input type=file id=photo1 /></td><td><input type=file id=photo2 /></td></tr>";
	echo "<tr><td><input type=file id=photo3 /></td><td><input type=file id=photo4 /></td></tr>";
	echo "<tr><td><input type=file id=photo5 /></td><td><input type=file id=photo6 /></td></tr>";

	

	
	echo "<tr><td colspan=2 align=right><button onclick=\"javascript:upld();\" >Save Match</button><input type=hidden id=method value=upload /></td></tr>";
	echo "</table>";


}


if ($method=="upload")
{
	print_r($_FILES);
		# Fix up the photos first
		$error=0;
		for ($i=1;$i<=6;$i++)
		{
			if ($_FILES['photo'.$i]['error']>0)
			{
				print_r($_FILES);
				$error=1;
			}
		}
		if ($error==0)
		{
		   $photo_update="";
        	   for ($i=1;$i<=6;$i++)
            {
                	if ($_FILES['photo'.$i])
                	{
                		print_r($_FILES['photo'.$i]);
                		echo substr($_FILES['photo'.$i]['name'],strlen($_FILES['photo'.$i]['name'])-6);
                		$extension=split("\.",substr($_FILES['photo'.$i]['name'],strlen($_FILES['photo'.$i]['name'])-6));
                		
                    	move_uploaded_file($_FILES['photo'.$i]['tmp_name'], $pics_path."/".urlencode($_POST['sku'])."-".urlencode($_POST['colour'])."-".$i.".".$extension[1]);
                    	$photo_update.=urlencode($_POST['sku'])."-".urlencode($_POST['colour'])."-".$i.".".$extension[1]."|";
                	}
            }
        }
        
        #Now deal with other data and update table
        $sql_query="update webDetails set photo='".$photo_update."', description=\""
        		.$_POST['description']."\", brand='".$_POST['brand']."', season='".$_POST['season']."', sizeGroup='".$_POST['size_type']."'
        				, categories='".$_POST['cats']."', colid='".$_POST['colid']."', categorytype='".$_POST['category']."', name=\"".$_POST['name']."\"
        		where 1=1
        		and   sku='".$_POST['sku']."'
        	    and  colour='".$_POST['colour']."'";
        		
        
        $do_it=$db_conn->query($sql_query);
        echo $sql_query;
        $return=1;

}


?>

<script type="text/javascript">
function editWeb(sku, colour, brand, season, category)
{
	 var brandurl=encodeURIComponent(brand);
	 var caturl=encodeURIComponent(category);
	$('#output').load('./website/stockExport.php?action=load&sku='+sku+'&colour='+colour+'&brand='+brandurl+'&season='+season+'&category='+caturl);

}

function completeWeb(sku, colour)
{

	$('#dialog').load('./website/stockExport.php?action=complete&sku='+sku+'&colour='+colour);
}

function upld()
{
        var fd= new FormData();
        fd.append('photo1',document.getElementById("photo1").files[0]);
        fd.append('photo2',document.getElementById("photo2").files[0]);
        fd.append('photo3',document.getElementById("photo3").files[0]);
        fd.append('photo4',document.getElementById("photo4").files[0]);
        fd.append('photo5',document.getElementById("photo5").files[0]);
        fd.append('photo6',document.getElementById("photo6").files[0]);
        fd.append('description',$("#description").val());
        fd.append('sku',$("#sku").val());
        fd.append('colour',$("#colour").val());
        fd.append('brand',$("#brand").val());
        fd.append('season',$("#season").val());
        fd.append('category',$("#category").val());
        fd.append('method',$("#method").val());
        fd.append('name',$("#prodname").val());
		fd.append('colid',$("#colid").val());
        fd.append('size_type',$("#size_type").val());
        var cats="";
        $('#catlist').find('input[type="checkbox"]').each(function(){
            	if ($(this).is(':checked'))
            	{
            		cats=cats+($(this).val()+',');
            	}
        });
        fd.append('cats',cats);
        $.ajax({
                url:'./website/stockExport.php',
                type:'POST',
                data:fd,
                processData: false,
                contentType:false,
                success : function(){
                        $('#output').load('./website/stockExport.php');
                }
        });
}


</script>
