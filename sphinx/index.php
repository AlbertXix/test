<?php
require 'sphinxapi.php';
header('Content-type:text/html;charset=utf-8');
?>

<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
<label>
<input style="width:400px;" type="text" name="keyword">
</label>
<label>
<input type="submit" name="Submit" value="sphinx搜索">
</label>
</form>

<?php
$mode = SPH_MATCH_ALL;
$host = "127.0.0.1";
$port = 9312;
$index = "rtindex_mysql";
$groupby = "";
$groupsort = "@group desc";
$filter = "group_id";

$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';

if (trim($keyword) =='') {
	exit('请输入关键词');
} else {
	echo '<p>关键词是：'.$keyword . '</p>';
}

try {
	$cl = new SphinxClient();
	$cl->SetServer($host, $port); //注意这里的主机
	$cl->SetConnectTimeout(1);
	$cl->SetMatchMode(SPH_MATCH_EXTENDED); //使用多字段模式
	$cl->SetWeights ( array ( 100, 1 ) );
	$res = $cl->Query($keyword, $index);
	$err = $cl->GetLastError();
	if ($err) exit($err);
	print_r($res);
} catch (Exception $e){
	echo $e->getMessage();
}

