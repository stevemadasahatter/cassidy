<?php

#Filesystems OK?
$fsstatus=0; # Green
$status=0;
$title="";
ob_start();
passthru("./fschecks.sh");
$commandout=ob_get_clean();
$filesystems=explode("\n",$commandout);
foreach ($filesystems as $filesystem)
{
	if ($filesystem<>"")
	{
	$fs=explode(",",$filesystem);
	$percent=explode("%",$fs[1]);
	if ($percent[0]<90)
	{
	    #Alert condition (green)
	    $fsstatus=0;
	    $title.=$fs[0]." is ".$percent[0]."% full (GREEN)<br>";
	}
	elseif ($percent[0]>90 and $percent[0]<95)
	{
		#Alert condition (amber)
		$fsstatus=1;
		$title.=$fs[0]." is ".$percent[0]."% full (AMBER)<br>";
	}
	elseif ($percent[0]>95)
	{
		#Red alert
		$fsstatus=2;
		$title.=$fs[0]." is ".$percent[0]."% full (RED)<br>";
	}
	}

}


	echo $title."<br>";

#Apache check

$apccount=exec('./apcstatus.sh');
if ($apccount==0)
{
	echo "Apache status is DOWN!<br>";
}

else 
{
	echo "Apache status is UP<br>";
}

# Database up
$db_conn=mysqli_connect('localhost','till','secure','till') or die ($dbstatus=2);

$sql_query="select count(*) from colours";
$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);
$dbstatus=0;
if ($result[0]<=0)
{
	$dbstatus=2;
}

if ($dbstatus==2)
{
	echo "Database is down<br>";
}
else
{
	echo "Database is UP<br>";
}
# Internet connectivity




?>
