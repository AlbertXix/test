<?php	
	class Fruit
	{
		static $num = 1;
		public $ID = 001;
		public static $user = 'HarryXLB';
		protected static $password = 'Letmein009';
		private $memory = 'A good man.';
		function __construct()
		{
			echo 'I\'m from class Fruit. <br />';
		}
	}
	
	class Apple extends Fruit
	{
		static $num = 1;
		function __construct()
		{
			// parent::__construct();
			echo 'I\'m from class Apple<br />';
			// self::showMsg();
		}
		
		static function showMsg()
		{
			echo 'Message: Hello .' . parent::$user . '<br />';
			// echo 'Your password is: ' . parent::$password;
			self:$num++;
			echo 'Password: ' . parent::$password . '<br />';
		}
	}
	Apple::showMsg(); // or $Apple->showMsg();
	$Apple = new Apple();
	echo 'ID: ' . $Apple->ID . '<br />';
	// echo $Apple->password . '<br />'; //Here make a error because 'Fatal error: Cannot access protected property Apple::$password'
	// echo $Apple->memory;
	