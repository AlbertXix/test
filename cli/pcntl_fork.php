<?php 

$sub_process_id = pcntl_fork();

if ($sub_process_id == -1){
    die('Could not create sub process');
} else if ($sub_process_id){
    pcntl_wait($status);
} else {
    echo 'fork process ok' . PHP_EOL;
}

