<?php 

define('TEST_TIMES', 500000 * 5);

echo PHP_EOL . 'Please input the power of 2: ';
// $power2 = fread(STDIN, 10);
$power2 = fscanf(STDIN, '%d')[0];
echo 'The result is: ' . my_power2($power2) . PHP_EOL;

$start_time = microtime(true);
$result = test_perform();
$spend_time1 = microtime(true) - $start_time;


$start_time = microtime(true);
$result2 = test_perform(false);
$spend_time2 = microtime(true) - $start_time;

echo 'my_power2() spent time: ' . $spend_time1 . ' seconds' . PHP_EOL;
echo 'power() spent time: ' . $spend_time2 . ' seconds' . PHP_EOL;

/**
 * more fast (185%) through digit left
 * than use pow() function
 */
function my_power2($power2, $num = 1)
{
    return $num << $power2;
}

/**
 * use php function of pow(),
 * slower than my_power2()
 */
function test_perform($myfunc = true)
{
    if ($myfunc){
        for($i = 0; $i < TEST_TIMES; $i++){
            $result = my_power2($i); 
        }
    } else {
        for($i = 0; $i < TEST_TIMES; $i++){
            $result = pow(2, $i); 
        }
    }

    return $result;
}
