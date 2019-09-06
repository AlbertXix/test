 <?php

$wn = $_GET['wn'] ? intval($_GET['wn']) : 0;

$keywords = [
    'apple',
    'microsoft',
    'facebook',
    'google',
    'goole-cn',
    'goole-hk',
    'bing',
    'bing-cn',
    'bing-tw',
    'hao123',
    'so',
    'sofun',
    'soche',
    'hao456',
];

$keywords = array_slice($keywords, 0, $wn);
shuffle($keywords);

//if (empty($wn)) $rnd_kws = $keywords;
//else {
//    shuffle($keywords);
//    $rnd_kws = array_map(function($v) use ($wn) {
//       static $k = 0;
//       if ($k <= $wn) return $v;
//       $k++;
//    }, $keywords);
//    $rnd_kws = array_values(array_filter($rnd_kws));
//}
 
ejson($keywords);

function ejson($arr){
    header('Content-Type:application/json;charset=UTF-8');
    echo json_encode($arr);
    exit;
}
