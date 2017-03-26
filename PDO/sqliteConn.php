<?php
/** sqlite数据库的3种连接方式 */

// 函数连接SQLite
echo '<h1>函数连接SQLite</h1>';
$dbhandle = sqlite_open('Article.db', 0777, $sqliteErr) or exit($sqliteErr);
$query = sqlite_query($dbhandle, 'SELECT title, content FROM Article LIMIT 25');
while ($entry = sqlite_fetch_array($query, SQLITE_ASSOC)) {
    echo 'title: ' . $entry['title'] . '<br />  content: ' . $entry['content'] . "<p></p><hr />";
}
sqlite_close($dbhandle);

// SQLite3专用连接
// echo '<h1>SQLite3专用连接</h1>';
// $DB = new SQLite3('Article.db');
// for ($x = 1; $x < 6; $x++){
	// $query = $DB->query("INSERT INTO Article (title, content, datetime) " . 
						// "VALUES ('标题" . $x . "', '内容" . $x . "', '" . date('Y-m-d H:i:s') . "')");
	// echo $x . ': 标题' . $x . ' ...<br />';
// }
// echo '更新完毕.<br /><hr />';
// while ($art = $rs->fetchArray()){
	// echo 'title: ' . htmlspecialchars($art['title']) . '<br />';
// ｝


// PDO版的连接方式 for SQLite
// echo '<h1>PDO版的连接方式 for SQLite</h1>';
$DB2 = new PDO("sqlite:Article.db");
$rs = $DB2->query('SELECT * FROM Article');
$rs->setFetchMode(PDO::FETCH_ASSOC);
print_r($rs->fetchAll());


