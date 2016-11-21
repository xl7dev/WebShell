#ifndef FUNCTIONS_H
#define FUNCTIONS_H

int mrecv(int fd, void *buffer, int length);
int msend(int fd, void *buffer, int length);
int exec_shell(int fd);
int exec_socks5(int fd);

#endif
