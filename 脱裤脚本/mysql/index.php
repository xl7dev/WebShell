<?php
if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_POST_FILES;
}
$phpself = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
require_once './config.inc.php';
require_once './db_mysql.class.php';

$excepttables = array();

$action = $_GET['action'];
if( empty($action) ) $action = "config";
$ajax = $_GET['ajax'];
$ajax = empty($ajax)?0:1;

if($action=="config") {
	if( !empty($_POST['valuesubmit']) ){
		$dbhost_new = setconfig($_POST['dbhost']);
		$dbuser_new = setconfig($_POST['dbuser']);
		$dbpw_new = setconfig($_POST['dbpw']);
		$dbname_new = setconfig($_POST['dbname']);
		writeconfig($dbhost_new,$dbuser_new,$dbpw_new,$dbname_new);
		cpmsg("连接设置: 设置成功，程序将自动返回。", $phpself."?action=".$action);
	}
	cpconfig();
}
else if($action=="showdatabase"){
	$thost = $_GET['thost'];
	$tuser = $_GET['tuser'];
	$tpw = $_GET['tpw'];
	$conn = @mysql_connect($thost, $tuser, $tpw);
	if( $conn ){
		if($query = @mysql_query("SHOW DATABASES")){
			$databaseshtml = "";
			while( $database = @mysql_fetch_array($query,MYSQL_ASSOC) ){
				$databaseshtml .= "<option value=\"".$database['Database']."\">".$database['Database']."</option>";
			}
			echo $databaseshtml;
		}
	}else{
		echo "";
	}
	exit;
}
else{
	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$dbuser = $dbpw = $dbname = $pconnect = NULL;
}

if($action=="export") {
	if( !empty($_POST['exportsubmit']) ){
		$type = $_POST['type'];
		$setup = $_POST['setup'];
		$sqlcharset = $_POST['sqlcharset'];
		$sqlcompat = $_POST['sqlcompat'];
		$usezip = $_POST['usezip'];
		$method = $_POST['method'];
		$sizelimit = $_POST['sizelimit'];
		$volume = $_POST['volume'];
		$filename = $_POST['filename'];


		$db->query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');

		if(!$filename || preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $filename)) {
			cpmsg('您没有输入备份文件名或文件名中使用了敏感的扩展名，请返回修改。');
		}
		if($type == 'alldata') {
			$tables = arraykeys2(fetchtablelist(), 'Name');
		}elseif($type == 'custom') {
			$tables = array();
			if(empty($setup)) {
				$customtablesnew = stripslashes($_POST['customtables']);
				$tables = unserialize($customtablesnew);
			}else{
				$customtables = $_POST['customtables'];
				$customtablesnew = empty($customtables)? '' : serialize($customtables);
				$tables = & $customtables;
			}
			if( !is_array($tables) || empty($tables)) {
				cpmsg('您至少需要选择一个数据表进行备份，请返回修改。');
			}
		}

		$volume = intval($volume) + 1;
		$idstring = '# Identify: '.base64_encode(time().",$version,$type,$method,$volume")."\n";
		$dumpcharset = $sqlcharset ? $sqlcharset : str_replace('-', '', $dbcharset?$dbcharset:"gbk");
		$setnames = ($sqlcharset && $db->version() > '4.1' && (!$sqlcompat || $sqlcompat == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';
		if($db->version() > '4.1') {
			if($sqlcharset) {
				$db->query("SET NAMES '".$sqlcharset."';\n\n");
			}
			if($sqlcompat == 'MYSQL40') {
				$db->query("SET SQL_MODE='MYSQL40'");
			} elseif($sqlcompat == 'MYSQL41') {
				$db->query("SET SQL_MODE=''");
			}
		}

		$backupfilename = './data/'.str_replace(array('/', '\\', '.'), '', $filename);
		if($usezip) {
			require_once './zip.func.php';
		}

		if($method == 'multivol') {

			$sqldump = '';
			$tableid = intval($_POST['tableid']);
			$startfrom = intval($_POST['startfrom']);
			$startrow = $_POST['startrow'];
			$extendins = $_POST['extendins'];
			$sqlcompat = $_POST['sqlcompat'];
			$usehex = $_POST['usehex'];
			$complete = TRUE;
			for(; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < $sizelimit * 1000; $tableid++) {
				$sqldump .= sqldumptable($tables[$tableid], $startfrom, strlen($sqldump));
				if($complete) {
					$startfrom = 0;
				}
			}

			$dumpfile = $backupfilename."-%s".'.sql';
			!$complete && $tableid--;
			if(trim($sqldump)) {
				$sqldump = "$idstring".
					"# <?exit();?>\n".
					"# Discuz! Multi-Volume Data Dump Vol.$volume\n".
					"# Version: Discuz! $version\n".
					"# Time: $time\n".
					"# Type: $type\n".
					"# Table Prefix: $tablepre\n".
					"#\n".
					"# Discuz! Home: http://www.discuz.com\n".
					"# Please visit our website for newest infomation about Discuz!\n".
					"# --------------------------------------------------------\n\n\n".
					"$setnames".
					$sqldump;
				$dumpfilename = sprintf($dumpfile, $volume);
				@$fp = fopen($dumpfilename, 'wb');
				@flock($fp, 2);
				if(@!fwrite($fp, $sqldump)) {
					@fclose($fp);
					cpmsg('数据文件无法保存到服务器，请检查目录属性。');
				} else {
					fclose($fp);
					if($usezip == 2) {
						$fp = fopen($dumpfilename, "r");
						$content = @fread($fp, filesize($dumpfilename));
						fclose($fp);
						$zip = new zipfile();
						$zip->addFile($content, basename($dumpfilename));
						$fp = fopen(sprintf($backupfilename."-%s".'.zip', $volume), 'w');
						if(@fwrite($fp, $zip->file()) !== FALSE) {
							@unlink($dumpfilename);
						}
						fclose($fp);
					}
					unset($sqldump, $zip, $content);
		cpmsgexport('分卷备份: 数据文件 #'.$volume.' 成功创建，程序将自动继续。',$phpself."?action=".$action);
				}
			} else {
				$volume--;
				if($volume<0)$volume = 0;
				if($usezip == 1) {
					$zip = new zipfile();
					$zipfilename = $backupfilename.'.zip';
					$unlinks = '';
					for($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($dumpfile, $i);
						$fp = @fopen($filename, "r");
						$content = @fread($fp, filesize($filename));
						@fclose($fp);
						$zip->addFile($content, basename($filename));
						$unlinks .= "@unlink('$filename');";
						$filelist .= "<li><a href=\"$filename\">$filename\n";
					}
					$fp = fopen($zipfilename, 'w');
					if(@fwrite($fp, $zip->file()) !== FALSE) {
						eval($unlinks);
					} else {
						cpmsg('恭喜您，全部 '.$volume.' 个备份文件成功创建，备份完成。<a href="'.$phpself.'?action='.$action.'">数据备份</a>\n<br />'.$filelist);
					}
					unset($sqldump, $zip, $content);
					fclose($fp);
					@touch('./data/index.htm');
					$filename = $zipfilename;
					cpmsg('数据成功备份并压缩至服务器 <a href="'.$filename.'">'.$filename.'</a> 中。<a href="'.$phpself.'?action='.$action.'">数据备份</a>');
				} else {
					@touch('./data/index.htm');
					for($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($usezip == 2 ? $backupfilename."-%s".'.zip' : $dumpfile, $i);
						$filelist .= "<li><a href=\"$filename\">$filename\n";
					}
					cpmsg('恭喜您，全部 '.$volume.' 个备份文件成功创建，备份完成。<a href="'.$phpself.'?action='.$action.'">数据备份</a><ul>'.$filelist.'</ul>');
				}
			}

		}

	}

	$shelldisabled = function_exists('shell_exec') ? '' : 'disabled';
	$sqlcharsets = "<input class=\"radio\" type=\"radio\" name=\"sqlcharset\" value=\"\" checked> $lang[default]".($dbcharset ? " &nbsp; <input class=\"radio\" type=\"radio\" name=\"sqlcharset\" value=\"$dbcharset\"> ".strtoupper($dbcharset) : '').($db->version() > '4.1' && $dbcharset != 'utf8' ? " &nbsp; <input class=\"radio\" type=\"radio\" name=\"sqlcharset\" value='utf8'> UTF-8</option>" : '');

	$tablelist = "";
	$pnbak_tables = fetchtablelist('',1);
	foreach($pnbak_tables as $key => $tables){
		$rowcount =0;
		$tablelist .="<tr>\n\t<td colspan=\"4\"><b>".(empty($key)?"其它":$key)."数据表</b>&nbsp;&nbsp;<input type=\"checkbox\" name=\"chkall\" onclick=\"exportcheckall(this,'".(empty($key)?"other_":$key)."')\" class=\"checkbox\" checked> <b>全选</b></td>\n</tr>\n";
		$tablelist .= "<tbody id=\"".(empty($key)?"other_":$key)."\">";
		foreach($tables as $table) {
			$tablelist .= ($rowcount % 4 ? '' : "<tr>")."\n\t<td><input class=\"checkbox\" type=\"checkbox\" name=\"customtables[]\" value=\"$table[Name]\" checked> $table[Name]</td>".($rowcount % 4!=3 ? '' : "\n</tr>\n");
				$rowcount++;
		}
		$i = $rowcount%4==0?0:(4-$rowcount%4);
		for(; $i>0;$i--){
			$tablelist .= ($rowcount % 4 ? '' : "<tr>")."\n\t<td>&nbsp;</td>".($rowcount % 4!=3 ? '' : "\n</tr>\n");
			$rowcount++;
		}
		$tablelist .= "</tbody>";
	}

	cpexport();
}
else if($action == 'importzip') {

	require_once 'zip.func.php';
	$datafile_server = $_GET['datafile_server'];
	$confirm = $_GET['confirm'];
	$multivol = $_GET['multivol'];

	$unzip = new SimpleUnzip();
	$unzip->ReadFile($datafile_server);

	if($unzip->Count() == 0 || $unzip->GetError(0) != 0 || !preg_match("/\.sql$/i", $importfile = $unzip->GetName(0))) {
		cpmsg('数据文件不存在: 可能服务器不允许上传文件或尺寸超过限制。<a href="'.$phpself.'?action='.$action.'">首页</a>');
	}

	$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", substr($unzip->GetData(0), 0, 256))));
	$confirm = !empty($confirm) ? 1 : 0;
	if(!$confirm && $identify[1] != $version) {
		cpmsg('导入和当前 Discuz! 版本不一致的数据极有可能产生无法解决的故障，您确定继续吗？', $phpself.'?action=importzip&datafile_server=$datafile_server&importsubmit=yes&confirm=yes', 'form');
	}

	$sqlfilecount = 0;
	foreach($unzip->Entries as $entry) {
		if(preg_match("/\.sql$/i", $entry->Name)) {
			$fp = fopen('./data/'.$entry->Name, 'w');
			fwrite($fp, $entry->Data);
			fclose($fp);
			$sqlfilecount++;
		}
	}

	if(!$sqlfilecount) {
		cpmsg('数据文件不存在: 可能服务器不允许上传文件或尺寸超过限制。<a href="'.$phpself.'?action='.$action.'">首页</a>');
	}

	$info = basename($datafile_server).' &nbsp; 版本: '.$identify[1].' &nbsp; 类型: '.($identify[2]=="alldata"?"全部数据":"自定义").' 方式: '.($identify[3] == 'multivol' ? "多卷" : "Shell").'<br />';

	if(isset($multivol)) {
		$multivol++;
		$datafile_server = preg_replace("/-(\d+)(\..+)$/", "-$multivol\\2", $datafile_server);
		if(file_exists($datafile_server)) {
			cpmsg('数据文件 #'.$multivol.' 成功解压缩，程序将自动继续。', $phpself.'?action=importzip&multivol='.$multivol.'&datafile_vol1='.$datafile_vol1.'&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes');
		} else {
			cpmsg('所有分卷文件解压缩完毕，您需要自动导入备份吗？导入后解压缩的文件将会被删除。', $phpself.'?action=import&from=server&datafile_server='.$datafile_vol1.'&importsubmit=yes&delunzip=yes', 'form', '', $phpself.'?action=import');
		}
	}

	if($identify[3] == 'multivol' && $identify[4] == 1 && preg_match("/-1(\..+)$/", $datafile_server)) {
		$datafile_vol1 = $datafile_server;
		$datafile_server = preg_replace("/-1(\..+)$/", "-2\\1", $datafile_server);
		if(file_exists($datafile_server)) {
			cpmsg($info.'<br />备份文件解压缩完毕，您需要自动解压缩其它的分卷文件吗？', $phpself.'?action=importzip&multivol=1&datafile_vol1=./data/'.$importfile.'&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes', 'form');
		}
	}

	cpmsg($info.'<br />备份文件解压缩完毕，您需要自动导入备份吗？导入后解压缩的文件将会被删除。', $phpself.'?action=import&from=server&datafile_server=./data/'.$importfile.'&importsubmit=yes&delunzip=yes', 'form', '', $phpself.'?action=import');

}
else if($action=="import") {
	if( empty($_GET['importsubmit']) && empty($_POST['deletesubmit']) ) {
		$exportlog = array();
		if(is_dir('./data/')) {
			$dir = dir('./data/');
			while($entry = $dir->read()) {
				$entry = './data/'.$entry;
				if(is_file($entry)) {
					if(preg_match("/\.sql$/i", $entry)) {
						$filesize = filesize($entry);
						$fp = fopen($entry, 'rb');
						$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
						fclose ($fp);
						$exportlog[] = array(
							'version' => $identify[1],
							'type' => $identify[2],
							'method' => $identify[3],
							'volume' => $identify[4],
							'filename' => $entry,
							'dateline' => filemtime($entry),
							'size' => $filesize
						);
					} elseif(preg_match("/\.zip$/i", $entry)) {
						$filesize = filesize($entry);
						$exportlog[] = array(
							'type' => 'zip',
							'filename' => $entry,
							'size' => filesize($entry),
							'dateline' => filemtime($entry)
						);
					}
				}
			}
			$dir->close();
		}
		else{
			cpmsg('目录不存在或无法访问，请检查创建 ./data/ 目录。');
		}
		$exportinfo = '';
		foreach($exportlog as $info) {
			$info['dateline'] = is_int($info['dateline']) ? date("Y-m-d H:i:s", $info['dateline'] + $timeoffset * 3600) : "未知";
			$info['size'] = sizecount($info['size']);
			$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
			$info['method'] = $info['type'] != 'zip' ? ($info['method'] == 'multivol' ? '多卷' : 'Shell') : '';
			$exportinfo .= "<tr align=\"center\"><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"".basename($info['filename'])."\"></td>\n".
				"<td class=\"altbg2\"><a href=\"$info[filename]\">".substr(strrchr($info['filename'], "/"), 1)."</a></td>\n".
				"<td class=\"altbg1\">$info[version]</td>\n".
				"<td class=\"altbg2\">$info[dateline]</td>\n".
				"<td class=\"altbg1\">".($info['type']=="alldata"?"全部数据":($info['type']=="zip"?"压缩备份":"自定义"))."</td>\n".
				"<td class=\"altbg2\">$info[size]</td>\n".
				"<td class=\"altbg1\">$info[method]</td>\n".
				"<td class=\"altbg2\">$info[volume]</td>\n".
				($info['type'] == 'zip' ? "<td class=\"altbg1\"><a href=\"".$phpself."?action=importzip&datafile_server=$info[filename]&importsubmit=yes\">[解压缩]</a></td>\n" :
				"<td class=\"altbg1\"><a href=\"".$phpself."?action=import&from=server&datafile_server=$info[filename]&importsubmit=yes\"".
				($info['version'] != $version ? " onclick=\"return confirm('导入和当前 Discuz! 版本不一致的数据极有可能产生无法解决的故障，您确定继续吗？');\"" : '').">[导入]</a></td>\n");
			$exportinfo .= "</tr>";
		}
		cpimport();
	}else if( !empty($_GET['importsubmit']) ){
		$from = $_GET['from'];
		$from = empty($from)?$_POST['from']:$from;
		$autoimport = $_GET['autoimport'];
		$datafile_server = $_GET['datafile_server'];
		$datafile_server = empty($datafile_server)?$_POST['datafile_server']:$datafile_server;
		$delunzip = $_GET['delunzip'];

		$readerror = 0;
		$datafile = '';
		if($from == 'server') {
			$datafile = ''.$datafile_server;
		}
		/*else if($from == 'local') {
			$datafile = $_FILES['datafile']['tmp_name'];
		}*/
		if(@$fp = fopen($datafile, 'rb')) {
			$sqldump = fgets($fp, 256);
			$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", $sqldump)));
			$dumpinfo = array('method' => $identify[3], 'volume' => intval($identify[4]));
			if($dumpinfo['method'] == 'multivol') {
				$sqldump .= fread($fp, filesize($datafile));
			}
			fclose($fp);
		} else {
			if($autoimport) {
				cpmsg('分卷数据成功导入论坛数据库。<a href="'.$phpself.'?action='.$action.'">首页</a>');
			} else {
				cpmsg('数据文件不存在: 可能服务器不允许上传文件或尺寸超过限制。');
			}
		}

		if($dumpinfo['method'] == 'multivol') {
			$sqlquery = splitsql($sqldump);
			unset($sqldump);

			foreach($sqlquery as $sql) {

				$sql = syntablestruct(trim($sql), $db->version() > '4.1', $dbcharset);

				if($sql != '') {
					$db->query($sql, 'SILENT');
					if(($sqlerror = $db->error()) && $db->errno() != 1062) {
						$db->halt('MySQL Query Error', $sql);
					}
				}
			}

			if($delunzip) {
				@unlink($datafile_server);
			}

			$datafile_next = preg_replace("/-($dumpinfo[volume])(\..+)$/", "-".($dumpinfo['volume'] + 1)."\\2", $datafile_server);

			if($dumpinfo['volume'] == 1) {
				cpmsg('分卷数据成功导入数据库，您需要自动导入本次其它的备份吗？',
					$phpself."?action=import&from=server&datafile_server=$datafile_next&autoimport=yes&importsubmit=yes".(!empty($delunzip) ? '&delunzip=yes' : ''),
					'form');
			} elseif($autoimport) {
				cpmsg('数据文件 #'.$dumpinfo[volume].' 成功导入，程序将自动继续。', $phpself."?action=import&from=server&datafile_server=$datafile_next&autoimport=yes&importsubmit=yes".(!empty($delunzip) ? '&delunzip=yes' : ''));
			} else {
				cpmsg('数据成功导入论坛数据库。<a href="'.$phpself.'?action='.$action.'">首页</a>');
			}
		} elseif($dumpinfo['method'] == 'shell') {
			require './config.inc.php';
			list($dbhost, $dbport) = explode(':', $dbhost);

			$query = $db->query("SHOW VARIABLES LIKE 'basedir'");
			list(, $mysql_base) = $db->fetch_array($query, MYSQL_NUM);

			$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
			shell_exec($mysqlbin.'mysql -h"'.$dbhost.($dbport ? (is_numeric($dbport) ? ' -P'.$dbport : ' -S"'.$dbport.'"') : '').
				'" -u"'.$dbuser.'" -p"'.$dbpw.'" "'.$dbname.'" < '.$datafile);

			cpmsg('数据成功导入论坛数据库。<a href="'.$phpself.'?action='.$action.'">首页</a>');
		} else {
			cpmsg('数据文件非 Discuz! 格式，无法导入。请返回');
		}


	}else if( !empty($_POST['deletesubmit']) ) {
		$delete = $_POST['delete'];
		if(is_array($delete)) {
			foreach($delete as $filename) {
				@unlink('./data/'.str_replace(array('/', '\\'), '', $filename));
			}
			cpmsg('指定备份文件成功删除。<a href="'.$phpself.'?action='.$action.'">首页</a>');
		} else {
			cpmsg('您没有选择要删除的备份文件，请返回。');
		}
	}

}


function sqldumptable($table, $startfrom = 0, $currsize = 0) {
	global $db, $sizelimit, $startrow, $extendins, $sqlcompat, $sqlcharset, $dumpcharset, $usehex, $complete, $excepttables;

	$offset = 300;
	$tabledump = '';
	$tablefields = array();

	$query = $db->query("SHOW FULL COLUMNS FROM $table", 'SILENT');
	if(strexists($table, 'adminsessions')) {
		return ;
	} elseif(!$query && $db->errno() == 1146) {
		return;
	} elseif(!$query) {
		$usehex = FALSE;
	} else {
		while($fieldrow = $db->fetch_array($query)) {
			$tablefields[] = $fieldrow;
		}
	}
	if(!$startfrom) {

		$createtable = $db->query("SHOW CREATE TABLE $table", 'SILENT');

		if(!$db->error()) {
			$tabledump = "DROP TABLE IF EXISTS $table;\n";
		} else {
			return '';
		}

		$create = $db->fetch_row($createtable);

		if(strpos($table, '.') !== FALSE) {
			$tablename = substr($table, strpos($table, '.') + 1);
			$create[1] = str_replace("CREATE TABLE $tablename", 'CREATE TABLE '.$table, $create[1]);
		}
		$tabledump .= $create[1];

		if($sqlcompat == 'MYSQL41' && $db->version() < '4.1') {
			$tabledump = preg_replace("/TYPE\=(.+)/", "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
		}
		if($db->version() > '4.1' && $sqlcharset) {
			$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=".$sqlcharset, $tabledump);
		}

		$query = $db->query("SHOW TABLE STATUS LIKE '$table'");
		$tablestatus = $db->fetch_array($query);
		$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : '').";\n\n";
		if($sqlcompat == 'MYSQL40' && $db->version() >= '4.1' && $db->version() < '5.1') {
			if($tablestatus['Auto_increment'] <> '') {
				$temppos = strpos($tabledump, ',');
				$tabledump = substr($tabledump, 0, $temppos).' auto_increment'.substr($tabledump, $temppos);
			}
			if($tablestatus['Engine'] == 'MEMORY') {
				$tabledump = str_replace('TYPE=MEMORY', 'TYPE=HEAP', $tabledump);
			}
		}
	}

	if(!in_array($table, $excepttables)) {
		$tabledumped = 0;
		$numrows = $offset;
		$firstfield = $tablefields[0];

		if($extendins == '0') {
			while($currsize + strlen($tabledump) + 500 < $sizelimit * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = $db->query($selectsql);
				$numfields = $db->num_fields($rows);

				$numrows = $db->num_rows($rows);
				while($row = $db->fetch_row($rows)) {
					$comma = $t = '';
					for($i = 0; $i < $numfields; $i++) {
						$t .= $comma.($usehex && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
						$comma = ',';
					}
					if(strlen($t) + $currsize + strlen($tabledump) + 500 < $sizelimit * 1000) {
						if($firstfield['Extra'] == 'auto_increment') {
							$startfrom = $row[0];
						} else {
							$startfrom++;
						}
						$tabledump .= "INSERT INTO $table VALUES ($t);\n";
					} else {
						$complete = FALSE;
						break 2;
					}
				}
			}
		} else {
			while($currsize + strlen($tabledump) + 500 < $sizelimit * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = $db->query($selectsql);
				$numfields = $db->num_fields($rows);

				if($numrows = $db->num_rows($rows)) {
					$t1 = $comma1 = '';
					while($row = $db->fetch_row($rows)) {
						$t2 = $comma2 = '';
						for($i = 0; $i < $numfields; $i++) {
							$t2 .= $comma2.($usehex && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text'))? '0x'.bin2hex($row[$i]) : '\''.mysql_escape_string($row[$i]).'\'');
							$comma2 = ',';
						}
						if(strlen($t1) + $currsize + strlen($tabledump) + 500 < $sizelimit * 1000) {
							if($firstfield['Extra'] == 'auto_increment') {
								$startfrom = $row[0];
							} else {
								$startfrom++;
							}
							$t1 .= "$comma1 ($t2)";
							$comma1 = ',';
						} else {
							$tabledump .= "INSERT INTO $table VALUES $t1;\n";
							$complete = FALSE;
							break 2;
						}
					}
					$tabledump .= "INSERT INTO $table VALUES $t1;\n";
				}
			}
		}

		$startrow = $startfrom;
		$tabledump .= "\n";
	}

	return $tabledump;
}

function strexists($haystack, $needle) {
	return !(strpos($haystack, $needle) === FALSE);
}

function fetchtablelist($tablepre = '',$iscate = 0) {
	global $db;
	$arr = explode('.', $tablepre);
	$dbname = $arr[1] ? $arr[0] : '';
	$sqladd = $dbname ? " FROM $dbname LIKE '$arr[1]%'" : "LIKE '$tablepre%'";
	!$tablepre && $tablepre = '*';
	$tables = $table = $othertables = array();
	$query = $db->query("SHOW TABLE STATUS $sqladd");
	while($table = $db->fetch_array($query)) {
		if($iscate==1){
			$pntablearr = explode("_",$table['Name']);
			if( count($pntablearr)>1 ){
				if( empty($tables[$pntablearr[0]]) ){
					$tables[$pntablearr[0]] = array();
				}
				$tables[$pntablearr[0]][] = $table;
			}else{
				$table['Name'] = ($dbname ? "$dbname." : '').$table['Name'];
				$othertables[] = $table;
			}
		}else{
			$table['Name'] = ($dbname ? "$dbname." : '').$table['Name'];
			$tables[] = $table;
		}
	}
	if( !empty($othertables) && $iscate){
		$tables[] = $othertables;
	}
	return $tables;
}

function arraykeys2($array, $key2) {
	$return = array();
	foreach($array as $val) {
		$return[] = $val[$key2];
	}
	return $return;
}

function splitsql($sql) {
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == "#" ? NULL : $query;
		}
		$num++;
	}
	return($ret);
}

function syntablestruct($sql, $version, $dbcharset) {

	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

function sizecount($filesize) {
	if($filesize >= 1073741824) {
		$filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
	} elseif($filesize >= 1048576) {
		$filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
	} elseif($filesize >= 1024) {
		$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
	} else {
		$filesize = $filesize . ' Bytes';
	}
	return $filesize;
}

function writeconfig($dbhost,$dbuser,$dbpw,$dbname,$database = 'mysql',$dbcharset = '',$charset = 'gbk'){
	$fp = fopen('./config.inc.php', 'r');
	$configfile = fread($fp, filesize('./config.inc.php'));
	fclose($fp);
	$configfile = preg_replace("/[$]dbhost\s*\=\s*[\"'].*?[\"'];/is", "\$dbhost = '$dbhost';", $configfile);
	$configfile = preg_replace("/[$]dbuser\s*\=\s*[\"'].*?[\"'];/is", "\$dbuser = '$dbuser';", $configfile);
	$configfile = preg_replace("/[$]dbpw\s*\=\s*[\"'].*?[\"'];/is", "\$dbpw = '$dbpw';", $configfile);
	$configfile = preg_replace("/[$]dbname\s*\=\s*[\"'].*?[\"'];/is", "\$dbname = '$dbname';", $configfile);
	$configfile = preg_replace("/[$]database\s*\=\s*[\"'].*?[\"'];/is", "\$database = '$database';", $configfile);
	$configfile = preg_replace("/[$]dbcharset\s*\=\s*[\"'].*?[\"'];/is", "\$dbcharset = '$dbcharset';", $configfile);
	$configfile = preg_replace("/[$]charset\s*\=\s*[\"'].*?[\"'];/is", "\$charset = '$charset';", $configfile);
	$fp = fopen('./config.inc.php', 'w');
	fwrite($fp, trim($configfile));
	fclose($fp);
}

function setconfig($string) {
	if(!get_magic_quotes_gpc()) {
		$string = str_replace('\'', '\\\'', $string);
	} else {
		$string = str_replace('\"', '"', $string);
	}
	return $string;
}

function cpconfig(){
	extract($GLOBALS, EXTR_SKIP);
	cpheader();
?>
<br />
<table width="600" border="0" align="center" cellpadding="2" cellspacing="0" class="logintable">
	<tr class="loginheader">
		<td width="80"></td>
		<td width="100"></td>
		<td width="164"></td>
		<td width="154"></td>
		<td width="80"></td>
	</tr>
	<tr style="height:40px">
		<td></td>
		<td class="line1"><span style="color:#ffff66;font-size:14px;font-weight: bold;">连接设置</span></td>
		<td class="line1">&nbsp;</td>
		<td class="line1">&nbsp;</td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td class="line2">&nbsp;</td>
		<td class="line2">&nbsp;</td>
		<td class="line2">&nbsp;</td>
		<td></td>
	</tr>
<form method="post" name="config" action="<?=$phpself?>?action=config">
	<tr>
		<td></td>
		<td style="text-align:right;">数据库服务器:</td>
		<td><input name="dbhost" type="text" id="dbhost" size="25" value="<?=$dbhost?>" /></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td style="text-align:right;">数据库用户名:</td>
		<td><input name="dbuser" type="text" id="dbuser" size="25" value="<?=$dbuser?>"></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td style="text-align:right;">数据库密码:</td>
		<td><input name="dbpw" type="text" id="dbpw" size="25" value="<?=$dbpw?>"></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td style="text-align:right;">数据库名:</td>
		<td><input name="dbname" type="text" id="dbname" size="25" value="<?=$dbname?>"></td>
		<td>
			<select name="dbselect" id="dbselect">
				<option value="">请选择</option>
			</select>
			&nbsp;
			<input type="button" value="搜" style="width:25px;background-color:#bedeff;border:1px solid #296c89;" onclick="javascript:showdatabases('dbselect');">
		</td>
		<td></td>
	</tr>
	<tr>
		<td></td>
		<td class="line1">&nbsp;</td>
		<td class="line1" align="center">
			<input type="submit" name="valuesubmit" class="button" value="保 存" />
		</td>
		<td class="line1">&nbsp;</td>
		<td></td>
	</tr>
</form>
	<tr>
		<td>&nbsp;</td>
		<td class="line2">&nbsp;</td>
		<td class="line2">&nbsp;</td>
		<td class="line2">&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="5" style="text-align:center;">
			Powered by <a href="http://www.discuz.net" target="_blank" style="color: #fff"><b>Discuz!</b></a>&nbsp;&copy; 2001-2007 Changed by pnkoo.cn</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>
<?php
	exit;
}

function cpimport(){
	extract($GLOBALS, EXTR_SKIP);
	cpheader();
?>
<table width="100%" border="0" cellpadding="2" cellspacing="6">
	<tr>
	  <td>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
	<tr class="header">
		<td>
			<div style="float:left; margin-left:0px; padding-top:8px">技巧提示</div>
			<div style="float:right; margin-right:4px; padding-bottom:9px"></div>
		</td>
	</tr>
	<tr>
		<td>
			<ul>
				<li>本功能在恢复备份数据的同时，将全部覆盖原有数据，请确定恢复前已将网站关闭，恢复全部完成后可以将网站重新开放。</li>
				<li>数据恢复功能只能恢复由当前版本 Discuz! 导出的数据文件，其它软件导出格式可能无法识别。</li>
				<li>从本地恢复数据需要服务器支持文件上传并保证数据尺寸小于允许上传的上限，否则只能使用从服务器恢复。</li>
				<li>如果您使用了分卷备份，只需手工导入文件卷 1，其它数据文件会由系统自动导入。</li>
			</ul>
		</td>
	</tr>
</table>
<br />
<form action="<?=$phpself?>?action=import&importsubmit=yes" name="datafilefrom" method="post">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
	<tr class="header">
		<td colspan="2">数据恢复</td>
	</tr>
	<tr>
		<td class="altbg1" width="40%"><input class="radio" type="radio" name="from" value="server" checked onclick="this.form.datafile_server.disabled=!this.checked;this.form.datafile.disabled=this.checked">从服务器(填写文件名或 URL):</td>
		<td class="altbg2" width="45%"><input type="text" size="40" name="datafile_server" value="./data/"></td>
	</tr>
	<tr>
		<td class="altbg1" width="40%"><input class="radio" type="radio" name="from" value="local" onclick="this.form.datafile_server.disabled=this.checked;this.form.datafile.disabled=!this.checked" disabled>从本地文件:</td>
		<td class="altbg2" width="45%"><input type="file" size="29" name="datafile" disabled></td>
	</tr>
</table>
<br />
<center><input class="button" type="submit" name="importsubmit" value="提 交"></center>
<br />
</form>

<form action="<?=$phpself?>?action=import" name="deleteform" method="post">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
	<tr class="header">
		<td colspan="9">数据备份记录</td>
	</tr>
	<tr align="center" class="category">
		<td width="48"><input class="checkbox" type="checkbox" name="chkall" onclick="importcheckall(this,'importfile')">删?</td>
		<td>文件名</td>
		<td>版本</td>
		<td>时间</td>
		<td>类型</td>
		<td>尺寸</td>
		<td>方式</td>
		<td>卷号</td>
		<td>操作</td>
	</tr>
	<tbody id="importfile">
<?=$exportinfo?>
	</tbody>
</table>
<br />
<center><input class="button" type="submit" name="deletesubmit" value="提 交"></center>
</form>

	  </td>
	</tr>
</table>
<?php
	cpfooter();
	exit;
}

function cpexport(){
	extract($GLOBALS, EXTR_SKIP);
	cpheader();
?>
<form method="post" name="export" id="export" action="<?=$phpself?>?action=export">
<input type="hidden" name="setup" value="1">
<table width="100%" border="0" cellpadding="2" cellspacing="6">
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
				<tr class="header">
					<td><div style="float:left; margin-left:0px; padding-top:8px">技巧提示</div><div style="float:right; margin-right:4px; padding-bottom:9px"></div>
					</td>
				</tr>
				<tbody id="menu_tip">
				<tr>
					<td>
						<ul>
							<li>数据备份功能根据您的选择备份数据，导出的数据文件可用“数据恢复”功能或 phpMyAdmin 导入。</li>
						</ul>
						<ul>
							<li>全部备份均不包含模板文件和附件文件。模板、附件和用户自定义头像的备份只需通过 FTP 下载相应文件即可。</li>
						</ul>
						<ul>
							<li>MySQL Dump 的速度比 Discuz! 分卷备份快很多，但需要服务器支持相关的 Shell 权限，同时由于 MySQL 本身的兼容性问题，通常进行备份和恢复的服务器应当具有相同或相近的版本号才能顺利进行。因此 MySQL Dump 是有风险的：一旦进行备份或恢复操作的服务器其中之一禁止了 Shell，或由于版本兼容性问题导致导入失败，您将无法使用 MySQL Dump 备份或由备份数据恢复；Discuz! 分卷备份没有此限制。</li></ul>
						<ul>
							<li>数据备份选项中的设置，仅供高级用户的特殊用途使用，当您尚未对数据库做全面细致的了解之前，请使用默认参数备份，否则将导致备份数据错误等严重问题。</li>
							<li>十六进制方式可以保证备份数据的完整性，但是备份文件会占用更多的空间。</li>
						</ul>
						<ul>
							<li>压缩备份文件可以让您的备份文件占用更小的空间。</li>
						</ul>
					</td>
				</tr>
				</tbody>
			</table><br />
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
				<tr class="header">
					<td colspan="2">数据备份类型</td>
				</tr>
				<tr>
				  <td class="altbg1" width="47%"><input class="radio" type="radio" value="alldata" name="type" onclick="javascript:showtables(0);" checked>全部数据</td>
					<td width="53%" class="altbg2"></td>
				</tr>
				<tr>
				  <td class="altbg1"><input class="radio" type="radio" value="custom" name="type" onclick="javascript:showtables(1);">自定义备份</td>
					<td class="altbg2">根据需要自行选择需要备份的数据表</td>
				</tr>
				<tbody id="showtables" style="display:none;">
				<tr>
					<td colspan="2">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<?=$tablelist?>
</table>
					</td>
				</tr>
				</tbody>
				<tr>
				  <td class="altbg1"></td>
					<td class="altbg2" style="text-align: right;"><input class="checkbox" type="checkbox" value="1" onclick="javascript:advanceoption(this);">更多选项 &nbsp;</td>
				</tr>
<tbody id="advanceoption" style="display:none;">
	<tr class="header">
		<td colspan="2">数据备份方式</td>
	</tr>
	<tr>
		<td class="altbg1">
			<input class="radio" type="radio" name="method" value="shell" <?=$shelldisabled?> onclick="if(<?=intval($db->version() < '4.1')?>) {if(this.form.sqlcompat[2].checked==true) this.form.sqlcompat[0].checked=true; this.form.sqlcompat[2].disabled=true; this.form.sizelimit.disabled=true;} else {this.form.sqlcharset[0].checked=true; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=true;}}"> 系统 MySQL Dump (Shell) 备份
		</td>
		<td class="altbg2">&nbsp;</td>
	</tr>
	<tr>
		<td class="altbg1">
			<input class="radio" type="radio" name="method" value="multivol" checked onclick="this.form.sqlcompat[2].disabled=false; this.form.sizelimit.disabled=false; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=false;}"> Discuz! 分卷备份 - 文件长度限制(kb)
		</td>
		<td class="altbg2"><input type="text" size="40" name="sizelimit" value="2048"></td>
	</tr>
	<tr class="header">
		<td colspan="2">数据备份选项</td>
	</tr>
	<tr>
		<td class="altbg1">&nbsp;使用扩展插入(Extended Insert)方式</td>
		<td class="altbg2">
			<input class="radio" type="radio" name="extendins" value="1"> 是 &nbsp; <input class="radio" type="radio" name="extendins" value="0" checked> 否
		</td>
	</tr>
	<tr>
		<td class="altbg1">&nbsp;建表语句格式</td>
		<td class="altbg2">
			<input class="radio" type="radio" name="sqlcompat" value="" checked> 默认 &nbsp; <input class="radio" type="radio" name="sqlcompat" value="MYSQL40"> MySQL 3.23/4.0.x &nbsp; <input class="radio" type="radio" name="sqlcompat" value="MYSQL41"> MySQL 4.1.x/5.x &nbsp;
		</td>
	</tr>
	<tr>
		<td class="altbg1">&nbsp;强制字符集</td>
		<td class="altbg2"><?=$sqlcharsets?></td>
	</tr>
	<tr>
		<td class="altbg1">&nbsp;十六进制方式</td>
		<td class="altbg2"><input class="radio" type="radio" name="usehex" value="1" checked> 是 &nbsp; <input class="radio" type="radio" name="usehex" value="0"> 否</td>
	</tr>
<?
	if(function_exists('gzcompress')) {
?>
	<tr>
		<td class="altbg1">&nbsp;压缩备份文件</td>
		<td class="altbg2"><input class="radio" type="radio" name="usezip" value="1"> 多分卷压缩成一个文件 &nbsp; <input class="radio" type="radio" name="usezip" value="2"> 每个分卷压缩成单独文件 &nbsp; <input class="radio" type="radio" name="usezip" value="0" checked> 不压缩</td>
	</tr>
<?
	}
?>
	<tr>
		<td class="altbg1">&nbsp;备份文件名</td>
		<td class="altbg2"><input type="text" size="40" name="filename" value="<?=date('ymd').'_'.random(8)?>"> .sql</td>
	</tr>
</tbody>
			</table>
		</td>
	</tr>
</table>
<center><input class="button" type="submit" name="exportsubmit" value="提 交"></center>
</form>
<?php
	cpfooter();
	exit;
}

function cpmsgexport($message, $url_forward = '') {
	extract($GLOBALS, EXTR_SKIP);
	cpheader(0);
	$message = "<form method=\"post\" name=\"exportpara\" id=\"exportpara\" action=\"$url_forward\">".
			"<br /><br /><br />$message<br /><br /><br />\n";
	$message .= "<a href=\"javascript:exportsubmit('exportpara');\">如果您的浏览器没有自动跳转，请点击这里</a>";
	$message .= "<script>setTimeout(\"exportsubmit('exportpara');\", 2000);</script>";
	$message .= "<input type='hidden' name='type' value='".$type."' />";
	$message .= "<input type='hidden' name='saveto' value='server' />";
	$message .= "<input type='hidden' name='filename' value='".$filename."' />";
	$message .= "<input type='hidden' name='method' value='multivol' />";
	$message .= "<input type='hidden' name='sizelimit' value='".$sizelimit."' />";
	$message .= "<input type='hidden' name='volume' value='".$volume."' />";
	$message .= "<input type='hidden' name='tableid' value='".$tableid."' />";
	$message .= "<input type='hidden' name='startfrom' value='".$startrow."' />";
	$message .= "<input type='hidden' name='extendins' value='".$extendins."' />";
	$message .= "<input type='hidden' name='sqlcharset' value='".$sqlcharset."' />";
	$message .= "<input type='hidden' name='sqlcompat' value='".$sqlcompat."' />";
	$message .= "<input type='hidden' name='exportsubmit' value='yes' />";
	$message .= "<input type='hidden' name='usehex' value='".$usehex."' />";
	$message .= "<input type='hidden' name='usezip' value='".$usezip."' />";
	$message .= "<input type='hidden' name='customtables' value='".$customtablesnew."' />";
	$message .= "</form><br />";
?>
<br /><br /><br /><br /><br /><br />
<table width="500" border="0" cellpadding="0" cellspacing="0" align="center" class="tableborder">
<tr class="header"><td>Discuz! 提示</td></tr><tr><td class="altbg2"><div align="center">
<?=$message?></div><br /><br />
</td></tr></table>
<br /><br /><br />
<?
	cpfooter();
	exit;
}

function cpmsg($message, $url_forward = '', $msgtype = 'message', $extra = '', $cancelurl = '') {
	cpheader(0);
	if($msgtype == 'form') {
		$message = "<form method=\"post\" action=\"$url_forward\">".
			"<br />$message$extra<br /><br /><br /><br />\n".
			"<input class=\"button\" type=\"submit\" name=\"confirmed\" value=\" 确 定 \"> &nbsp; \n".
			"<input class=\"button\" type=\"button\" value=\" 取 消 \" onClick=\"".
			($cancelurl == '' ? 'history.go(-1)' : 'location.href=\''.$cancelurl.'\'').
			";\"></form><br />";
	} else {
		if($url_forward) {
			$message .= "<br /><br /><a href=\"$url_forward\">如果您的浏览器没有自动跳转，请点击这里</a>";
			$message .= "<script>setTimeout(\"redirect('$url_forward');\", 2000);</script>";
		} elseif(strpos($message, "返回")) {
			$message .= "<br /><br /><a href=\"javascript:history.go(-1);\" class=\"mediumtxt\">[ 点击这里返回上一页 ]</a>";
		}
		$message = "<br />$message$extra<br />";
	}
?>
<br /><br /><br /><br /><br /><br />
<table width="500" border="0" cellpadding="0" cellspacing="0" align="center" class="tableborder">
<tr class="header"><td>Discuz! 提示</td></tr><tr><td class="altbg2"><div align="center">
<?=$message?></div><br /><br />
</td></tr></table>
<br /><br /><br />
<?
	cpfooter();
	exit;
}

function cpheader($showmenu = 1){
	extract($GLOBALS, EXTR_SKIP);
	if($db){
		$query = $db->query("select database()");
		$title = "PNBAKMYSQL备份/恢复 - ".$db->result($query, 0);
	}else{
		$title = "PNBAKMYSQL备份/恢复";
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gbk">
<title><?=$title?></title>
<link href="pnbak.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="pnbak.js"></script>
</head>
<body leftmargin="10" topmargin="10">
<?php
	if($showmenu){
?>
<table width="100%" border="0" cellpadding="2" cellspacing="6">
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="guide">
				<tr>
					<td>
						<a href="<?=$phpself?>?action=config">连接设置</a> &nbsp; | &nbsp; <a href="<?=$phpself?>?action=export">数据备份</a> &nbsp; | &nbsp; <a href="<?=$phpself?>?action=import">数据恢复</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php
	}
}

function cpfooter(){
?>
<br /><br />
<div class="footer">
	<hr size="0" noshade color="#9DB3C5" width="80%">Powered by <a href="http://www.discuz.net" target="_blank" style="color: #666"><b>Discuz!</b> 6.0.0</a> &nbsp;&copy; 2001-2007 Changed by pnkoo.cn
</div>
</body>
</html>
<?php
}
?>