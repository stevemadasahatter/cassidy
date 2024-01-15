<?php
include '../config.php';
include '../functions/auth_func.php';
session_start();
$auth=check_auth();
$action=$_REQUEST['action'];
if ($auth<>1)
{
	exit();
}

if ($action=="min")
{
	echo "<p width=100% align=right><img onclick=\"javascript:minMenu('max');\" src=./images/arrow.jpg />&nbsp;&nbsp;</p>";
	exit();
}
else
{
	echo "<p width=100% align=right><img onclick=\"javascript:minMenu('min');\" src=./images/arrow.jpg />&nbsp;&nbsp;</p>";
}

$db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);
$sql_query="select parentmenu.name parent, parentmenu.id, childmenu.name child, childmenu.url from parentmenu, childmenu, menustruct 
		where menustruct.parentID = parentmenu.id and menustruct.childID=childmenu.id and menustruct.active=1
		and childmenu.level<=".$_SESSION['LEVEL']."
		order by parentmenu.order, childmenu.menuorder asc";
$results=$db_conn->query($sql_query);

$top=0;
while ($menuitem=mysqli_fetch_array($results))
{
	if ($top==$menuitem['id'])
	{
		echo "<li class=child ><a href=# onclick=\"javascript:$('#output').load('".$menuitem['url']."');\" >".$menuitem['child']."</a></li>";
	}
	else 
	{
		if ($top<>0)
		{
			echo "</div></ul>";
		}
		echo "<ul><li onclick=\"javascript:chgMenu('menu".$menuitem['id']."');\" class=parent>".$menuitem['parent']."</li><div id=menu".$menuitem['id']." style=\"display:none;\">";
		echo "<li class=child ><a href=# onclick=\"javascript:$('#output').load('".$menuitem['url']."');\" >".$menuitem['child']."</a></li>";
		$top=$menuitem['id'];
	}
}
echo "</ul>";
?>

<script type="text/javascript">
function chgMenu(menu)
{
	$('#'+menu).slideToggle('fast');
}

function minMenu(type)
{
	if (type=='min')
	{
		$('#menu').css('width','5%');
		$('#menu').load('./menu/menu.php?action=min');
		$('#output').css('width','90%');
	}
	else
	{
		$('#menu').css('width','18%');
		$('#menu').load('./menu/menu.php?action=max');
		$('#output').css('width','78%');
		
	}
}
</script>
