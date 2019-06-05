<?php
$link=mysql_connect('localhost','harryxlb','ligexi007') or exit('cannt connect the database');
mysql_select_db('wind87');
mysql_query('set names utf8');
$sqlaid = 'select article_id from pw_cms_article';
$sqlcontent = 'select content from window';
$sqlupdate = 'update pw_cms_articlecontent set article_id=';
$sqlupdate2 = 'update pw_cms_articlecontent set content=';
$query = mysql_query($sqlcontent);
while($rs = mysql_fetch_array($query)){
	$upd = mysql_query($sqlupdate2.'"'.$rs['content'].'"') or exit('update the data error!');
	echo 'update the record success, content is<br /> '.$rs['content'].'<br />';
}

?>