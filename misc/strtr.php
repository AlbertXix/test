<?php
$arr = array("Hello" => "Hi",
			 "world" => "earth",
			 "nihao" =>"ÄãºÃ",
			 "china" =>"ÖÐ¹ú");
echo strtr("Hello world",$arr);
echo strtr("nihao china",$arr);
?>