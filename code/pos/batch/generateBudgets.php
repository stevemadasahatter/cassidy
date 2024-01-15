<?php

include '../config.php';
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

#we can only do stats until today, we can only project 1 year ahead
#Start in 2005 because nothing existed prior which was dependable

#Generate Time dimension look up
	echo "Time Dimension\n";
	echo "--------------\n";

	echo "==> Drop Table\n";

	$sql_query="drop table reporting.timedim";
	$doit=$db_conn->query($sql_query);

	echo "==> Table dropped\n";
	echo "==> Recreate table\n";
	$sql_query="create table reporting.timedim select transDate realdate,year(transDate) yr, week(transDate) wk,weekday(transDate) wkd, concat(year(transDate), week(transDate), weekday(transDate)) keyval from orderheader group by year(transDate), week(transDate), weekday(transDate)";	
	$doit=$db_conn->query($sql_query);
	echo "==> Table recreate complete\n";

echo "Budget calcs\n";
echo "------------\n";

echo "==> Clear table\n";

$sql_query="delete from reporting.budgets";
$doit=$db_conn->query($sql_query);

echo "==> Table cleared\n";
echo "==> Population\n";
for ($i=2008;$i<=2020;$i++)
{
	echo "====> Populate for year $i\n";
	$sql_query="insert into reporting.budgets
select budgets.company, budgets.keyvalue, actuals.PayValue actual, budgets.PayValue budget
from
(select avgs.company, concat('$i',avgs.keyvalue) keyvalue, avgs.PayValue PayValue
from 
(select sums.company company,concat(sums.wk,sums.wkd) keyvalue, avg(sums.PayValue) PayValue
from
(select year(transDate) yr, week(transDate) wk,weekday(transDate) wkd, sum(PayValue) PayValue, company 
from tenders group by year(transDate), week(transDate) ,weekday(transDate)
) sums
where sums.yr >=".($i-3)."
and sums.yr <=".($i-1)."
group by sums.company,sums.wk, sums.wkd
) avgs) budgets,
(select company, concat(year(transDate),week(transDate),weekday(transDate)) keyvalue, sum(PayValue) PayValue 
from tenders where year(transDate) = $i group by concat(year(transDate),week(transDate),weekday(transDate))
) actuals
where 1=1
and actuals.company=budgets.company
and actuals.keyvalue=budgets.keyvalue
	";
	$doit=$db_conn->query($sql_query);

}
?>
