<?php 
fwrite(STDOUT, 'What\' your name:');
$user = fgets(STDIN);
fwrite(STDOUT, 'Hello, ' . $user);