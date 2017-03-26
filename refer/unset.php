<?php
$a = 1;
$b =& $a;
$a = 2;
unset ($a);
echo 'now $b is: ' . $b;
?> 