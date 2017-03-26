<?php 
class Purl{
	private $strUrl, $method;
    private $retHtml = '';

	function __construct($strUrl, $method = 'c', $port = 80){
		$this->strUrl = $strUrl;
		$this->method = $method;
		echo($this->getHtml());
	}

	public function getHtml(){
		if (!$this->strUrl) false;
	    switch($this->method){
			default:
			case 'c': return $this->doCurl(); break;
			case 'g': return $this->doGetContents(); break;
			case 's': return $this->doSockFile(); break;
		}	
	}

	protected function doCurl(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->strUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$this->retHtml = curl_exec($ch);
		return $this->retHtml;
	}

	protected function doGetContents(){
		$this->retHtml = file_get_contents($this->strUrl);
		return $this->retHtml;

	}
	protected function doSockFile(){
		$sock = fsock_open($this->strUrl, $port, $errNo, $errStr, 20);
		if (!sock) return 'Socket error occurred, cannot connect the server.' . PHP_EOL . $errStr;
		fwrite($sock, 'GET / HTTP/1.1' . PHP_EOL);
		fwrite($sock, 'HOST: ' . $this->strUrl . PHP_EOL);
		fwrite($sock, 'Connection: closed' . PHP_EOL . PHP_EOL);
		while(!feof($sock)){
			$this->retHtml .= fgets($sock, 1024);
		}
		return $this->retHtml;
	}
}
