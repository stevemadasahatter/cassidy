<?php

function print_barcode($text)
{
require_once('/var/www/pos/functions/class/BCGFontFile.php');
require_once('/var/www/pos/functions/class/BCGColor.php');
require_once('/var/www/pos/functions/class/BCGDrawing.php');
require_once('/var/www/pos/functions/class/BCGcode128.barcode.php');

// The arguments are R, G, and B for color.
$colorFront = new BCGColor(0, 0, 0);
$colorBack = new BCGColor(255, 255, 255);

$font = new BCGFontFile('/var/www/pos/functions/font/Arial.ttf','10');

$code = new BCGcode128(); // Or another class name from the manual
$code->setScale(1); // Resolution
$code->setThickness(30); // Thickness
$code->setForegroundColor($colorFont); // Color of bars
$code->setBackgroundColor($colorBack); // Color of spaces
//$code->setFont($font); // Font (or 0)
$code->setFont(0); // Font (or 0)
$code->parse($text); // Text

$drawing = new BCGDrawing('', $colorBack);
$drawing->setBarcode($code);
$drawing->draw();

return $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
}

function barcode_print($text)
{
	require_once('/var/www/pos/functions/class/BCGFontFile.php');
	require_once('/var/www/pos/functions/class/BCGColor.php');
	require_once('/var/www/pos/functions/class/BCGDrawing.php');
	require_once('/var/www/pos/functions/class/BCGcode128.barcode.php');

	// The arguments are R, G, and B for color.
	$colorFront = new BCGColor(0, 0, 0);
	$colorBack = new BCGColor(255, 255, 255);

	$font = new BCGFontFile('/var/www/pos/functions/font/Arial.ttf','10');

	$code = new BCGcode128(); // Or another class name from the manual
	$code->setScale(1); // Resolution
	$code->setThickness(30); // Thickness
	$code->setForegroundColor($colorFont); // Color of bars
	$code->setBackgroundColor($colorBack); // Color of spaces
	$code->setFont($font); // Font (or 0)
	$code->parse($text); // Text

	$drawing = new BCGDrawing('', $colorBack);
	$drawing->setBarcode($code);
	$drawing->draw();

	return $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
}

function getNextBarcode()
{
        include '../config.php';
        $db_conn=mysqli_connect($db_host, $db_username, $db_password, $db_name);

        $sql_query="select max(CONVERT(barcode, SIGNED INTEGER)) barcode from style";
        $maxes=$db_conn->query($sql_query);

        $max=mysqli_fetch_array($maxes);

        return $max['barcode']+1;
}

?>
