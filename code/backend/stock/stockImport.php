<?php

include '../config.php';
include '../functions/field_func.php';
include '../functions/barcode_func.php';
require_once '/var/www/backend/functions/phpexcel/Classes/PHPExcel/IOFactory.php';
session_start();
$db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

$action=$_REQUEST['action'];

#put the progress indicator here
if ($action=="")
{	
	echo "<form id=uploader>";
 	echo "<table>";
	echo "<tr><td><h2>Upload file</h2></td></tr>";
	echo "<tr><td><input type=file name=upload><input type=hidden name=action value=upload /></td></tr>";
	echo "<tr><td><button onclick=\"javascript:upld();\">Upload!</button>";
	echo "</table>";
	echo "</form>";
}

if ($action=="upload")
{
	if ($_FILES["upload"]["error"] > 0) {
		$return=0;
  	} else {
    		echo "Upload: " . $_FILES["upload"]["name"] . "<br>";
    		echo "Type: " . $_FILES["upload"]["type"] . "<br>";
    		echo "Size: " . ($_FILES["upload"]["size"] / 1024) . " kB<br>";
    		echo "Temp file: " . $_FILES["upload"]["tmp_name"] . "<br>";
    	if (file_exists("/var/www/backend/tmp" . $_FILES["upload"]["name"])) {
      		//echo $_FILES["upload"]["name"] . " already exists. ";
    		move_uploaded_file($_FILES["upload"]["tmp_name"],"/var/www/backend/tmp/inprogress.xlsx");
    	} else {
      		move_uploaded_file($_FILES["upload"]["tmp_name"],
      		"/var/www/backend/tmp/inprogress.xlsx");
		$return=1;
    	}
  	}
	
}
if ($action=="read")
{
	$inputFileType = 'Excel2007';
	$inputFileName = '/var/www/backend/tmp/inprogress.xlsx';	
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	/** Load $inputFileName to a PHPExcel Object **/
	$objPHPExcel = $objReader->load($inputFileName);
	echo "got here";
	$worksheetData = $objReader->listWorksheetInfo($inputFileName);
	echo "got here";
	echo "<h2>Select sheets to process this import (SELECT ONLY 1 FOR NOW)</h2>";
	echo "<table>";
	$i=0;
	foreach ($worksheetData as $worksheet)
	{
		echo "<tr><td>".$worksheet['worksheetName']." (".$worksheet['totalRows'].")</td><td><input type=checkbox value=$i></td></tr>";
		$i++;
	}
	echo "<tr><td colspan=2><button onclick=\"javascript:readSheet($i);\" >Read Sheet</button></td></tr>";
	echo "</table>";
	#Select worksheet we want to process
	
}


if ($action=="readdata")
{
	#Read in config data
        $inputFileType = 'Excel2007';
        $inputFileName = '/var/www/backend/tmp/inprogress.xlsx';
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setReadDataOnly(true);
        /** Load $inputFileName to a PHPExcel Object **/
	$worksheetData = $objReader->listWorksheetInfo($inputFileName);

class MyReadFilter implements PHPExcel_Reader_IReadFilter
{
        private $_startRow = 0;
        private $_endRow = 0;
        private $_columns = array();
        /** Get the list of rows and columns to read */
        public function __construct($startRow, $endRow, $columns)
        {
                $this->_startRow = $startRow; 
		$this->_endRow = $endRow; 
		$this->_columns = $columns;
        }
	public function readCell($column, $row, $worksheetName = '') 	
	{ 
		// Only read the rows and columns that were configured 
		if ($row >= $this->_startRow && $row <= $this->_endRow) 
		{ 
			if (in_array($column,$this->_columns)) 
			{ 
				return true; 
			} 
		} 
		return false; 
	} 
}

	$filterSubset=new MyReadFilter(1,$worksheetData[$_REQUEST['id']]['totalRows'],range('A','Z'));
	
	$objReader->setLoadSheetsOnly($worksheetData[$_REQUEST['id']]['worksheetName']);
	$objReader->setReadFilter($filterSubset);
        $objPHPExcel = $objReader->load($inputFileName);
	

	$sheetData=$objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

	$assoc=$sheetData[2];

        echo "<div id=sku><h2>Brand is ".$worksheetData[$_REQUEST['id']]['worksheetName']."<select name=brand>".fuzzygetSelect('brands',$worksheetData[$_REQUEST['id']]['worksheetName'])."</select></h2></div>";
        echo "<div id=waiting>
                <p>Working <img src=./images/wait.gif /></p>
        </div>";
        echo "<div id=import>";

        
        #Display controls
        echo "<div>";
        echo "<ul style=\"display:table-row;\">";
        echo "<li style=\"width:30px;\"  class=importitem></li>";
        echo "<li style=\"width:80px;\"  class=importitem></li>";
        echo "<li style=\"width:120px;\"  class=importitem>Season</li>";
        echo "<li style=\"width:170px;\" class=importitem><select class=import name=season>".getSelect('seasons','')."</select></li>";
        echo "<li style=\"width:120px;\"  class=importitem>Vat Rate</li>";
        echo "<li style=\"width:70px;\"  class=importitem><select class=import name=vatrate>".getSelect('vatkey','')."</select></li>";
        echo "<li style=\"width:70px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:10px;\"  class=importitem></li>";        
        echo "<li style=\"width:10px;\"  class=importitem></li>";
        echo "<li style=\"width:40px;\"  class=importitem></li>";
        echo "<li style=\"width:120px;\"  class=importitem><select class=import name=Productgroupmaster>".fuzzygetSelect('Productgroup', '')."</select><p onclick=\"javascript:propagate('Productgroup');\">Propagate</p></li>";
        echo "<li style=\"width:120px;\"  class=importitem><select class=import name=sizekeymaster>".getSelect('sizekey','')."</select><p onclick=\"javascript:propagate('sizekey');\">Propagate</p></li>";
        
        echo "</ul>";
        echo "</div>";
	#Display array
        echo "<div>";
	echo "<ul style=\"display:table-row;\">";
	echo "<li style=\"width:30px;\"  class=importtitle>".$assoc['A']."</li>";
	echo "<li style=\"width:80px;\"  class=importtitle>".$assoc['B']."</li>";
	echo "<li style=\"width:120px;\"  class=importtitle>".$assoc['C']."</li>";
	echo "<li style=\"width:170px;\" class=importtitle>".$assoc['D']."</li>";
	echo "<li style=\"width:120px;\"  class=importtitle>".$assoc['E']."</li>";
	echo "<li style=\"width:70px;\"  class=importtitle>".$assoc['F']."</li>";
	echo "<li style=\"width:70px;\"  class=importtitle>".$assoc['G']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['H']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['I']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['J']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['K']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['L']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['M']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['N']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['O']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['P']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['Q']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['R']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['S']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['T']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['U']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['V']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['W']."</li>";
	echo "<li style=\"width:10px;\"  class=importtitle>".$assoc['X']."</li>";
	echo "<li style=\"width:40px;\"  class=importtitle>".$assoc['Y']."</li>";
	echo "<li style=\"width:120px;\"  class=importtitle>Product Group</li>";
	echo "<li style=\"width:120px;\"  class=importtitle>Size Type</li>";

	echo "</ul>";
	echo "</div>";

	for ($i=7;$i<$worksheetData[$_REQUEST['id']]['totalRows'];$i++)
	{
		echo "<div id=item".$sheetData[$i]['B'].">";
		echo "<ul style=\"border-bottom:1px solid #eee;padding:0px;\">";
		echo "<li name=deliverymnth style=\"width:30px;\" class=importitem>".$sheetData[$i]['A']."</li>";
		echo "<li name=sku style=\"width:80px;\" class=importitem>".$sheetData[$i]['B']."</li>";
		echo "<li name=no style=\"width:120px;\" class=importitem>".$sheetData[$i]['C']."<br><select class=import name=category>".fuzzygetSelect('category', $sheetData[$i]['C'])."</select></li>";
		echo "<li name=description style=\"width:170px;\" class=importitem>".$sheetData[$i]['D']."</li>";
		echo "<li name=no style=\"width:120px;\" class=importitem>".$sheetData[$i]['E']."<br><select class=import name=colour>".fuzzygetSelect('colours2', $sheetData[$i]['E'])."</select></li>";
		echo "<li name=cost style=\"width:70px;\" class=importitem>".number_format($sheetData[$i]['F'],2)."</li>";
		echo "<li name=retail style=\"width:70px;\"class=importitem>".number_format($sheetData[$i]['G'],2)."</li>";
		echo "<li name=size1 style=\"width:10px;\" class=importitem>".$sheetData[$i]['H']."</li>";
		echo "<li name=size2 style=\"width:10px;\" class=importitem>".$sheetData[$i]['I']."</li>";
		echo "<li name=size3 style=\"width:10px;\" class=importitem>".$sheetData[$i]['J']."</li>";
		echo "<li name=size4 style=\"width:10px;\" class=importitem>".$sheetData[$i]['K']."</li>";
		echo "<li name=size5 style=\"width:10px;\" class=importitem>".$sheetData[$i]['L']."</li>";
		echo "<li name=size6 style=\"width:10px;\" class=importitem>".$sheetData[$i]['M']."</li>";
		echo "<li name=size7 style=\"width:10px;\" class=importitem>".$sheetData[$i]['N']."</li>";
		echo "<li name=size8 style=\"width:10px;\" class=importitem>".$sheetData[$i]['O']."</li>";
		echo "<li name=size9 style=\"width:10px;\" class=importitem>".$sheetData[$i]['P']."</li>";
		echo "<li name=size10 style=\"width:10px;\" class=importitem>".$sheetData[$i]['Q']."</li>";
		echo "<li name=size11 style=\"width:10px;\" class=importitem>".$sheetData[$i]['R']."</li>";
		echo "<li name=size12 style=\"width:10px;\" class=importitem>".$sheetData[$i]['S']."</li>";		
		echo "<li name=size13 style=\"width:10px;\" class=importitem>".$sheetData[$i]['T']."</li>";
		echo "<li name=size14 style=\"width:10px;\" class=importitem>".$sheetData[$i]['U']."</li>";
		echo "<li name=size15 style=\"width:10px;\" class=importitem>".$sheetData[$i]['V']."</li>";
		echo "<li name=size16 style=\"width:10px;\" class=importitem>".$sheetData[$i]['W']."</li>";
		echo "<li name=no style=\"width:10px;\" class=importitem>".$sheetData[$i]['X']."</li>";
		echo "<li name=no style=\"width:40px;\" class=importitem>".number_format($sheetData[$i]['Y'],2)."</li>";
		echo "<li name=no style=\"width:120px;\" class=importitem><select class=import name=Productgroup>".fuzzygetSelect('Productgroup', '')."</select></li>";
		echo "<li name=no style=\"width:120px;\" class=importitem><select class=import name=sizekey>".getSelect('sizekey','')."</select></li>";
		echo "</ul>";
		echo "</div>";
		if ($sheetData[($i+1)]['B']=="")
		{
			$i=40000000;
		}
	}
	echo "<p width=100% align=right><button onclick=\"javascript:commit();\">Commit</button>";
	echo "</div>";
echo "<script type=text/javascript>


</script>";
}

if ($action=="validate")
{
	#Do data checks

}

if ($action=="commit")
{
	$oldsku="";
	unset($dupe);
	
	#Double check SKU/colour is unique. If not add season on the end
	$sql_query="select count(*) cnt from stock sto, seasons sea, styleDetail style, brands bra, colours col
where StockRef ='".$_REQUEST['sku']."'
	and sea.id = ".$_REQUEST['season']."
	and bra.id = ".$_REQUEST['brand']." 
	and col.id <> '".$_REQUEST['colour']."'
	and col.colour = sto.colour
	and sea.id = style.season
	and bra.id = style.brand
    and style.sku = sto.Stockref";
	$results=$db_conn->query($sql_query);
	$dupe=mysqli_fetch_array($results);
	
	#Detects if the same brand for the same season has a new colour. Keep SKU
	if ($dupe['cnt']>0)
	{
		$sku_add_colour=1;
	}
	
	$sql_query="select count(*) cnt from stock sto, seasons sea, styleDetail style, brands bra, colours col
where StockRef ='".$_REQUEST['sku']."'
	and sea.id <> ".$_REQUEST['season']."
	and bra.id = ".$_REQUEST['brand']." 
    and col.colour = sto.colour
	and sea.id = style.season
	and bra.id = style.brand
    and style.sku = sto.Stockref";
	$results=$db_conn->query($sql_query);
	$dupe=mysqli_fetch_array($results);

	#Detects if SKU existed for the same brand in a different season
	if ($dupe['cnt']>0)
	{
		$sku_add_season=1;
	}
	
	$sql_query="select count(*) cnt from stock sto, seasons sea, styleDetail style, brands bra, colours col
where StockRef ='".$_REQUEST['sku']."'
	and bra.id <> ".$_REQUEST['brand']."
    and col.colour = sto.colour
	and sea.id = style.season
	and bra.id = style.brand
    and style.sku = sto.Stockref";
	$results=$db_conn->query($sql_query);
	$dupe=mysqli_fetch_array($results);
	
	#Detects if SKU existed in different brand
	if ($dupe['cnt']>0)
	{
		$sku_add_brand=1;
	}
	

	#Change SKU
	$oldsku=$_REQUEST['sku'];
	if ($sku_add_season)
	{
		$sql_query="select season addition from seasons where id = ".$_REQUEST['season'];
		$results=$db_conn->query($sql_query);
		$season=mysqli_fetch_array($results);
		$_REQUEST['sku']=$_REQUEST['sku'].$season['addition'];
		
	}
	elseif ($sku_add_brand)
	{
		$sql_query="select brand addition from brands where id = ".$_REQUEST['brand'];
		$results=$db_conn->query($sql_query);
		$season=mysqli_fetch_array($results);
		$_REQUEST['sku']=$_REQUEST['sku'].$season['addition'];
	}
		
	#Upload into database
	# Style
	$barcode=getNextBarcode();
	$descr = $db_conn->real_escape_string($_REQUEST['description']);
	$sql_query="insert into style (sku, company, description, sizekey, vatkey, onsale, barcode) values (";
	$setClause="upper(\"".$_REQUEST['sku']."\"),".$_SESSION['CO'].",\"".$descr."\",".$_REQUEST['sizekey'].",".$_REQUEST['vatrate'].",0, $barcode)";
	$sql_query=$sql_query.$setClause;
	try {
		$do_it=$db_conn->query($sql_query);
	}
	catch (Exception $e) {
		echo "";
	}
	
	# Styledetail 
	$setClause="(upper(\"".$_REQUEST['sku']."\"),\"".$descr."\",\"".$_REQUEST['Productgroup']."\",\"".$_REQUEST['category']."\",\"".$_REQUEST['season']
		."\",\"".$_REQUEST['brand']."\",".$_SESSION['CO'].")";
		
	
	
			
	$sql_query="insert into styleDetail (sku, description, Productgroup, category, season, brand, company) values ";
	$sql_query.=$setClause;
	try {
		$do_it=$db_conn->query($sql_query);
	}
	catch (Exception $e)  {
		echo "";
	}

	for ($i=1;$i<17;$i++)
	{
		if ($_REQUEST['size'.$i]=="")
		{
			$_REQUEST['size'.$i]=0;
		}
	}
	# Stock
	$setClause="(upper(\"".$_REQUEST['sku']."\"),".$_SESSION['CO'].",upper(\"".$_REQUEST['colour']."\"),".$_REQUEST['size1'].",".$_REQUEST['size2'];
	$setClause.=",".$_REQUEST['size3'].",".$_REQUEST['size4'].",".$_REQUEST['size5'].",".$_REQUEST['size6'].",".$_REQUEST['size7'].",";
	$setClause.=$_REQUEST['size8'].",".$_REQUEST['size9'].",".$_REQUEST['size10'].",".$_REQUEST['size11'].",".$_REQUEST['size12'].",".$_REQUEST['size13']."
			,".$_REQUEST['size14'].",".$_REQUEST['size15'].",".$_REQUEST['size16'].",\"".$_REQUEST['deliverymnth']."\",0";
	$setClause.=",".$_REQUEST['retail'].",".$_REQUEST['cost'].",1)";
	
	$sql_query="insert into stock (StockRef, company, colour, purchased1,purchased2,purchased3,purchased4,purchased5,purchased6,purchased7,purchased8,purchased9,purchased10
		,purchased11,purchased12,purchased13,purchased14,purchased15,purchased16,deliverymnth, forsale, retailprice, costprice,web_status) values ";
	$sql_query.=$setClause;
	//echo $sql_query;
	try {
		$do_it=$db_conn->query($sql_query);
	}
	catch (Exception $e)  {
		echo "";
	}

	#Check to return a pass/fail
	$sql_query="select styleDetail.sku, stock.StockRef from styleDetail, stock, style 
				where styleDetail.sku=stock.StockRef 
				and styleDetail.sku = style.sku
				and stock.colour='".$_REQUEST['colour']."' 
				and style.sku = '".$_REQUEST['sku']."'
				and styleDetail.sku = '".$_REQUEST['sku']."'";
	$results=$db_conn->query($sql_query);
	$num_rows=mysqli_affected_rows($db_conn);
	
	if ($num_rows==1)
	{
		if ($oldsku<>"")
		{
			echo "-->".$_REQUEST['sku'];
		}
	}
	else
	{
		echo "error";
	}
	exit();
}


?>

<script type="text/javascript">
$('#wait').hide();
$(document).ready(function(){
	$('button').button();
});


function upld()
{
	$('#wait').show();
	var fd= new FormData(document.getElementById("uploader"));
	$.ajax({
		url:'./stock/stockImport.php',
		type:'POST',
		data:fd,
		processData: false,
		contentType:false,
		success : function(){
			$('#output').load('./stock/stockImport.php?action=read');
		}
	});
}

function readSheet(id)
{
	$('#wait').show();
	$('input[type=checkbox]').each(function(){
		if (this.checked)
		{
			$('#output').load('./stock/stockImport.php?action=readdata&id='+this.value);
		}
	});
}

function propagate(type)
{
	var setID=$('select[name='+type+'master]').val();
	setID=setID;
	$('div[id^=item]').find('select[name='+type+']>option:eq('+setID+')').prop('selected',true);
}

function commit()
{
    $('#dialog').append('<div id=temp></div>');
    $('#dialog').css('top','20%');
    $('#dialog').css('left','50%');
    $('#dialog').css('margin-left','-14%');	
	$('#dimmer').show();
	$('#dialog').show();

	$('div[id^=item]').each(function(){
		var getString="";
		var sku=$(this).find('li[name=sku]').text();
		$(this).find('li:not([name=no])').each(function(){
			getString=getString+$(this).attr('name')+'='+encodeURI($(this).text())+'&';
		});
		$(this).find('select:not([name$=master])').each(function(){
			getString=getString+$(this).attr('name')+'='+encodeURI($(this).val())+'&';
		});
		getString=getString+'season='+$('select[name=season]').val()+'&';
		getString=getString+'vatrate='+$('select[name=vatrate]').val()+'&';
		getString=getString+'brand='+$('select[name=brand]').val();
		
		$.ajax({
			url:'./stock/stockImport.php?action=commit&'+getString,
			type:'GET',
			processData: false,
			contentType:false,
			success:function(msg)
			{
				if (msg=="error")
				{
					$('#temp').append("<p>"+sku+"<img src=./images/red-cross.jpg /></p>");
				}
				else
				{
					$('#temp').append("<p>"+sku+"<img src=./images/ok.png />"+msg+"</p>");
				}
				
			}
		});	
		

		
});
	$('#temp').append("<button onclick=\"javascript:$('#dimmer').hide();$('#temp').remove();$('#dialog').hide();\" >Close</button>");
	$('#waiting').hide();
}
</script>
