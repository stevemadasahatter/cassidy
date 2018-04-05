<script type="text/javascript">

$('#password').keypress(function (e) {
	  if (e.which == 13) {
	    $('#loginbut').click();
	    return false;    //<---- Add this line
	  }
});

function login()
{
         $('#dialog').append('<div id=temp></div>');
                 $('#dialog').css('top','20%');
                 $('#dialog').css('left','45%');
                 $('#dialog').css('margin-left','-14%');	
		 $('#password').focus();
         $('#temp').load('./auth/login.php?action=login');
         $('#dimmer').show();
         $('#dialog').show();
}

function send(username,co)
{
        var password= $('#password').val();
        $('#passwd').load('./auth/login.php?action=logmein&username='+username+'&password='+password+'&co='+co);
}

function logout()
{
        $('#login').load('../auth/login.php?action=logout');
}

$(document).ready(function(){
	 $('#password').focus();
        $('button').button();
        });

function selectco(username)
{
	 $('#password').focus();
	$('#selectco').load('./auth/login.php?action=selectco&username='+username);
}

function passwd(username,co)
{
	 $('#password').focus();
	$('#passwd').load('./auth/login.php?action=passwd&username='+username+'&company='+co);
}
</script>
<?php

include '../config.php';
include '../functions/auth_func.php';
session_start();

if ($_REQUEST['action']=="check")
{
    if ($_SESSION['CO']<>'')
    {
        $_SESSION['CO'] = $_SESSION['CO'];
        echo "Session";
        exit();
    }
    else {
        echo "No session";
        $auth=deauthenticate();
        $_REQUEST['action']="logout";
    }
}



if ($_REQUEST['action']=='logout')
{
	$auth=deauthenticate();
	 echo "<script type=text/javascript>location.reload(); </script>";
	exit();
}

$db_conn=mysqli_connect($db_host,$db_username, $db_password, $db_name);

if ($_REQUEST['action']=="logmein")
{

	$auth=authenticate($_REQUEST['username'], $_REQUEST['password'], $_REQUEST['co']);
	if ($auth<>0)
	{
		echo "<script type=text/javascript>alert('Incorrect Password'); location.reload(); </script>";	
	}
	echo "<script type=text/javascript>location.reload(); </script>";
	exit();
}
if (!$_SESSION['BE'] && $_REQUEST['action']=="")
{
	echo "<script type=text/javascript>login();</script>";
}

elseif ($_SESSION['BE'] && $_REQUEST['action']=="")
{
        $username=$_SESSION['BE'];
        $auth=check_auth();
        if ($auth==1)
        {
                unset($result);
                $sql_query="select forename, lastname from users where username='".$username."' and active =1";
                $result=$db_conn->query($sql_query);
                $results=mysqli_fetch_array($result);
                echo "<table width=100%><tr><td class=signame>".$results['forename']." ".$results['lastname']."</td><td align=right><a href=\"#\" onclick=\"javascript:logout();\" >Logout</a></td></tr></table>";
        }
        else
        {
        	session_destroy();
        	echo "<script>javascript:location.reload();</script>";
        }
}


if ($_REQUEST['action']=="login")
{
	#login page
	echo "<table width=100%>";
	echo "<tr><td align=center><h2>Cassidy</h2></td></tr>";
	echo "<tr><td align=left>Select User</td></tr>";
	echo "</table><ul>";
	$sql_query="select username, multi from users where active=1";
	$results=$db_conn->query($sql_query);
	while ($result=mysqli_fetch_array($results))
	{
		if ($result['multi']==0)
		{
			echo "<li onclick=\"javascript:selectco('".$result['username']."');\">";
			echo "<table class=users>";
			echo "<tr><td><img class=logimg src=./images/multi.png /></td></tr>";
			echo "<tr><td align=center>".$result['username']."</td></tr>";
			echo "</table>";
			echo "</li>";
		}
		elseif($result['multi']==1)
		{
                        echo "<li onclick=\"javascript:passwd('".$result['username']."',1);\">";
                       	echo "<table  class=users>";
                        echo "<tr><td><img  class=logimg src=./images/heart.jpg /></td></tr>";
                        echo "<tr><td  align=center>".$result['username']."</td></tr>";
			echo "</table>";
                        echo "</li>";
		}
		elseif($result['multi']==2)
		{
                        echo "<li onclick=\"javascript:passwd('".$result['username']."',2);\">";
						echo "<table class=users>";
                        echo "<tr><td><img  class=logimg  src=./images/leaves.png /></td></tr>";
                        echo "<tr><td align=center>".$result['username']."</td></tr>";
						echo "</table>";
                        echo "</li>";
		}
	}
	echo "</ul>";
	echo "<table>";
	echo "<div id=selectco style=\"display:none;\"></div>";
	echo "<div id=passwd style=\"display:none;\"></div>";

}

elseif ($_REQUEST['action']=="selectco")
{
	echo "<p>Select Company</p>";
	echo "<p width=100%><table align=center><tr><td><img onclick=\"passwd('".$_REQUEST['username']."',1);\" class=logimg src=./images/heart.jpg /></td><td><img onclick=\"passwd('".$_REQUEST['username']."',1);\" class=logimg  src=./images/leaves.png /></td></tr></table></p>";
	echo "<script type=text/javascript>$('#selectco').slideToggle('fast');</script>";	

}

elseif ($_REQUEST['action']=="passwd")
{
	echo "<p width=100%><table align=center>";
        echo "<tr><td><input type=password id=password></td></tr>";
        echo "<tr><td><button id=loginbut style=\"width:100%;\" onclick=\"javascript:send('".$_REQUEST['username']."',".$_REQUEST['company'].");\">Login</button><td></tr>";
	echo "</table></p>";
	echo "<script type=text/javascript>$('#passwd').slideToggle('fast');$('#password').focus();</script>";

}


?>
<script type=text/javascript>
setInterval(function(){
    $.ajax({ 
        	url: "./auth/login.php?action=check", 
        	success: 
            		function(){
        				
    					}
			, error:
					function(){
						location.reload();
			}
		});
	}, 30000);
</script>
