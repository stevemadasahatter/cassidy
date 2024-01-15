<?php

$printers=exec('lpstat -a | wc -l');
if ($printers<>4)
{
    $return=1;
}
else
{
    $return=0;
}

echo $return;
?>