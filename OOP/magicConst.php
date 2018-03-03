<?php
// 常用魔术常量

namespace My;

class Magic{
	public static function showMagic(){
		echo '__CLASS__: ' . __CLASS__ . PHP_EOL;
		echo '__METHOD__: ' . __METHOD__ . PHP_EOL;
		echo '__FUNCTION__: ' . __FUNCTION__ . PHP_EOL;
		ECHO '__LINE__: ' . __LINE__ . PHP_EOL;
	}
}

Magic::showMagic();
