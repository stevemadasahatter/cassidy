<?php

include '../config.php';

$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$db_conn2=mysqli_connect($db_host, $db_username, $db_password, $db_name);

#Drop table
echo "Dropping table\n";
$sql_query="drop table customerRank";
$results=$db_conn->query($sql_query);
echo "Table dropped";
#ceate new table
echo "Creating table again\n";
$sql_query="create table customerRank
     (custref int(6)
     , lifetimeTot decimal(10,2)
     , lifetimeRank int(6)
     , yearTot decimal(8,2)
     , yearRank int(6)
     , sixTot decimal(8,2)
     , sixRank int(6)
     )
    ";

$results=$db_conn->query($sql_query);
echo "Table created\n";
echo "Initial Lifetime total population started\n";
#Start with an initial populate
$sql_query="insert into customerRank (custref, lifetimeTot) select orderheader.custref, sum(tenders.PayValue)
from tenders, orderheader
where tenders.transno = orderheader.transno
group by orderheader.custref";

$results=$db_conn->query($sql_query);
echo "Initial population complete\n";
#Rank the customers lifetime
echo "Updating Lifetime Rank stats\n";
$sql_query="select custref from customerRank order by lifetimeTot desc";

$results=$db_conn->query($sql_query);
$i=1;
while ($customer=mysqli_fetch_array($results))
{
	if ($customer['custref']<>0)
	{	
		$sql_query2="update customerRank set lifetimeRank = ".$i." where custref=".$customer['custref'];
		$update=$db_conn2->query($sql_query2);
		$i++;
	}
}

echo "Lifetime Rank complete\n";
echo "Year Rank started\n";
$sql_query="select orderheader.custref, sum(tenders.PayValue) yeartot
from tenders, orderheader
where tenders.transno = orderheader.transno and orderheader.transDate > timestamp(date_sub(now(), interval 1 year)) group by orderheader.custref order by 2 desc ";
$results=$db_conn->query($sql_query);
$i=1;
while ($customer=mysqli_fetch_array($results))
{
	$sql_query2="update customerRank set yearTot = ".$customer['yeartot'].", yearRank=$i where custref = ".$customer['custref'];
	$update=$db_conn2->query($sql_query2);
	$i++;
}
echo "Year rank complete\n";

echo "6 Month rank started\n";
$sql_query="select orderheader.custref, sum(tenders.PayValue) yeartot
from tenders, orderheader
where tenders.transno = orderheader.transno and orderheader.transDate > timestamp(date_sub(now(), interval 6 month)) group by orderheader.custref order by 2 desc ";
$results=$db_conn->query($sql_query);
$i=1;
while ($customer=mysqli_fetch_array($results))
{
        $sql_query2="update customerRank set sixTot = ".$customer['yeartot'].", sixRank=$i where custref = ".$customer['custref'];
        $update=$db_conn2->query($sql_query2);
        $i++;
}
echo "All Done";
?>
