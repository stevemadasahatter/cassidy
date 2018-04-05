<?php
include '../config.php';
include '../functions/field_func.php';

$action=$_REQUEST['action'];


if ($action=="")
{	
	echo "<div id=set>";
	//echo "<h2>Reporting Selection</h2>";
	echo "<table>";
	echo "<tr>";
	$menu=array();
	$categories=array();
	if ($handle = opendir('./configs/')) 
	{
		while (false !== ($entry = readdir($handle))) 
		{
			if (substr($entry,strlen($entry)-3,3)=='php')
			{
				include './configs/'.$entry;
				$menu[$category]['data'][]=$dataset;
				$menu[$category]['entry'][]=$entry;
			}
		}
	}
	
	$i=0;
	foreach ($menu as $key =>$category)
	{
	    echo "<td style=\"margin-bottom:10px;padding:10px;width:200px;background-color:#eee;color:#000;\">
                <a style=\"color:#000;\" onclick=\"javascript:showmenu('$key');\" >$key</a>";
	    
	    $left=365+$i*221;
	    echo "<div id=$key class=menu style=\"width:218px;z-index:10;border:1px solid #777;background:#fff;position:absolute;left:".$left.";display:none;\">";
	    echo "<table>";
	    $j=0;
	    foreach ($menu[$key]['data'] as $report)
	    {
	        echo "<tr><td onclick=\"javascript:populate_filters('".$menu[$key]['entry'][$j]."');\">$report</td></tr>";
	        $j++;
	    }
	    echo "</table>";
	    echo "</div>";
	    echo "</td>";
	    $i++;
	}
	echo "</tr></table>";
	echo "</div>";
	
	echo "<div id=selection></div>";
}


if ($action=="show")
{
	include './configs/'.$_REQUEST['dataset'];
	
	if ($nodate<>1)
	{
	    echo "<div id=dates><table><tr><td>Start of Day</td><td>End of Day</td></tr>";
	    echo "<tr><td><div id=datein></div></td><td><div id=dateout></div></td></tr></table></div>";
	}
	
	echo "<h2>Selected Dataset - $dataset</h2><input type=hidden id=dataset value=\"".$_REQUEST['dataset']."\" />";
	echo "<div id=detail><table><tr><th>Filter</th><th>Option</th><th style=\"width:85px;text-align:left;\">GroupBy</th></tr>";
	$i=0;
	foreach ($filters[0] as $filter)
	{
		$selectdetail=getSelect($filter);
		if ($selectdetail=="<option value=''></option>")
		{
			echo "<tr><td>$filter</td><td><input type=text id=$filter />";
		}
		else 
		{
			echo "<tr><td>$filter</td><td><select id=$filter><option selected></option>".$selectdetail."</select>";
		}
		if ($filters[2][$i]<>"")
		{
			echo "</td><td style=width:85px;><input style=width:85px; type=text id=\"".$filter."grp\"></tr>";
		}
		else
		{
			echo "</td></tr>";
		}
		$i++;
	}
	echo "<tr><td></td><td colspan=2><button id=save>Save</button><button onclick=\"javascript:download_report();\">Download</button><button id=submit>Run</button></td></tr>";
	if ($rollup==0)
	{
		echo "<tr><td colspan=2></td></tr>";
	}
	else 
	{
		echo "<tr><td colspan=2>Subtotals?</td><td><input type=checkbox id=totals  checked /></td></tr>";
	}
	echo "<tr><td colspan=2>Suppress Duplicates?</td><td><input type=checkbox id=dupe /></td></tr>";
	echo "<tr><td colspan=2>Suppress Zeros?</td><td><input type=checkbox id=zeros /></td></tr>";
	echo "<tr><td colspan=2>Limit Rows</td><td><input type=text id=limit /></td></tr>";
	echo "<tr><td colspan=2>Orient</td><td><select id=orient>";
	if ($orient=="landscape")
	{
        echo "<option value=landscape selected>Landscape</option>";
        echo "<option value=portrait>Portrait</option>";
	}
	else
	{
	    echo "<option value=landscape >Landscape</option>";
	    echo "<option value=portrait selected>Portrait</option>";
	}
	echo "</select></td></tr>";
	echo "</table>";
	
	
	
	echo "</div>";
	
	echo "<div id=float></div>";
}



?>
<script type="text/javascript">


$(function()
{
	$('#datein').datepicker({      
		  changeMonth: true,
	      changeYear: true,
	      yearRange:"2003:2020"});
	$('#dateout').datepicker({      
		  changeMonth: true,
	      changeYear: true});
	$('button').button();
});


function populate_filters(dataset)
{
	var datasetname=$('#dataset option:selected').text();
	var datasetURL=encodeURI(dataset);
	var datasetnameURL=encodeURI(datasetname);
	$('#selection').load('./report/report.php?action=show&dataset='+datasetURL);	
	$('#selection').slideDown('fast');
	$('.menu').hide();
}

$('#close').click(function()
{
	$('#temp').remove();
	$('#float').hide();
});


$('#submit').click(function()
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
	getString=getString+'action=display&';
	getString=getString+'datein='+datein+'&';
	getString=getString+'dateout='+dateout+'&';
	getString=getString+'dataset='+$('#dataset').val()+'&';
	getString=getString+'totals='+totals+'&';
	getString=getString+'totals='+totals+'&';
	getString=getString+'zeros='+zeros+'&';
	getString=getString+'dupe='+dupe+'&';
	getString=getString+'limit='+$('#limit').val()+'&';
	window.open('./report/output.php?'+getString, '_blank');
//	$('#float').html('<div id=temp></div>');
//	$('#temp').load('./report/output.php?'+getString);
//	$('#float').show();
//	$('#dimmer').show();
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
	window.open('./report/output.php?'+getString);
}

$('#save').click(function()
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
	getString=getString+'action=display&';
	getString=getString+'datein='+datein+'&';
	getString=getString+'dateout='+dateout+'&';
	getString=getString+'dataset='+$('#dataset').val()+'&';
        getString=getString+'totals='+totals+'&';
    	getString=getString+'totals='+totals+'&';
    	getString=getString+'zeros='+zeros+'&';
    	getString=getString+'dupe='+dupe+'&';
	$('#dimmer').show();
	$('#float').html('<div id=temp></div>');
	$('#temp').load('./report/save_report.php?'+getString);
	$('#float').show();
});

function showmenu(key)
{
	$('.menu').hide();
	$('#'+key).slideDown('fast');
}

</script>
