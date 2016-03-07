Basic and Stealthy PHP webshell
===============================

**THIS SCRIPT IS ONLY FOR ADMINISTRATION OF SERVERS YOU OWN! Author is not responsible for wrong usage of this script.**

BNS webshell has two parts. One part (server.php) has to be uploaded to remote server you wish to control. Second part (client.php) is your local client. You can run it on your own PC or upload it to another remote server.

![BNS client screenshot](https://i.imgur.com/mjorwMZ.png)

**Usage:**

First of all upload server.php to remote server. After that open client.php and insert full path of server.php to "Shell URL" field. Press "Check" button to see if shell is active.

**Key advantages of BNS shell:**

- Small size. You can insert only one string of php code to any script and get full controll of server.

- Stealthy. All commands are sent via COOKIES. Target server logs will just show GET requests to server.php.

- Has OS shell, PHP shell and basic file manager.

- Client-server architecture. You can run shell client on other remote server to completely hide your home IP.