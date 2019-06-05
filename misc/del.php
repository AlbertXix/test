<?php 
error_reporting(E_ALL ^ E_NOTICE);

echo 'delete files: ' . PHP_EOL;
delFiles($argv[1], $argv[2]);
clearstatcache();

function delFiles($dir, $findFileName, $flags = GLOB_ONLYDIR){
	// if (!is_dir($dir)) return FALSE;
	if ($dir != '..' && $dir !='.')
		$filesArr = glob( $dir . DIRECTORY_SEPARATOR . '*', $flags );
	else return FALSE;
	// print_r($filesArr);exit;
	foreach ($filesArr as $file){
		if (basename($file) == $findFileName){
			echo 'Deleting file(folder): ' . $file . PHP_EOL;
			is_dir($file) ? rmdir($file) : unlink($file);
		} else { 
			echo 'Scanning folder: ' . $file . PHP_EOL;
			delFiles($file, $findFileName);
		}
	}
}

function delFiles2($dir, $findFileName){
	if (is_dir($dir)) {
		if ($dh = opendir($dir)){
			while (($file = readdir($dh)) !== FALSE) {
				if ($file != '..' && $file != '.') {
					chdir($dir);
					$fileFullPath = $dir . DIRECTORY_SEPARATOR . basename( $file );
					if ($file == $findFileName){
						echo 'Deleting file(folder): ' . $fileFullPath . PHP_EOL;
						// is_dir($fileFullPath) ? rmdir($fileFullPath) : unlink($fileFullPath);
						chdir($file);
						// if (substr($file, 0, 1) == '.') echo 'dot start';
					} else { 
						echo 'Scanning file: ' . $fileFullPath . PHP_EOL;
						delFiles($fileFullPath, $findFileName);
					}
				}
			}
			// closedir($dh);
		}
	} else return FALSE;
}