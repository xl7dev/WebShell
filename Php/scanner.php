<?php
 
set_time_limit(0);//设置程序执行时间
ob_implicit_flush(True);
ob_end_flush();
$url = isset($_REQUEST['url'])?$_REQUEST['url']:null; 

/*端口扫描代码*/
function check_port($ip,$port,$timeout=0.1) {
 $conn = @fsockopen($ip, $port, $errno, $errstr, $timeout);
 if ($conn) {
 fclose($conn);
 return true;
 }
}

 
function scanip($ip,$timeout,$portarr){
foreach($portarr as $port){
if(check_port($ip,$port,$timeout=0.1)==True){
echo 'Port: '.$port.' is open<br/>';
@ob_flush();
@flush();
 
}
 
}
}

echo '<html>
<form action="" method="post">
<input type="text" name="startip" value="Start IP" />
<input type="text" name="endip" value="End IP" />
<input type="text" name="port" value="80,8080,8888,1433,3306" />
Timeout<input type="text" name="timeout" value="10" /><br/>
<button type="submit" name="submit">Scan</button>
</form>
</html>
';

if(isset($_POST['startip'])&&isset($_POST['endip'])&&isset($_POST['port'])&&isset($_POST['timeout'])){
    
$startip=$_POST['startip'];
$endip=$_POST['endip'];
$timeout=$_POST['timeout'];
$port=$_POST['port'];
$portarr=explode(',',$port);
$siparr=explode('.',$startip);
$eiparr=explode('.',$endip);
$ciparr=$siparr;
if(count($ciparr)!=4||$siparr[0]!=$eiparr[0]||$siparr[1]!=$eiparr[1]){
exit('IP error: Wrong IP address or Trying to scan class A address');
}
if($startip==$endip){
echo 'Scanning IP '.$startip.'<br/>';
@ob_flush();
@flush();
scanip($startip,$timeout,$portarr);
@ob_flush();
@flush();
exit();
}
 
if($eiparr[3]!=255){
$eiparr[3]+=1;
}
while($ciparr!=$eiparr){
$ip=$ciparr[0].'.'.$ciparr[1].'.'.$ciparr[2].'.'.$ciparr[3];
echo '<br/>Scanning IP '.$ip.'<br/>';
@ob_flush();
@flush();
scanip($ip,$timeout,$portarr);
$ciparr[3]+=1;
 
if($ciparr[3]>255){
$ciparr[2]+=1;
$ciparr[3]=0;
}
if($ciparr[2]>255){
$ciparr[1]+=1;
$ciparr[2]=0;
}
}
}

/*内网代理代码*/

function getHtmlContext($url){ 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_HEADER, TRUE);    //表示需要response header 
    curl_setopt($ch, CURLOPT_NOBODY, FALSE); //表示需要response body 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 120); 
    $result = curl_exec($ch); 
  global $header; 
  if($result){ 
       $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE); 
       $header = explode("\r\n",substr($result, 0, $headerSize)); 
       $body = substr($result, $headerSize); 
  } 
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') { 
        return $body; 
    } 
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '302') { 
    $location = getHeader("Location"); 
    if(strpos(getHeader("Location"),'http://') == false){ 
      $location = getHost($url).$location; 
    } 
        return getHtmlContext($location); 
    } 
    return NULL; 
} 

function getHost($url){ 
    preg_match("/^(http:\/\/)?([^\/]+)/i",$url, $matches); 
    return $matches[0]; 
} 
function getCss($host,$html){ 
    preg_match_all("/<link[\s\S]*?href=['\"](.*?[.]css.*?)[\"'][\s\S]*?>/i",$html, $matches); 
    foreach($matches[1] as $v){ 
    $cssurl = $v; 
        if(strpos($v,'http://') == false){ 
      $cssurl = $host."/".$v; 
    } 
    $csshtml = "<style>".file_get_contents($cssurl)."</style>"; 
    $html .= $csshtml; 
  } 
  return $html; 
} 

if($url != null){ 

    $host = getHost($url); 
    echo getCss($host,getHtmlContext($url)); 
}
?>
