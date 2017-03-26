<?php 

$categories = array(
    array( 'id' => 1, 'pid' => 0, 'name' => '一年级'),
    array( 'id' => 2, 'pid' => 0, 'name' => '二年级'),
    array( 'id' => 3, 'pid' => 0, 'name' => '三年级'),
    array( 'id' => 4, 'pid' => 1, 'name' => '一年级<1>班'),
    array( 'id' => 5, 'pid' => 1, 'name' => '一年级<2>班'),
    array( 'id' => 6, 'pid' => 2, 'name' => '二年级<1>班'),
    array( 'id' => 7, 'pid' => 2, 'name' => '二年级<2>班'),
    array( 'id' => 8, 'pid' => 3, 'name' => '三年级<1>班'),
    array( 'id' => 9, 'pid' => 3, 'name' => '三年级<2>班'),
);

$result = '';

$treeCategory = function($pid = 0, $level = 0) use (&$treeCategory, $categories, $result) {
    foreach ($categories as $key => $category) {
        if ($category['pid'] == $pid){
            $level++;
            $category['level'] = $level;
            $result .= str_repeat('  ', $level) . '--' . $category['name'] . PHP_EOL;
            $result .= $treeCategory($category['id'], $level);
        }
    }
    return $result;
};

print_r($treeCategory(0));

