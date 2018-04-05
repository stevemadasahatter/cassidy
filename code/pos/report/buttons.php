<?php
include '../config.php';
include '../functions/auth_func.php';

session_start();
$auth=check_auth();

if ($auth<>1)
{
        echo "<p>Please sign in<p>";
        exit();
}

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$sql_query="select Description, url, params, position from reportmenu order by position";
$results=$db_conn->query($sql_query);

$i=0;
echo "<p width=50% align=center>";
echo "<table>";
while ($menuitem=mysqli_fetch_array($results))
{
	if ($i % 2==0)
	{
		echo "<tr><td><button id='".$menuitem['url'].$menuitem['position']."'>".$menuitem['Description']."</button></td>";
	}
	else
	{
		echo "<td><button id='".$menuitem['url'].$menuitem['position']."'>".$menuitem['Description']."</button></td></tr>";

	}
	$javascript.="$('#".$menuitem['url'].$menuitem['position']."').click(function(){\$('#temp').load('./report/".$menuitem['url'].".php?".$menuitem['params']."'); $('#dimmer').show(); $('#dialog').show(); });\n";
	$i++;
}
echo "</table>";
?>

<script type=text/javascript>
        $('#dialog').append('<div id=temp></div>');
        $('#dialog').css('top','20%');
        $('#dialog').css('left','50%');
        $('#dialog').css('margin-left','-30%');
        $('#dialog').css('width:280px');

<?php
echo $javascript;
?>
$(document).ready(function(){
		$('button').button();
});
</script>
