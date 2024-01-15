
<?php

include '../config.php';
include '../functions/auth_func.php';
session_start();

echo <<<EOF
<script>
$(document).ready(function(){			
			 $("#dialog-confirm").dialog({
	        autoOpen: false,
	        modal: true
	      });
});
		
function displayPetty()
{
$('#dialog-confirm').html('<p>Please enter the Float starting value for today</p>');

$("#dialog-confirm" ).dialog({
    title : "Petty Cash",
    resizable: false,
    height:280,
    autoOpen: true,
    modal: false,
    buttons: {
      "OK": function() {
        $( this ).dialog( "close" );
      },        
    }
  }).parent('.ui-dialog').css('zIndex',999999);
}

</script>
EOF;
$db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);


## Logout code
if ($_REQUEST['action']=='logout')
{
	$auth=deauthenticate();
	 echo "<script type=text/javascript>location.reload(); </script>";
}
$till=$_COOKIE['tillIdent'];
$auth="No";

# Check till is in a company and authorised
$company=getTillCompany($till);

if (!$company)
{
	echo "<script>alert('This Till $sql_query (".$_COOKIE['tillIdent'].") is not authorised');</script>";
	exit();
}

if ($_REQUEST['action']=="typing")
{
        $auth=authenticate($_REQUEST['username'], $_REQUEST['password'], $company, $till);
        if ($auth<>0)
        {
       		echo "<script type=text/javascript>$('#password').val('');</script>";
                echo "<p>Password incorrect</p>";
                exit();
                
        }
	else 
	{

           	echo "<script type=text/javascript>location.reload(); </script>";
         	exit();
	}

}

if (!$_SESSION['POS'] && $_REQUEST['action']<>"login" && $_REQUEST['action']<>'typing')
{

        echo "<table width=100%><tr><td>Not logged in</td><td align=right><button onclick=\"javascript:login();\" >Login</button></td></tr></table>";
}
elseif($_SESSION['POS'] && $_REQUEST['action']== "login" && $_REQUEST['action']<>'typing' )
{
	session_destroy();
	echo "<script>javascript:location.reload();</script>";	
}
else
{
        $username=$_SESSION['POS'];
        $auth=check_auth();
        if ($auth==1)
        {
                unset($result);
                $sql_query="select forename, lastname from users where username='".$username."' and active =1";
                $result=$db_conn->query($sql_query);
                $results=mysqli_fetch_array($result);
                echo "<table width=100%><tr><td class=signame>".$results['forename']." ".$results['lastname']."</td><td align=right>";
                echo "<button onclick=\"javascript:report();\" >Reports</button>";
                echo "<button onclick=\"javascript:logout();\" >Logout</button></td></tr></table>";
                
                $till=$_COOKIE['tillIdent'];
                $tillsession=getTillSession($till);
                
                $startval=getPettyCash($till);
                if ($startval['startval']=="" || $startval['startval']==0)
                {
                	echo "<script>javascript:displayPetty();</script>";
                }

        }
        else 
        {
        	if ($_SESSION['POS'])
        	{
        		session_destroy();
        		echo "<script>javascript:location.reload();</script>";
        	}
        }


}


if ($_REQUEST['action']=="login")
{
	#login page
	echo "<table width=100%>";
	echo "<tr>";
	$sql_query="select username from users where active =1";
	$results=$db_conn->query($sql_query);
	$names[]="";
	$i=0;
	while ($result=mysqli_fetch_array($results))
	{
                $buttons.="<td style=\"border:1px solid #eee;text-align:center;width:60px;\" onclick=\"javascript:selUser('".$result['username']."');\"><p class=icon>".strtoupper(substr($result['username'],0,1))."</p><p>".$result['username']."</p></td>";
	}
	echo $buttons."</tr>";
	
	echo "<tr><td colspan=12><div id=status></div></td></tr><tr><td colspan=10>";
	echo "<tr><td colspan=12 align=center><input type=password autocomplete=off id=numBox onkeyup=\"javascript:send();\"><input type=hidden id=username value='' /></input></td></tr>";
	echo <<<EOF
<table id="keypad" style="margin-left:auto;margin-right:auto;" >
<tr>
<td class="key" onclick="javascript:keypad('1');">1</td>
<td class="key" onclick="javascript:keypad('2');">2</td>
<td class="key" onclick="javascript:keypad('3');">3</td>
</tr>
<tr>
<td class="key" onclick="javascript:keypad('4');">4</td>
<td class="key" onclick="javascript:keypad('5');">5</td>
<td class="key" onclick="javascript:keypad('6');">6</td>
</tr>
<tr>
<td class="key" onclick="javascript:keypad('7');">7</td>
<td class="key" onclick="javascript:keypad('8');">8</td>
<td class="key" onclick="javascript:keypad('9');">9</td>
</tr>
<tr>
<td class="btn" onclick="javascript:keypadspc('del');">DEL</td>
<td class="key" onclick="javascript:keypad('0');">0</td>
<td class="btn" onclick="javascript:keypadspc('clr');">CLR</td>
</tr>
</table>
</td></tr>
EOF;
	$size=getTillType();
	echo "</table>";
	echo "<table style=\"margin-left:auto;margin-right:auto;\" width=100%><tr><td align=center><img align=center src=./images/".$size['size']."-logo.png /></td></tr></table>";
}

if ($_REQUEST['action']=="logout")
{
	deauthenticate();
	clearReadout();
	echo "<script>javascript:location.reload();</script>";
	exit();
}

?>
<script type="text/javascript">
function login() 
{        
		$('#temp').remove();
         $('#dialog').append('<div id=temp></div>');
         $('#temp').load('./auth/login.php?action=login', function(){
		var wid=$('#dialog').width();
                 $('#dialog').css('left','50%');
                 $('#dialog').css('margin-left',wid/2*-1);
		$('#dialog').css('border-radius','25px');
		 $('#dimmer').show();
	});
         $('#dialog').show();
}

function send()
{
	var username= $('#username').val();
	var password= $('#numBox').val();
	var leng=password.length;
	if (leng==4)
	{
		$('#status').load('./auth/login.php?action=typing&username='+username+'&password='+password);
	}
    
}

function selUser(username)
{
	$('#username').val(username);
	$('#numBox').val('');
	$('#numBox').focus();
	$('#keypad').show();

}


function logout()
{
	$('#signin').load('./auth/login.php?action=logout');
}

function report()
{
	$('#dialog').append('<div id=temp></div>');
	$('#temp').load('./report/dashboard.php');
	$('#dimmer').show();
	$('#dialog').show();
}

$(document).ready(function(){
	$('button').button();
    $('#numBox').click(function(){
        $('#keypad').fadeToggle('fast');
});
});
    
    
function keypad(button)
{

	$('#numBox').val($('#numBox').val() +  button);
        
    	var username= $('#username').val();
    	var password= $('#numBox').val();
    	var leng=password.length;
    	if (leng==4)
    	{
    		$('#status').load('./auth/login.php?action=typing&username='+username+'&password='+password);
    	}
}
    
 function keypadspc(type)
 {
        if(type == 'del')
        {
            if($('#numBox').val().length > 0)
            {
            		$('#numBox').val($('#numBox').val().substring(0, $('#numBox').val().length - 1));
            }
        }
        if(type == 'clr')
        {
            $('#numBox').val('');
        }
}

</script>
