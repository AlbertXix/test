<?php 
//全国，判断条件是$REQUEST_URI是否含有html 
if (!strpos($_SERVER["REQUEST_URI"],".html")) 
{ 
$page="http://qq.ip138.com/weather/"; 
$html = file_get_contents($page,'r'); 
$pattern="/<B>全国主要城市、县当天和未来五天天气趋势预报在线查询<\/B>(.*?)<center style=\"padding\:3px\">/si"; 
//正则匹配之间的html 
preg_match($pattern,$html,$pg); 
echo ""; 
//正则替换远程地址为本地地址 
$p=preg_replace('/\/weather\/(\w+)\/index.htm/', $_SERVER['SCRIPT_NAME'] . '/$1.html', $pg[1]); 
echo $p; 
} 
//省，判断条件是$REQUEST_URI是否含有？ 
else if(!strpos($_SERVER["REQUEST_URI"],"?")){ 
//yoyo推荐的使用分割获得数据，这里是获得省份名称 
$province=explode("/",$_SERVER["REQUEST_URI"]); 
$province=explode(".",$province[count($province)-1]); 
$province=$province[0]; 
//被注释掉的是我自己写出来的正则，感觉写的不好，但效果等同上面 
//preg_match('/[^\/]+[\.(html)]$/',$_SERVER["REQUEST_URI"],$pro); 
//$province=preg_replace('/\.html/','',$pro[0]); 
$page="http://qq.ip138.com/weather/".$province."/index.htm"; 
//获取html数据之前先尝试打开页面，防止恶意输入地址导致出错 
if (!@fopen($page, "r")) { 
die("对不起，该地址不存在!<a href=javascript:history.back(1)>点击这里返回</a>"); 
exit(0); 
} 
$html = file_get_contents($page,'r'); 
$pattern="/五天天气趋势预报<\/B>(.*?)请输入输入市/si"; 
preg_match($pattern,$html,$pg); 
echo ""; 
//正则替换，获取省份，城市 
$p=preg_replace('/\/weather\/(\w+)\/(\w+).htm/', '$2.html?pro=$1', $pg[1]); 
echo $p; 
} 
else { 
//市，通过get传递省份 
$pro=$_REQUEST['pro']; 
$city=explode("/",$_SERVER["REQUEST_URI"]); 
$city=explode(".",$city[count($city)-1]); 
$city=$city[0]; 
//preg_match('/[^\/]+[\.(html)]+[\?]/',$_SERVER["REQUEST_URI"],$cit); 
//$city=preg_replace('/\.html\?/','',$cit[0]); 
$page="http://qq.ip138.com/weather/".$pro."/".$city.".htm"; 
if (!@fopen($page, "r")) { 
die("对不起，该地址不存在!<a href=javascript:history.back(1)>点击这里返回</a>"); 
exit(0); 
} 
$html = file_get_contents($page,'r'); 
$pattern="/五天天气趋势预报<\/B>(.*?)请输入输入市/si"; 
preg_match($pattern,$html,$pg); 
echo ""; 
//获取真实的图片地址 
$p=preg_replace('/\/image\//', 'http://qq.ip138.com/image/', $pg[1]); 
echo $p; 
} 
?>