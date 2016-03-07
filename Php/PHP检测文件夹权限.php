<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
	<title>PHP检测文件夹权限</title>
	</head>
	<body bgcolor="#000000" text="#FFFFFF">
	<form action="" method="POST">
	单个目录路径:<input type="text" name="name">
	<input type="submit" name="submit" value="开始检测">
	</form>
	常用文件路径:<br>
	c:\windows<br>
	c:\Documents and Settings<br>
        c:\Program Files<br>
	c:\Documents and Settings\All Users\Application Data\Microsoft\Media Index<br>
 	C:\php\PEAR<br>
 	C:\Program Files\Zend\ZendOptimizer-3.3.0<br>
	C:\Program Files\Common Files<br>
	C:\7i24.com\iissafe\log<br>
	C:\windows<br>
	C:\RECYCLER<br>
	C:\windows\temp<br>
	c:\Program Files\Microsoft SQL Server\90\Shared\ErrorDumps<br>
	f:\recycler<br>
	C:\Program Files\Symantec AntiVirus\SAVRT<br>
	C:\WINDOWS\7i24.com\FreeHost<br>
	C:\php\dev<br>
	C:\~1<br>  
	C:\System Volume Information<br>
	C:\Program Files\Zend\ZendOptimizer-3.3.0\docs<br>
	C:\Documents and Settings\All Users\DRM<br>
	C:\Documents and Settings\All Users\Application Data\McAfee\DesktopProtection<br>
	C:\Documents and Settings\All Users\Application Data\360safe\softmgr<br>
	C:\Program Files\Microsoft SQL Server\90\Shared\ErrorDumps<br>
	<p>BY:10086 QQ:10086 blog:www.chouwazi.com</p>
	<hr>
	<body>
 
 
</html>
 
 
<?php	
	set_time_limit(120);
	if($_POST['submit']){
     $a=$_POST['name'];
	if(file_exists($a)){
 
	dir_File($a);
	}else{
	echo "文件目录无权限或不存在";
	}
	}
 
 
	function dir_File($dirname){
 
	$dir_handle=@opendir($dirname);
	while($fileName=@readdir($dir_handle)){
	if($fileName!="." && $fileName!=".."){
	$dirFile=$dirname."\\".$fileName;
	//echo $dirFile."<br>";
	if(is_dir($dirFile)){
	//echo $dirFile."这是一个目录"."<br>";
   if(is_writable($dirFile)){
	echo $dirFile."这个目录可写"."<br>";
	echo $dir=dir_File($dirFile);
 
	}
	}
	}
	}
	}
 
?>