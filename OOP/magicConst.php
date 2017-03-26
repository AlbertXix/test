<?php
// 常用魔术常量

class Magic{
	public static function showMagic(){
		echo '__CLASS__: ' . __CLASS__ . '<br />';
		echo '__METHOD__: ' . __METHOD__ . '<br />';
		echo '__FUNCTION__: ' . __FUNCTION__ . '<br />';
		ECHO '__LINE__: ' . __LINE__ . '<br />';
	}
}

Magic::showMagic();
