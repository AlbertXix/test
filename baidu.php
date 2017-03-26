<doctype html>
<html>
<head><title>Baidu Spider</title></head>
<body>
	<div id="main">
		<h1>模拟百度蜘蛛</h1>
		<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post">
			<input type="text" name="txtURL" />
			<input type="submit" value="Start" />
		</form>
		<?php
		if (isset($_POST['txtURL'])){
			$url=trim($_POST['txtURL']);
			$data=imitateSpider($url);
			print_r($data);
		}

function imitateSpider($url){
$ci = curl_init();   //初始化一个CURL的会话
 
/*
Fatal error: Call to undefined function curl_init()
 
设置php.ini配置文件
extension=php_curl.dll
把libeay32.dll和ssleay32.dll拷贝到c:\windows\system32里面，重启Apache
*/
 
 
$user_agent = "Baiduspider+(+http://www.baidu.com/search/spider.htm)";//这里模拟的是百度蜘蛛
//curl_setopt($ci,CURLOPT_PROXY,'74.125.71.99');
curl_setopt($ci, CURLOPT_URL, $url);
curl_setopt($ci, CURLOPT_HEADER, false);
curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ci, CURLOPT_REFERER, 'http://www.baidu.com');//这里写一个来源地址，可以写要抓的页面的首页
curl_setopt($ci, CURLOPT_USERAGENT, $user_agent);
$temp=curl_exec($ci);//执行CURL会话
curl_close($ci);
return $temp;
}
 
?>
	</div>
</body>
</html>