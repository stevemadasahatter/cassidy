<?php

include '../config.php';
include '../functions/auth_func.php';

session_start();
$auth=check_auth();

if ($auth<>1)
{
        exit();
}
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
$sql_query="select status from orderheader where transno = ".$_SESSION['orderno'];
$results=$db_conn->query($sql_query);
$result=mysqli_fetch_array($results);
if ($result['status']=='C')
{
	$disabled="";
}
else
{
	$disabled="disabled";
}

echo "<table width=100%><tr><td align=right>
	<button id=cancel>Clear</button>
	<button id=complete>Tender</button></td></tr></table>";

echo "<div id=dialog-confirm title=\"Cancel Sale?\">";
echo "<p><span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 20px 0;\"></span>The sale will be cleared. Are you sure?</p></div>";

?>

<script type="text/javascript">

	$(document).ready(function(){
		$('button').button();
	    $("#dialog-confirm").dialog({
	        autoOpen: false,
	        modal: true
	      });
	});

	$('#cancel').click(function(){
		$('#dialog').append('<div id=temp></div>');

	    $("#dialog-confirm" ).dialog({
	        resizable: false,
	        height:180,
	        autoOpen: true,
	        modal: true,
	        buttons: {
	          "Yes": function() {
	  			$('#temp').load('./order/processOrder.php?action=cancel');
				$('#dialog').css('top','0%');
				 $('#dialog').css('left','50%');
				 $('#dialog').css('margin-left','-38%');
				 $('#dimmer').show();
				 $('#dialog').show();
	            $( this ).dialog( "close" );
	          },
	          "No": function() {
	            $( this ).dialog( "close" );
	          }
	        }
	      });
	});

	$('#complete').click(function(){
		 $('#dialog').append('<div id=temp></div>');
		 $('#dialog').css('top','0%');
		 $('#dialog').css('left','50%');
		 $('#dialog').css('margin-left','-38%');
         $('#temp').load('./order/processOrder.php?action=complete');
	 	$('#dimmer').show();
         $('#dialog').show();

	});	
</script>
