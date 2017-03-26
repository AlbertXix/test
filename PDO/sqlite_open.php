<?php
// if ($db = sqlite_open('foo', 0666, $sqliteerror)) { 
    // sqlite_query($db, 'CREATE TABLE foo (bar varchar(10))');
    // sqlite_query($db, "INSERT INTO foo VALUES ('fnord')");
    // $result = sqlite_query($db, 'select bar from foo');
    // var_dump(sqlite_fetch_array($result)); 
// } else {
    // die($sqliteerror);
// }

$dbhandle = sqlite_open('Article.db', 0777, $sqliteErr) or exit($sqliteErr);
$query = sqlite_query($dbhandle, 'SELECT title, content FROM Article LIMIT 25');
while ($entry = sqlite_fetch_array($query, SQLITE_ASSOC)) {
    echo 'title: ' . $entry['title'] . '<br />  content: ' . $entry['content'] . "<p></p><hr />";
}
?>