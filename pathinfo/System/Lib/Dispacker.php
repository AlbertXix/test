<?php 

class Dispacher {

	public static $controller = "Index";

	public static $method = "index";

	public static $methodSuffix = '.action';

	public static $isDefaultModule = FALSE;

	static public function forward() {

		self::$controller .= CONTROLLER_SUFFIX;

		$pathbits = array();
		if (isset($_SERVER['PATH_INFO'])){
			$pathbits = explode("/",  $_SERVER['PATH_INFO']);
		}

		// echo $_SERVER['PATH_INFO'] . PHP_EOL;
		// print_r(pathinfo($_SERVER['PATH_INFO']));
		// print_r($pathbits);exit;

		if ( isset($pathbits[1]) && trim($pathbits[1]) != "" ){
			$module = ucfirst($pathbits[1]) ;
			$modList = explode(',', MODULE_LIST);
			if (isset($pathbits[2]))
				self::$controller = ucfirst($pathbits[2]) . CONTROLLER_SUFFIX;
			if (isset($pathbits[3]))
				self::$method = str_replace(self::$methodSuffix, '', $pathbits[3]);
			if ($module != DEFAULT_MODULE && !in_array($module, $modList)) { 
				$module = DEFAULT_MODULE;
				self::$controller = ucfirst($pathbits[1]) . CONTROLLER_SUFFIX;
				if (isset($pathbits[2]))
					self::$method = str_replace(self::$methodSuffix, '', $pathbits[2]);
			}
		}

		// self::$method = ucwords(self::$method);
		// $firstLetter = substr(self::$method, 0, 1);
		// self::$method = str_replace($firstLetter, strtolower($firstLetter), self::$method);
		$file = ROOT_DIR . CONTROLLER_DIR . $module . '/'. self::$controller . '.php';
		if (!is_file($file)){
			throw new Exception("File not found: $file, controller: " . self::$controller);
		}else{
			require_once $file;
			call_user_func_array( self::$controller . '::' . self::$method . '--', array(self::$controller) ) ;
		}	
	}
}