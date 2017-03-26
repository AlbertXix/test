<?php 
// cUrl download file to local

if (isset($_GET['rfile']) && isset($_GET['lfile']))
	curl_download($_GET['rfile'], $_GET['lfile']);

if ($argc == 3 && preg_match('#^http[s]*://#i', $argv[1]))
	curl_download($argv[1], $argv[2]);

if ($argc == 3 && ($argv[2] === 0 || $argv[2] === 1))
	print_r(checkUrlStatus($argv[1], $argv[2]));

function curl_download($remoteFile, $localFile) {
	$fp = fopen($localFile, "wb");
	$ch = curl_init($remoteFile);
	// curl_setopt($ch, CURLOPT_URL, $remoteFile);
	// curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_NOBODY, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
	$fcontent = curl_exec($ch);
	fwrite($fp, $fcontent);

	$errno = curl_errno($ch);
	$errmsg = curl_error($ch);

	curl_close($ch);
	fclose($fp);   
	if($errno){
		echo 'Error: ' . $errmsg . "($errno)";
		exit;
	}
}

function checkUrlStatus($url, $returnAll = 0){
	$headersArr = get_headers($url);
	$statusLine = explode(" ", $headersArr[0]);
	// if (!$returnAll) return $statusLine[1];
	if (!$returnAll) return $headersArr[0];
	return implode(PHP_EOL, $headersArr);
}