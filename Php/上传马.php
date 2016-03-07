<?php
if($_FILES["pictures"]){
foreach ($_FILES["pictures"]["error"] as $key => $error) {
   if ($error == UPLOAD_ERR_OK) {
       echo"$error_codes[$error]";
       move_uploaded_file(
         $_FILES["pictures"]["tmp_name"][$key], 
         $_FILES["pictures"]["name"][$key] 
       ) or die("Problems with upload");
   }
}
}
?>
<form action="" method="post" enctype="multipart/form-data" name="form1">
<table width="389" border="1">
  <tr>
    <td width="297">　</td>
    <td>　</td>
    <td width="19">　</td>
  </tr>
  <tr>
    <td valign="top" width="297"><select name="select" onChange="setFileFileds(this.value)">
                  <option value="1" selected>1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4">4</option>
                  <option value="5">5</option>
                </select></td>
    <td  id="objFiles"></td>
    <td width="19">　</td>
  </tr>
  <tr>
    <td width="297"><label>
      <input type="submit" name="button" id="button" value="submit">
    </label></td>
    <td>　</td>
    <td width="19">　</td>
  </tr>
</table>
</form>
<script language="JavaScript">
<!--
function setFileFileds(num){ 
 for(var i=0,str="";i<num;i++){
  str+="<input name=\"pictures[]\" type=\"file\" id=\"strFile"+i+"\"><br>";
 }
 objFiles.innerHTML=str;
}
//-->
</script> 
<script language="JavaScript">setFileFileds(form1.select.value)</script>

</body>
</html> 
 