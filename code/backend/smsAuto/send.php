<html>
<body>
<table>
<?php
$grp=$_REQUEST['grp'];
$name=$_REQUEST['name'];
$txt=$_REQUEST['txt'];
$test=$_REQUEST['test'];
include 'IntelliSMS.php';

if ($grp=="")
{
	exit();
}

ob_start();

$db_conn=mysqli_connect('localhost', 'mailer', 'mailer', 'smsAuto');

$sql_query="select smsNumber from sendGroups where grpID=$grp";
$results=$db_conn->query($sql_query);
$num_rows=mysql_affected_rows($db_conn);

$j=0;
$i=0;
while ($result=mysqli_fetch_array($results))
{
	#Build the CSV list
	if ($j==0)
	{
		$number = str_replace(' ', '', $result['smsNumber']);
		$send_list[$i]=$number;
		$j++;
	}
	else
	{
		$number = str_replace(' ', '', $result['smsNumber']);
		$send_list[$i].=",".$number;
		$j++;
	}
	if ($test=="true")
	{
		echo "<tr><td>Would have sent $txt to ".$result['smsNumber']."</td></tr>";
	}
	if ($j>=100)
	{
		$j=0;
		$i++;
	}
}

$list_num=$i;
echo $list_num;
print_r($send_list);
$objIntelliSMS = new IntelliSMS();
$objIntelliSMS->Username = 'cocorose';
$objIntelliSMS->Password = 's4usages';

if ($test=="false")
	{
		#Send the real text
		for ($t=0;$t<=$list_num;$t++)
		{
		$output=$objIntelliSMS->SendMessage ( $send_list[$t], $txt, $name );
		echo $send_list[$t];
		print_r($output);
		}
	}
ob_flush();

$num_rows=mysqli_affected_rows($db_conn);
echo "<tr><td>".$num_rows." text messages sent</td></tr>";
?>
