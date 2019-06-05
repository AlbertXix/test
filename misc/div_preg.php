<?php
$str = '<div class="am1">
    <div class="image">1111111111</div>
    <div class="image">2222222222</div>
    <a href="test.php">link1</a>
</div>
<div class="am1">
     <div class="image">1111111111</div>
     <img src="./tt.gif" />
     <div class="image">2222222222</div>
</div>';
if (preg_match_all('#<div[^>]*>((?>[^</div>]+|<a.+?>.+?</a>|<img.+?/>|(?R))*)</div>#is', $str, $matches)){
    print_r($matches);
}
 ?>