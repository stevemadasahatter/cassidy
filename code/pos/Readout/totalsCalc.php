<?php

include '../config.php';
include '../functions/auth_func.php';

session_start();


$total=bagTotals();
echo "<p width=30% align=right>";
echo "<table  style=\"position:relative;top:-2px;left:-2px;\">";
echo "<tr class=bagheader><td class=bagheader colspan=2>Summary</td></tr>";
//echo "<tr><td class=totalhead>Total</td><td class=totalnum>&pound;".number_format($total['total'],2)."</td></tr>";
echo "<tr><td class=totalhead>Items</td><td class=totalnum>".$total['count']."</td></tr>";
//echo "<tr onclick=\"javascript:changeDiscount();\"><td class=totalhead>Discount (non-Sale only)</td><td style=\"text-decoration:underline;\" class=totalnum>".$total['discount']."%</td></tr>";
//echo "<tr><td class=totalhead>Paid</td><td class=totalnum>".number_format($total['paid'],2)."</td></tr>";
echo "<tr><td class=totalgrand>Outstanding Total</td><td class=totalnumgrand>&pound;".number_format($total['outstanding'],2)."</td></tr>";
echo "</table></p>";
?>
