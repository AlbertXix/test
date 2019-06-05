<?php 
abstract class AbsCls { 
	public static function say($str){ 
		echo "abstract class says: " . $str . PHP_EOL; 
	} 
}

AbsCls::say('hello');