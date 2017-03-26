<?
class whois{
	var $RESULT;
	var $FOUND;
	var $ERROR;

	function getServer($suffix,$isIDN=0)
	{
		if($isIDN==1)return "whois.hichina.com";
		$server=array();
		$server['cn']="whois.cnnic.net.cn";
		$server['中国']="whois.hichina.com";
		$server['公司']="whois.hichina.com";
		$server['网络']="whois.hichina.com";
		$server['com']="whois.internic.net";
		$server['net']="whois.internic.net";
		$server['org']="whois.pir.org";
		$server['biz']="whois.neulevel.biz";
		$server['info']="whois.afilias.info";
		$server['us']="whois.nic.us";
		$server['cc']="whois.nic.cc";
		$server['tv']="whois.www.tv";
		$server['in']="whois.inregistry.net";
		$server['hk']="whois.hkirc.net.hk";
		$server['tw']="whois.twnic.net.tw";
		$ser = $server[$suffix];
		if(!$ser) $ser = "whois.internic.net";
		return $ser;
	}  

	function query($domain)
	{
		$this->RESULT = "";
		$suffix = substr($domain,strrpos($domain,".")+1);
		$server = $this->getServer($suffix,ord($domain[0])>127);
		if(!($fp = fsockopen($server, 43))){
		    $this->ERROR++; 
			$this->FOUND=0;
			return 0;
		}
		fwrite($fp, $domain."\r\n");
		while (!feof($fp)) {
			$line = fgets($fp, 1024);
			$this->RESULT .= $line;
			if(eregi( "No match for", $line)): 
					$this->FOUND=0; 
			elseif(eregi( "Not found", $line)): 
					$this->FOUND=0;
			elseif(eregi( "not available for", $line)): 
					$this->FOUND=0;
			elseif(eregi( "no matching record", $line)): 
					$this->FOUND=0; 
            elseif(eregi( "not available for", $line)): 
					$this->FOUND=0; 
			elseif(eregi( "WHOIS database is down",$line)): 
					$this->ERROR++; 
					$this->FOUND=0; 
			elseif(eregi( "Please wait a while and try again",$line)): 
					$this->ERROR++; 
					$this->FOUND=0; 
					break;
			else:
				    $this->FOUND=1;
			endif;
		}
		if(eregi("(.cn)$",$domain) && function_exists("iconv")) $this->RESULT = iconv("utf-8","gb2312",$this->RESULT);
		fclose($fp);
	}
}
?>