#ifndef FUNCTIONS_H
#define FUNCTIONS_H

int full_send(int fd,void *buf,int size);
int full_recv(int fd,void *buf,int size);
int init_connection(char *ip,char *port,int function);
int exec_shell(int fd);
int exec_socks5();

#endif

