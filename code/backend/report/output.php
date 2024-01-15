<?php
include '../config.php';
include '../functions/auth_func.php';
include '../functions/print_func.php';
include '../functions/field_func.php';
require_once '../functions/dompdf/dompdf_config.inc.php';

echo <<<EOF
<html>
<head>
		    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="cache-control" content="no-store">
    <meta http-equiv="pragma" content="no-store">
        <title>Cassidy - Back Office</title>
        <link rel=stylesheet type="text/css" href="../style/login.css" />
         <link rel="stylesheet" href="../style/jquery-cr/jquery-ui.css">
		<link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="../style/jquery-cr/jquery-ui.js"></script>
        <script src="./tableExport.js"></script>
        <script src="./jquery.base64.js"></script>
</head>
<body>

EOF;

ob_start();
$action=$_REQUEST['action'];

$runtime=date('d/m/Y');
session_start();
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

if ($action<>"download" && $action<>"print" && $action<>'file')
{
	echo "<p align=right><button id=export>Export</button><button id=close>Close</button></p>";
}

		
		include './configs/'.$_REQUEST['dataset'];
		if ($_REQUEST['orient']<>'')
		{
		    $orient=$_REQUEST['orient'];
		}
?>

<style>
p, td, a
{
	font-size:12pt;
	font-family:arial;
	text-decoration:none;
	vertical-align:middle;
	color:#000000;
	margin:0px;
	padding:0px;
	clear:both;
    white-space:nowrap;
    overflow:hidden;
}


table.report td
{
	font-size:10pt;
	font-family:arial;
	padding-left:0px;
	padding-right:0px !important;
	width:40px;
}


table.report th
{
	font-weight:bold;
	font-family:arial;
	word-wrap:break-word;
}

table.summary td
{
	font-weight:bold;
	font-family:arial;
	padding-left:0px;
		border-top:1px solid #000000;
	border-bottom:1px solid #000000;
	padding-right:25px;
}

table.summary th
{
	font-weight:bold;
	font-family:arial;
}

tr.summary td.grandtotal
{
    text-align:left;
    font-style:normal;
    font-weight:bold;
}

tr.summary td
{
    font-style:italic;
	font-family:arial;
	padding-left:0px;
	border-top:1px solid #000000;
	border-bottom:1px solid #000000;
	padding-right:25px;
    text-align:right;
}


td.number
{
	text-align:right;
}

.level0
{
	font-size:16pt;
    font-weight:bold;
}

.level1
{
	font-size:14pt;
}

.level2
{
	font-family:arial;
	font-size:10pt;
    font-style:italic;
}
					
h2
{
	padding:0px;
	margin:0px;
	font-size:12pt;
	font-family:arial;
	color:#000000;
	
}
.reportodd
{
	background-color:#eee;
}

.columnborder
{
    border-left:2px solid #000000;
    padding-left:25px !important;
}

.hightitle
{
    text-align:center;
    background:#eee;
    font-size:13pt;
    font-weight:bold;
}

@page
{
    size:A4 <?php echo $orient; ?>;
}

@page {
  @bottom-right {
    margin: 10pt 0 30pt 0;
    border-top: .25pt solid #666;
    content: "RTest";
    font-size: 9pt;
    color: #333;
  }

  @bottom-left { 
    margin: 10pt 0 30pt 0;
    border-top: .25pt solid #666;
    content: counter(page);
    font-size: 9pt;
  }
}

</style>

<?php
	
	if ($action=="print2" || $action=="download2")
	{
	    echo<<<EOF
<style>


@page:left {
  @bottom-right {
    margin: 10pt 0 30pt 0;
    border-top: .25pt solid #666;
    content: "RTest";
    font-size: 9pt;
    color: #333;
  }

  @bottom-left { 
    margin: 10pt 0 30pt 0;
    border-top: .25pt solid #666;
    content: counter(page);
    font-size: 9pt;
  }
}

</style>

EOF;

	    

	}
		$sql_query="select nicename from companies where conum=".$_SESSION['CO'];
		$results=$db_conn->query($sql_query);
		$result=mysqli_fetch_array($results);
		echo "<h2 align=left>".$result['nicename']."</h2>";
		echo "<table width=100%><tr><td><h2>$dataset</h2></td><td align=right><b>Run Date: $runtime</b></td></tr></table>";
		#build SQL statement
		#Loop through request
		for ($i=0;$i<100;$i++)
		{
			if ($_REQUEST[$filters[0][$i]]!="")
			{
			    if ($_REQUEST[$filters[0][$i].'like']==1)
			    {
				    $predicate.="and ".$filters[1][$i]." like '%".$_REQUEST[$filters[0][$i]]."%' ";
			    }
			    else 
			    {
			        $predicate.="and ".$filters[1][$i]."='".$_REQUEST[$filters[0][$i]]."' ";
			    }
				$predicate_text.= "<p>".$filters[4][$i]." : ".getNiceName($filters[0][$i],$_REQUEST[$filters[0][$i]])."</p>";
			}
		}

	       if ($_REQUEST['dateinovr'])
	       {
	           $_REQUEST['batch']=1;
	           $_REQUEST['datein']=$_REQUEST['dateinovr'];
	       }
	       
	       if ($_REQUEST['dateoutovr'])
	       {
	           $_REQUEST['dateout']=$_REQUEST['dateoutovr'];
	       }
	       
			#add date
			if ($_REQUEST['batch']==1)
			{
				#preformatted
				$sql_date=str_replace('[[DATE]]', " $date between ".$_REQUEST['datein']." and ".$_REQUEST['dateout']." ", $sql);
				$sql_date=str_replace('[[DDATE]]', " between ".$_REQUEST['datein']." and ".$_REQUEST['dateout']." ", $sql_date);
				$predicate_text.="<p>Date : ".$_REQUEST['datein']." - ".$_REQUEST['dateout']."</p>";
				
			}
			else
			{
				$sql_date=str_replace('[[DATE]]', " $date between STR_TO_DATE('".$_REQUEST['datein']." 00:00:00', '%m/%d/%Y %H:%i:%s') and STR_TO_DATE('".$_REQUEST['dateout']." 23:59:59', '%m/%d/%Y %H:%i:%s') ", $sql);
				$sql_date=str_replace('[[DDATE]]', " between STR_TO_DATE('".$_REQUEST['datein']." 00:00:00', '%m/%d/%Y %H:%i:%s') and STR_TO_DATE('".$_REQUEST['dateout']." 23:59:59', '%m/%d/%Y %H:%i:%s') ", $sql_date);
				$predicate_text.="<p>Date : ".date_format(date_create($_REQUEST['datein']), 'd/m/Y')." - ".date_format(date_create($_REQUEST['dateout']),'d/m/Y')."</p>";
			}

			
			//$predicate.="and $date between STR_TO_DATE('".$_REQUEST['datein']." 00:00:00', '%m/%d/%Y %H:%i:%s') and STR_TO_DATE('".$_REQUEST['dateout']." 23:59:59', '%m/%d/%Y %H:%i:%s') ";
			

			#Add group by clause
			#build array
			$groupby=array();
			$j=0;
			for ($i=0;$i<1000;$i++)
			{
				if ($_REQUEST[$filters[0][$i]."grp"]!="")
				{
					$groupby[$_REQUEST[$filters[0][$i]."grp"]]=$filters[2][$i];
					$preby[$_REQUEST[$filters[0][$i]."grp"]]=$filters[3][$i];
					$j++;
				}
			}
			
			if ($j>0)
			{
				$group="group by ";
				$order=" order by ";
				$pre="";
				for ($i=1;$i<=$j;$i++)
				{
					if ($j==$i)
					{
						$group.=$groupby[$i];
						$order.=$groupby[$i];
						$pre.=$preby[$i].", ";
				}
					else
					{
						$group.=$groupby[$i].", ";
						$order.=$groupby[$i].", ";
						$pre.=$preby[$i].", ";
					}
				}
				if ($groupby_fixed<>"")
				{
					$group.=$groupby[$i].", ".$groupby_fixed;
					$order.=$groupby[$i].", ".$groupby_fixed;
				}
			
			}
			
			else
			{
				$group="";
			
				if ($groupby_fixed<>"")
				{
					$group.=$groupby[$i]."group by ".$groupby_fixed;
					$order.=$groupby[$i]."order by ".$groupby_fixed;
				}
			}
	#Build SQL statement
	if ($_REQUEST['totals']==1)
	{
	    if ($select_off<>1)
	    {
	        $query="select ";
	    }
		if ($order_by=="" && $orderby_fixed=="")
		{

			$query.=$pre." ".$sql_date.$predicate.$group."  ";
		}
		elseif ($order_by=="" && $orderby_fixed<>"")
		{
			$query.=$pre." ".$sql_date.$predicate.$group." order by ".$orderby_fixed."  ";
		}
		else
		{
			$query.=$pre." ".$sql_date.$predicate.$group." order by ".$order_by."  ";
		}
		if ($rollup==1)
		{
			$query.=" with rollup";
		}
	}

	else
	{
	    if ($select_off<>1)
	    {
	        $query="select ";
	    }
		if ($order_by=="" && $orderby_fixed=="")
		{

			$query.=$pre." ".$sql_date.$predicate.$group."  ";
		}
		elseif ($order_by=="" && $orderby_fixed<>"")
		{
			$query.=$pre." ".$sql_date.$predicate.$group." order by ".$orderby_fixed."  ";
		}
		else
		{
			$query.=$pre." ".$sql_date.$predicate.$group." order by ".$order_by."  ";
		}
	}
	
	if ($_REQUEST['limit']<>'')
	{
	   $query.=" LIMIT ".$_REQUEST['limit'];   
	}
	
	if ($debug==1)
	{
		echo $query;
	}
	$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);
	if ($debug==1)
	{
	    $results=$db_conn->query($query) or die ("<p>MySQL Error: ".mysqli_error($db_conn)."</p>");
	}
	else
	{
	   $results=$db_conn->query($query);
	}
	$num_fields=mysqli_num_fields($results);
	$num_rows=mysqli_affected_rows($db_conn);
	$predicate_text.="<p>Rows Fetched : ".$num_rows."</p><br>";
	echo $predicate_text;
	echo "<table id=\"data_table\" class=report>";
	echo "<thead>";
	echo "<tr>";
	$elements=count($groupby);
	
	#No high title for group by 
    echo "<td colspan=".$elements."></td>";
    for ($h=0;$h<=20;$h+=2)
    {
        if ($high_titles[$h]=="")
        {
            echo "<td class=blanktitle colspan=".$high_titles[$h+1]."></td>";
        }
        else
        {
            echo "<td class=hightitle colspan=".$high_titles[$h+1].">".$high_titles[$h]."</td>";
        }
        
        if ($high_titles[$h+3]=="")
        {
            $h=100;
        }
    }
    echo "</tr><tr>";
	$i=0;
	$colnames=array();
	while($columns=mysqli_fetch_field($results))
	{
		array_push($colnames,$columns->name);
		
		if ($i >= $elements-1 && $_REQUEST['totals']==1)
		{
			if ($i<=1)
			{
				echo "<th align=left>".$columns->name."</th>";
			}
			else 
			{
				echo "<th>".$columns->name."</th>";
			}
		}
		else 
		{
			if ($i<=1)
			{
				echo "<th align=left>".$columns->name."</th>";
			}
			else
			{
				echo "<th>".$columns->name."</th>";
			}
		}
		$i++;
	}

	echo "</tr></thead>";
	
	$cols=count($colnames)-$elements;
	#First row will be a summary row unless there is only 1 level of summary
	if ($elements>1 && $_REQUEST['totals']==1)
	{
		$summary_title=1;
	}	
	
	$row_num=0;
	while ($result=mysqli_fetch_assoc($results))
	{	
		unset($search);
		#Is this a Summary title row?
		if ($summary_title>=1)
		{
			for ($j=$summary_title-1;$j<$elements-1;$j++)
			{

				echo "<tr><td class=level$j >".$result[$colnames[$j]]."</td></tr>";

			}	
			$summary_title=0;
		}
		
		$summary_title=array_search(array_search(NULL, $result), $colnames);
		
		for ($i=0;$i<50;$i++)
		{
		      if ($i <= $elements-1)
		      {
		          if ($result[$colnames[$i]]==NULL)
		          {
		              $search="yes";
		          }
		      }
		      else {
		          $i=100;
		      }
		}

		if ($search<>"")
		{
				echo "<tr class=summary>";
		}
		else
		{
			$summary_title=0;
			if (($row_num % 2) ==1)
			{
				echo "<tr class=reportodd>";
			}
			else
			{
				echo "<tr class=reporteven>";
			}
			
			$row_num++;
		}	
		
		#Build output page
		$i=0;
		$summary_sent=0;
		foreach ($result as $row)
		{
			#Output Summary column
			if ($i <= $elements-1 && $row==NULL)
			{
				if ($row==NULL  && $summary_sent==0)
				{
					if ($result[$colnames[$i-1]]=="")
					{
						echo "<td class=grandtotal>Grand Total</td>";
						$summary_sent=1;
					}
					else
					{
						echo "<td style=\"white-space:nowrap !important;\">".$result[$colnames[$i-1]]." Subtotal</td>";
						$summary_sent=1;
					}
				}
				else
				{
				    echo "<td></td>";
				}
			}
			elseif ($i<=$elements-1  && $_REQUEST['totals']==0 )
			{
				if ($prevresult[$colnames[$i]]<>$row || $_REQUEST['dupe']<>1)
				{
					echo "<td align=left>$row</td>";
				}
				else
					{
						echo "<td></td>";
					}
			}

			elseif ($i >= $elements-1 && $row<>NULL)
			{
				if ((is_numeric($row) || $row==0) && $i>=1)
				{
				    if ($seperators[$i-$elements]==1 && $i<>1)
				    {
				        echo "<td class=\"number columnborder\">";
				    }
				    else 
				    {
					   echo "<td class=number>";
					   
				    }
					if ($_REQUEST['zeros']==1 && $row=='0')	
					{
						echo "</td>";
					}
					else{
						echo "$row</td>";
					}
				}
        			else
        			{
        					if ($prevresult[$colnames[$i]]<>$row)
        					{
        					    if ($seperators[$i-$elements]==1)
        					    {
        						  echo "<td class=columnborder>$row</td>";
        					    }
        					    else
        					    {
        					        echo "<td>$row</td>";
        					    }
        					}
        					else
        					{
        						echo "<td></td>";
        					}
        			}

			}
			else
			{
			    echo "<td></td>";
			}
			$i++;
		}
		$summary_sent=0;

		echo "</tr>";
		$prevresult=$result;
	}
	echo "</table>";
$html=ob_get_clean();

if ($action=="display")
{
	echo $html;
}

if ($action=="print")
{
	ob_clean();
	print_action($html,$main_printer, $orient);
}

if ($action=="download")
{
	download_action($html,$main_printer, $orient, 'display');
	
}

if ($action=="file")
{    

    #Email file
    require_once "Mail.php";
    //define the receiver of the email
    $to = "sdkellymail@gmail.com,rebecca@cocorose.co.uk";
    
    //define the subject of the email
    $subject = 'Your report';
    //create a boundary string. It must be unique
    //so we use the MD5 algorithm to generate a random hash
    $random_hash = md5(date('r', time()));
    //define the headers we want passed. Note that they are separated with \r\n
    //read the atachment file contents into a string,
    //encode it with MIME base64,
    //and split it into smaller chunks
    $attachment_chunk = chunk_split(base64_encode(download_action($html,$main_printer, $orient, 'file')));
    //define the body of the message.
    ob_start(); //Turn on output buffering
        ?>
--PHP-mixed-<?php echo $random_hash; ?>

Content-Type: multipart/alternative; boundary="PHP-alt-<?php echo $random_hash; ?>"

--PHP-alt-<?php echo $random_hash; ?>

Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

<h2>Your shopping receipt</h2>
<p>Please find your shopping receipt attached for your convenience. Thanks again for your custom.</p>
<p>Kind regards</p>
<p>Coco Rose</p>
--PHP-alt-<?php echo $random_hash; ?>--

--PHP-mixed-<?php echo $random_hash; ?>

Content-Type: application/pdf; name="report.pdf"
Content-Transfer-Encoding: base64
Content-Disposition: attachment

<?php echo $attachment_chunk; ?>
--PHP-mixed-<?php echo $random_hash; ?>--


<?php
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();

$host = "smtp.gmail.com";
$username = "rebecca@cocorose.co.uk";
$password = "R0semaryandthym3!";
$from="shop@cocorose.co.uk";

$headers = array (
    'To' => $to,
    'Subject' => $subject,
    'MIME-Version' => '1.0',
    'Content-type' => 'multipart/mixed; boundary="PHP-mixed-'.$random_hash.'"',
    'return-receipt-to' => $from ,
    'return-path' => $from,
    'From' =>  $from);

$smtp = Mail::factory('smtp',
    array ('host' => $host,
        'port'=> 587,
        'auth' => true,
        'socket_options' => array('ssl' => array('verify_peer_name' => false)),
        'debug' => true,
        'username' => $username,
        'password' => $password));
    $mail = $smtp->send($to, $headers, $message);
}



?>

<script type="text/javascript">
$(document).ready(function(){
	$('button').button();
	$("#export").click(function() {
		var export_type = 'excel';
		$('#data_table').tableExport({
		type : export_type,
		escape : 'false',
		ignoreColumn: []
		});
		});
});

$('#close').click(function()
{
window.close();
});

function print_report()
{
	var datein=$('#datein').val();
	var dateout=$('#dateout').val();
    if ($('#totals').is(":checked"))
    {
            var totals=1;
    }
    else
    {
            var totals=0;
    }

    if ($('#zeros').is(":checked"))
    {
            var zeros=1;
    }
    else
    {
            var zeros=0;
    }

    if ($('#dupe').is(":checked"))
    {
            var dupe=1;
    }
    else
    {
            var dupe=0;
    }
	
	var getString="";
	$('#detail').find('select').each(function(){
		getString=getString+$(this).attr('id')+'='+$(this).val()+'&';
	});

	$('#detail').find('input[id*=grp]').each(function(){
		getString=getString+$(this).attr('id')+'='+$(this).val()+'&';
	});
	$('#detail').find('input[type=text]').each(function(){
		getString=getString+$(this).attr('id')+'='+$(this).val()+'&';
	});
	getString=getString+'action=print&';
	getString=getString+'datein='+datein+'&';
	getString=getString+'dateout='+dateout+'&';
	getString=getString+'dataset='+$('#dataset').val()+'&';
	getString=getString+'totals='+totals+'&';
	getString=getString+'zeros='+zeros+'&';
	getString=getString+'dupe='+dupe+'&';
	getString=getString+'limit='+$('#limit').val()+'&';
	$('#dimmer').show();
	$('#float').html('<div id=temp></div>');
	$('#temp').load('./report/output.php?'+getString);
	$('#float').show();
	$('#temp').remove();
	$('#float').hide();
	$('#dimmer').hide();
}

function download_report()
{
	var datein=$('#datein').val();
	var dateout=$('#dateout').val();
        if ($('#totals').is(":checked"))
        {
                var totals=1;
        }
        else
        {
                var totals=0;
        }
        if ($('#zeros').is(":checked"))
        {
                var zeros=1;
        }
        else
        {
                var zeros=0;
        }

        if ($('#dupe').is(":checked"))
        {
                var dupe=1;
        }
        else
        {
                var dupe=0;
        }

	var getString="";
	$('#detail').find('select').each(function(){
		getString=getString+$(this).attr('id')+'='+$(this).val()+'&';
	});

	$('#detail').find('input[id*=grp]').each(function(){
		getString=getString+$(this).attr('id')+'='+$(this).val()+'&';
	});
	$('#detail').find('input[type=text]').each(function(){
		getString=getString+$(this).attr('id')+'='+$(this).val()+'&';
	});
	getString=getString+'action=download&';
	getString=getString+'datein='+datein+'&';
	getString=getString+'dateout='+dateout+'&';
	getString=getString+'dataset='+$('#dataset').val()+'&';
        getString=getString+'totals='+totals+'&';
    	getString=getString+'totals='+totals+'&';
    	getString=getString+'zeros='+zeros+'&';
    	getString=getString+'dupe='+dupe+'&';
	window.open('./output.php?'+getString);
}


</script>
