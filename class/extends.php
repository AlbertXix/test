<?php
error_reporting(E_ALL ^ E_NOTICE);

const TBPRE = '58tc_';

class DB{
	static public $db;
	protected $user = 'xlb';
	
	function __construct(){
		
	}
	
	/**singleton*/
	static function instance(){
		if (is_null($db))
			self::$db = new DB;
		return self::$db;
	}
	
	static function table($strTable){
		return TBPRE . $strTable;
	}
	
	public function get(){
		echo 'you get it, table prefix is: ' . TBPRE;
	}
}

class MySql extends DB{
	function __construct(){
		var_dump($this->user);
		$this->db = self::instance();
	}
	
	public function show(){
		//perform object links
		return $this->db->get();
	}
	
}

$my = new MySql;
$my->show();

