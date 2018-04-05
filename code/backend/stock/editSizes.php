<?php 

include '../config.php';
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="")
{
	echo "<h2>Search for a Size Key to Edit</h2>";
	#Must be searching for a size then
	echo "<table>";
	echo "<tr><td><input autocomplete=off onkeyup=\"javascript:srch(this.value);\" name=name></td><td><button onclick=\"javascript:select();\" >Add</button></td></tr>";
	echo "</table>";
	
	echo "<div id=searchresults>";

	$sql_query="select sizekey, sizekeydescription, size1, size2, size3, size4, size5, size6, size7, size8, size9, size10
			, size11, size12, size13, size14, size15, size16, size17, size18, size19, size20 from sizes where sizekeydescription like '%".$searchterm."%' limit 30";
	$results=$db_conn->query($sql_query);
	echo "<table  width=100%>";
	echo "<tr><td>Description</td><td colspan=20>Sizes</td></tr>";
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr onclick=\"javascript:select('".$result['sizekey']."');\" ><td>".$result['sizekeydescription']."</td>";
		for ($i=1;$i<=20;$i++)
		{
		    echo "<td>".$result['size'.$i]."</td>";
		}
		echo "</tr>";
	}

	echo "</table></div>";
	echo "<div id=updater_att></div>";
	echo "<div id=message_att></div>";
}
elseif ($action=="select")
{

	$term=$_REQUEST['term'];
	$sql_query="select sizekey, sizekeydescription, size1, size2, size3, size4, size5, size6, size7, size8, size9, size10
			, size11, size12, size13, size14, size15, size16, size17, size18, size19, size20 from sizes where sizekey = '".$term."'";
	
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);
}

elseif ($action=="save")
{
	if ($_REQUEST['term']=="undefined")
	{
		$setClause="'".$_REQUEST['sizekeydescription']."',\"".$_REQUEST['size1']."\",\"".$_REQUEST['size2']."\",\"".$_REQUEST['size3']."\",\"".$_REQUEST['size4']."\",\"".$_REQUEST['size5']."\"";
		$setClause.=",\"".$_REQUEST['size6']."\",\"".$_REQUEST['size7']."\",\"".$_REQUEST['size8']."\",\"".$_REQUEST['size9']."\",\"".$_REQUEST['size10']."\",\"".$_REQUEST['size11']."\",\"".$_REQUEST['size12']."\"";
		$setClause.=",\"".$_REQUEST['size13']."\",\"".$_REQUEST['size14']."\",\"".$_REQUEST['size15']."\",\"".$_REQUEST['size16']."\",\"".$_REQUEST['size17']."\",\"".$_REQUEST['size18']."\",\"".$_REQUEST['size19']."\",\"".$_REQUEST['size20']."\"";
	

		$sql_query="insert into sizes (sizekeydescription, size1, size2, size3, size4, size5, size6, size7, size8, size9, size10
			, size11, size12, size13, size14, size15, size16, size17, size18, size19, size20)
			values(".$setClause.")";
		$result=$db_conn->query($sql_query);
	}
	else
	{
		$setClause=" sizekeydescription='".$_REQUEST['sizekeydescription']."',size1=\"".$_REQUEST['size1']."\",size2=\"".$_REQUEST['size2']."\",size3=\"".$_REQUEST['size3']."\",size4=\"".$_REQUEST['size4']."\",size5=\"".$_REQUEST['size5']."\"";
		$setClause.=",size6=\"".$_REQUEST['size6']."\",size7=\"".$_REQUEST['size7']."\",size8=\"".$_REQUEST['size8']."\",size9=\"".$_REQUEST['size9']."\",size10=\"".$_REQUEST['size10']."\",size11=\"".$_REQUEST['size11']."\",size12=\"".$_REQUEST['size12']."\"";
		$setClause.=",size13=\"".$_REQUEST['size13']."\",size14=\"".$_REQUEST['size14']."\",size15=\"".$_REQUEST['size15']."\",size16=\"".$_REQUEST['size16']."\",size17=\"".$_REQUEST['size17']."\",size18=\"".$_REQUEST['size18']."\",size19=\"".$_REQUEST['size19']."\",size20=\"".$_REQUEST['size20']."\"";
		$sql_query="update sizes set ".$setClause." where sizekey='".$_REQUEST['term']."'";
		$result=$db_conn->query($sql_query);
	}
	
	echo "<p class=message>Size saved</p>";
}

elseif ($action=="delete")
{	
		$term=$_REQUEST['term'];
		$sql_query="delete from sizes where sizekey = '".$term."'";
	
		$do_it=$db_conn->query($sql_query);
		echo "<p class=message>Size Deleted</p>";
}


if ($action=="select" )
{
# Draw up table for record
echo "<table>";
echo "<h2>Size Record</h2>";
echo "<tr><td>Size Description</td><td><input type=text name=sizekeydescription value='".$detail['sizekeydescription']."' /></td></tr>";
echo "<tr><td>Size1</td><td><input  type=text name=size1 value='".$detail['size1']."' ></td></tr>";
echo "<tr><td>Size2</td><td><input  type=text name=size2 value='".$detail['size2']."' ></td></tr>";
echo "<tr><td>Size3</td><td><input  type=text name=size3 value='".$detail['size3']."' ></td></tr>";
echo "<tr><td>Size4</td><td><input  type=text name=size4 value='".$detail['size4']."' ></td></tr>";
echo "<tr><td>Size5</td><td><input  type=text name=size5 value='".$detail['size5']."' ></td></tr>";
echo "<tr><td>Size6</td><td><input  type=text name=size6 value='".$detail['size6']."' ></td></tr>";
echo "<tr><td>Size7</td><td><input  type=text name=size7 value='".$detail['size7']."' ></td></tr>";
echo "<tr><td>Size8</td><td><input  type=text name=size8 value='".$detail['size8']."' ></td></tr>";
echo "<tr><td>Size9</td><td><input  type=text name=size9 value='".$detail['size9']."' ></td></tr>";
echo "<tr><td>Size10</td><td><input  type=text name=size10 value='".$detail['size10']."' ></td></tr>";
echo "<tr><td>Size11</td><td><input  type=text name=size11 value='".$detail['size11']."' ></td></tr>";
echo "<tr><td>Size12</td><td><input  type=text name=size12 value='".$detail['size12']."' ></td></tr>";
echo "<tr><td>Size13</td><td><input  type=text name=size13 value='".$detail['size13']."' ></td></tr>";
echo "<tr><td>Size14</td><td><input  type=text name=size14 value='".$detail['size14']."' ></td></tr>";
echo "<tr><td>Size15</td><td><input  type=text name=size15 value='".$detail['size15']."' ></td></tr>";
echo "<tr><td>Size16</td><td><input  type=text name=size16 value='".$detail['size16']."' ></td></tr>";
echo "<tr><td>Size17</td><td><input  type=text name=size17 value='".$detail['size17']."' ></td></tr>";
echo "<tr><td>Size18</td><td><input  type=text name=size18 value='".$detail['size18']."' ></td></tr>";
echo "<tr><td>Size19</td><td><input  type=text name=size19 value='".$detail['size19']."' ></td></tr>";
echo "<tr><td>Size20</td><td><input  type=text name=size20 value='".$detail['size20']."' ></td></tr>";

echo "</table>";
echo "</div>";
echo "<input type=hidden name=term value='".$term."'>";
echo "<p width=100% align=right><button onclick=\"javascript:subForm();\" >Save</button><button onclick=\"javascript:delterm('".$_REQUEST['term']."');\" >Delete</button><button onclick=\"javascript:location.reload();\" >Close</button></p>";
}
?>
<script type="text/javascript">

$(document).ready(function(){
			$('button').button();
	});

function srch(value)
{
	$('#searchresults').show();
	var search=encodeURIComponent(value);
	$('#searchresults').load('./stock/editSizes.php?action=results&term='+search);
}

function select(value)
{
	var search2=encodeURIComponent(value);
	$('#updater_att').load('./stock/editSizes.php?action=select&term='+search2);
}

function delterm(term)
{
	if (term=="")
	{
		alert('No user searched for');
		exit();
	}
	var areyousure=confirm('Care should be taken in deleting sizes. They are used for old stock');
	if (areyousure==true)
	{
		$('#message_att').load('./stock/editSizes.php?action=delete&term='+term);
	}
	$('#output').load('./stock/editSizes.php');
	
}
function subForm()
{
	var getString="action=save&";
	$('input').each(function(){
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
	$('#message_att').load('./stock/editSizes.php?'+getString);
}

</script>
