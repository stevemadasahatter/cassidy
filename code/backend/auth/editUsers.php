<?php 

include '../config.php';
include '../functions/field_func.php';
session_start();
$action=$_REQUEST['action'];
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action=="")
{
	echo "<h2>Search for user to Edit</h2>";
	#Must be searching for a user then
	echo "<table>";
	echo "<tr><td><input autocomplete=off onkeyup=\"javascript:srch(this.value);\" name=name></td><td><button onclick=\"javascript:select();\" >Add</button></td></tr>";
	echo "</table>";
	
	echo "<div id=searchresults></div>";
	echo "<div id=updater></div>";
	echo "<div id=message></div>";

}
elseif ($action=="results")
{
	$searchterm=$_REQUEST['term'];
	if ($searchterm=="")
	{
		exit();
	}
	$sql_query="select username, forename, lastname from users where concat(forename,' ',lastname) like '%".$searchterm."%' or username like '%".$searchterm."%'";
	$results=$db_conn->query($sql_query);
	
	echo "<table>";
	echo "<tr><th>Username</th><th>Forename</th><th>Last Name</th></tr>";
	while ($result=mysqli_fetch_array($results))
	{
		echo "<tr onclick=\"javascript:select('".$result['username']."');\" ><td>".$result['username']."</td>";
		echo "<td>".$result['forename']."</td>";
		echo "<td>".$result['lastname']."</td>";
		echo "</tr>";
	}
}
elseif ($action=="select")
{

	$term=$_REQUEST['term'];
	$sql_query="select username, forename, lastname, active, level, multi from users where username = '$term'";
	
	$details=$db_conn->query($sql_query);
	$detail=mysqli_fetch_array($details);
}

elseif ($action=="save")
{
	if ($_REQUEST['term']=="undefined")
	{
		$setClause="'".$_REQUEST['username']."',\"".$_REQUEST['forename']."\",\"".$_REQUEST['lastname']."\",\"".$_REQUEST['level']."\",\""
				.$_REQUEST['active']."\",\"".$_REQUEST['multi']."\",\"".$_REQUEST['passwd']."\"";
	

		$sql_query="insert into users (username, forename, lastname, level, active, multi,password)
			values(".$setClause.")";
		$result=$db_conn->query($sql_query);
	}
	else
	{
		$setClause=" username='".$_REQUEST['username']."',forename=\"".$_REQUEST['forename']."\",lastname=\"".$_REQUEST['lastname']."\",level=\""
				.$_REQUEST['level']."\",active=";
		$setClause.=$_REQUEST['active'].",multi=\"".$_REQUEST['multi']."\"";
		if ($_REQUEST['passwd']<>"")
		{
			$setClause.=",password=md5(".$_REQUEST['passwd'].")";
		}
		$sql_query="update users set ".$setClause." where username='".$_REQUEST['term']."'";
		$result=$db_conn->query($sql_query);
	}
	
	echo "<p class=message>User saved</p>";
		
}

elseif ($action=="delete")
{	
		$term=$_REQUEST['term'];
		$sql_query="delete from users where username = '".$term."'";
	
		$do_it=$db_conn->query($sql_query);
		echo "<p class=message>User Deleted</p>";
}


if ($action=="select" )
{
# Draw up table for record
echo "<table>";
echo "<h2>User Record</h2>";
echo "<tr><td>Username</td><td><input type=text name=username value='".$detail['username']."' /></td></tr>";
echo "<tr><td>First Name</td><td><input  type=text name=forename value=".$detail['forename']." ></td></tr>";
echo "<tr><td>Last Name</td><td><input  type=text name=lastname value=".$detail['lastname']." ></td></tr>";
echo "<tr><td>Password</td><td><input  type=password name=passwd ></td></tr>";
echo "<tr><td>Level</td>
		<td><select name=level><option value=".$detail['level']." selected>".$detail['level']."</option><option value=1>1</option>
<option value=2>2</option><option value=3>3</option></select></td></tr>";

echo "<tr><td>Active</td><td><select name=active><option value=".$detail['active']." selected>".$detail['active']."</option><option value=0>0</option><option value=1>1</option></select></td></tr>";
echo "<tr><td>Company</td><td><select name=multi>";
$companies=getSelect('companies',$detail['multi']);
echo $companies;
echo "</select></td></tr>";

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
	$('#searchresults').load('./auth/editUsers.php?action=results&term='+search);
}

function select(value)
{
	$('#searchresults').hide();
	var search2=encodeURIComponent(value);
	$('#updater').load('./auth/editUsers.php?action=select&term='+search2);
}

function delterm(term)
{
	if (term=="")
	{
		alert('No user searched for');
		exit();
	}
	$('#message').load('./auth/editUsers.php?action=delete&term='+term);
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
	$('#message').load('./auth/editUsers.php?'+getString);
}

</script>
