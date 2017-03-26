<?php 

$rows = array(
    array('id' => 1, 'pid' => 0, 'name' => '江西省'),
    array('id' => 2, 'pid' => 0, 'name' => '黑龙江省'),
    array('id' => 3, 'pid' => 1, 'name' => '南昌市'),
    array('id' => 4, 'pid' => 2, 'name' => '哈尔滨市'),
    array('id' => 5, 'pid' => 2, 'name' => '鸡西市'),
    array('id' => 6, 'pid' => 4, 'name' => '香坊区'),
    array('id' => 7, 'pid' => 4, 'name' => '南岗区'),
    array('id' => 8, 'pid' => 6, 'name' => '和兴路'),
    array('id' => 9, 'pid' => 7, 'name' => '西大直街'),
    array('id' => 10, 'pid' => 8, 'name' => '东北林业大学'),
    array('id' => 11, 'pid' => 9, 'name' => '哈尔滨工业大学'),
    array('id' => 12, 'pid' => 8, 'name' => '哈尔滨师范大学'),
    array('id' => 13, 'pid' => 1, 'name' => '赣州市'),
    array('id' => 14, 'pid' => 13, 'name' => '赣县'),
    array('id' => 15, 'pid' => 13, 'name' => '于都县'),
    array('id' => 16, 'pid' => 14, 'name' => '茅店镇'),
    array('id' => 17, 'pid' => 14, 'name' => '大田乡'),
    array('id' => 18, 'pid' => 16, 'name' => '义源村'),
    array('id' => 19, 'pid' => 16, 'name' => '上坝村')
);

function genTree($rows, $id='id', $pid='pid')
{
    $items = array();
    foreach ($rows as $row) $items[$row[$id]] = $row;
    foreach ($items as $item) $items[$item[$pid]]['son'][$item[$id]] = &$items[$item[$id]];
    return isset($items[0]['son']) ? $items[0]['son'] : array(); 
}


// print_r(genTree($rows));

$data = array();

function showTree($rows, $level = 0) {
    global $data;

    foreach ($rows as $key => $value) {
        $data[] = array('id' => $value['id'], 'pid' => $value['pid'], 
                'name' => str_repeat('==', $level) . $value['name']
            );

        if (isset($value['son'])) {
            $level++;
            // $value['son']['level'] = $level + 1;
            showTree($value['son'], $level);
        }
    }
    return $data;
}

$rows = genTree($rows);
print_r(showTree($rows));

/////////////////////////////////
// another function
// function data2arr($tree, $rootId = 0, $level = 0) {  
//     foreach($tree as $leaf) {  
//         if($leaf['pid'] == $rootId) {  
//             echo str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . $leaf['id'] . ' ' . $leaf['name'] . '<br/>';  
//             foreach($tree as $l) {  
//                 if($l['pid'] == $leaf['id']) {  
//                     data2arr($tree, $leaf['id'], $level + 1);  
//                     break;  
//                 }  
//             }  
//         }  
//     }  
// }  
  
// // another function
// function arr2tree($tree, $rootId = 0) {  
//     $return = array();  
//     foreach($tree as $leaf) {  
//         if($leaf['pid'] == $rootId) {  
//             foreach($tree as $subleaf) {  
//                 if($subleaf['pid'] == $leaf['id']) {  
//                     $leaf['children'] = arr2tree($tree, $leaf['id']);  
//                     break;  
//                 }  
//             }  
//             $return[] = $leaf;  
//         }  
//     }  
//     return $return;  
// }  

// function tree2html($tree) {  
//     echo '<ul>';  
//     foreach($tree as $leaf) {  
//         echo '<li>' .$leaf['name'];  
//         if( ! empty($leaf['children']) ) tree2html($leaf['children']);  
//         echo '</li>' . PHP_EOL;  
//     }  
//     echo '</ul>' . PHP_EOL;  
// }  

// // print_r(arr2tree($rows));
// $tree = arr2tree($rows);
// echo tree2html($tree);
