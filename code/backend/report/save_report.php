<?php
include "../config.php";
#Parse the post so we can save it

$action=$_REQUEST['action'];

if ($action=="display")
{
	foreach ($_REQUEST as $key=>$value)
	{
		if ($value<>"" && $key<>'datein' && $key<>'dateout')
		{
			$fileoutput.="$".$key."='".$value."';\n";
		}
	
	}
	echo "<h2>Readying Report for save</h2>";
	echo "<p>Saved reports need to have relative dates. Select the date you wish to be used</p>";
	echo "<table><tr><td>During today</td><td><input type=radio id=dates value=now></input></td></tr>";
	echo "<tr><td>This week</td><td><input type=radio id=dates value=week></input></td></tr>";
	echo "<tr><td>This month</td><td><input type=radio id=dates value=month></input></td></tr>";
	echo "<tr><td>This year</td><td><input type=radio id=dates value=year></input></td></tr>";
	echo "<tr><td>Beginning of time</td><td><input type=radio  id=dates value=all></input></td></tr>";
	echo "<tr><td>Name of report</td><td><input type=text  id=title></input></td></tr>";
	echo "<tr><td></td><td><button onclick=javascript:cancel(); >Cancel</button><input type=hidden id=uri value=\"$fileoutput\" />";
	echo "<button onclick=javascript:submit(); >Submit</button><input type=hidden id=uri value=\"$fileoutput\" /></td></tr>";
	echo "</table>";
}

if ($action=="save")
{
	if($_REQUEST['dates']=="now")
	{
		$datein=" date_sub(now(), interval 1 day) ";
	}
	elseif ($_REQUEST['dates']=="week")
	{
		$datein=" date_sub(now(), interval dayofweek(now()) DAY ";
	}
	elseif ($_REQUEST['dates']=="month")
	{
		$datein=" date_sub(now(), interval dayofmonth(now()) day ";
	}
	elseif ($_REQUEST['dates']=="year")
	{
		$datein=" date_sub(now(), interval dayofyear(now()) day ";
	}
	elseif ($_REQUEST['dates']=="all")
	{
		$datein=" STR_TO_DATE('01/01/2000 00:00:00', '%m/%d/%Y %H:%i:%s') ";
	}
	$fileoutput2="<?php\n";
	$dateout=" now() ";
	foreach ($_REQUEST as $key=>$value)
	{
		if ($value!="" && $key!="datein" && $key!="dateout" && $key!="action" && $key!="fileoutput" && $key!="dates")
		{
			$enc=urlencode($value);
			$fileoutput2.="$".$key."=\"".$enc."\";\n";
		}
	
	}
	
	$enc=urlencode($dateout);
	$fileoutput2.="$"."dateout=\"".$enc."\";\n";
	
	$enc=urlencode($datein);
	$fileoutput2.="$"."datein=\"".$enc."\";\n";
	$fileoutput2.=$_REQUEST['fileoutput'];
	#make up filename
	$filename=date('DmYHMs').".dfn";
	$success=file_put_contents($report_save."/".$filename, $fileoutput2);
}

echo $fileoutput;
?>

<script type=text/javascript>
$(document).ready(function(){
	$('button').button();
});

function submit(){
	var getString="";
	var dates=$('input:radio[id=dates]:checked').val();
	var title=$('#title').val();
	var name=encodeURI(title);
	getString=getString+'dates='+dates+'&';
	getString=getString+'fileoutput='+$('#uri').val()+'&';
	getString=getString+'name='+name+'&';
	getString=getString+'action=save&';
	$('#temp').load('./report/save_report.php?'+getString);
};

function cancel()
{
	$('#temp').remove();
	$('#dimmer').hide();
}

</script>