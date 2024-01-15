<?php

session_start();
print_r($_SESSION);

echo "<button onclick=\"javascript:location.reload();\" >Close</button>";
?>