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

$font = new BCGFontFile('/var/www/pos/functions/dompdf/lib/fonts/Arial.ttf');

$code = new BCGcode128(); // Or another class name from the manual
$code->setScale(2); // Resolution
$code->setThickness(30); // Thickness
$code->setForegroundColor($colorFont); // Color of bars
$code->setBackgroundColor($colorBack); // Color of spaces
//$code->setFont($font); // Font (or 0)
$code->parse($text); // Text

$drawing = new BCGDrawing('', $colorBack);
$drawing->setBarcode($code);
$drawing->draw();

return $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
}
?>
