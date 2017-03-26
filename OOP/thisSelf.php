<?php
class MyApp{
	public $country = 'china';
	public static $id = 100;
	protected static $user = 'harryxlb';
	private $password = 'ligexi';
	
	function __construct(){
		// 调用非静态成员用$this
		echo 'country: ' . $this->country . '<br />';
	}
	
	function __destruct(){
		echo '<p>I\'m destruct.';
	}
}

class Apple extends MyApp{
	function __construct(){
		// echo 'country: ' . $this->country;
	}

	function showCountry(){
		echo 'country: ' . $this->country . '<br />';
	}
	
	public static function showInfo(){
		self::$id = 200;
		// 调用静态成员用self/parent
		echo 'parent id: ' . parent::$id . '<br />';
		echo 'self ' . 'id: ' . self::$id . '<br />';
		echo 'user: '. parent::$user . '<br />';
	}
}

$MyApp = new MyApp();

$App = new Apple;
$App->showCountry();

Apple::showInfo();

