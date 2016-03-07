<?php

$timestamp = time();
$errmsg = '';

$dberror = $this->error();
$dberrno = $this->errno();

if($dberrno == 1114) {

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gbk">
<title>Max Onlines Reached</title>
</head>
<body bgcolor="#FFFFFF">
<table cellpadding="0" cellspacing="0" border="0" width="600" align="center" height="85%">
  <tr align="center" valign="middle">
    <td>
    <table cellpadding="10" cellspacing="0" border="0" width="80%" align="center" style="font-family: Verdana, Tahoma; color: #666666; font-size: 9px">
    <tr>
      <td valign="middle" align="center" bgcolor="#EBEBEB">
        <br /><b style="font-size: 10px">Forum onlines reached the upper limit</b>
        <br /><br /><br />Sorry, the number of online visitors has reached the upper limit.
        <br />Please wait for someone else going offline or visit us in idle hours.
        <br /><br />
		<b>Config.</b>:  <a href='index.php?action=config'>Return</a>
      </td>
    </tr>
    </table>
    </td>
  </tr>
</table>
</body>
</html>
<?
	exit();
}else if($dberrno == 1045) {
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gbk">
<title>数据库连接失败</title>
</head>
<body bgcolor="#FFFFFF">
<table cellpadding="0" cellspacing="0" border="0" width="600" align="center" height="85%">
  <tr align="center" valign="middle">
    <td>
    <table cellpadding="10" cellspacing="0" border="0" width="80%" align="center" style="color: #666666; font-size: 12px">
    <tr>
      <td valign="middle" align="center" bgcolor="#EBEBEB" height="80">
        数据库连接失败，请返回连接设置。<br/><br/>
		<a href='index.php?action=config'>连接设置</a>
      </td>
    </tr>
    </table>
    </td>
  </tr>
</table>
</body>
</html>
<?php
	exit;
}
else {

	if($message) {
		$errmsg = "<b>Discuz! info</b>: $message\n\n";
	}

	$errmsg .= "<b>Time</b>: ".gmdate("Y-n-j g:ia", $timestamp)."\n";
	$errmsg .= "<b>Script</b>: ".$GLOBALS['phpself']."\n\n";
	if($sql) {
		$errmsg .= "<b>SQL</b>: ".htmlspecialchars($sql)."\n";
	}
	$errmsg .= "<b>Error</b>:  $dberror\n";
	$errmsg .= "<b>Errno.</b>:  $dberrno\n";
	$errmsg .= "<b>Config.</b>:  <a href='index.php?action=config'>Return</a>";

	echo "<p style=\"font-family: Verdana, Tahoma; font-size: 11px; background: #FFFFFF;\">";
	echo nl2br($errmsg);
	echo '</p>';
	exit();
}

?>