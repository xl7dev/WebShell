//Based on http://www.ietf.org/rfc/rfc1928.txt
//HTTP:http://www.ietf.org/rfc/rfc2616.txt
#ifndef SOCKS5_H
#define SOCKS5_H

/****
  +----+----------+----------+
  |VER | NMETHODS | METHODS  |
  +----+----------+----------+
  | 1  |    1     | 1 to 255 |
  +----+----------+----------+
****/
typedef struct
{
    char ver;
    char nmethods;
    char methods[255];
}SELECT,*pSELECT;

/****
  +----+--------+
  |VER | METHOD |
  +----+--------+
  | 1  |   1    |
  +----+--------+
****/
typedef struct
{
    char ver;
    char method;
}SELECT_RESPONSE,*pSELECT_RESPONSE;

/****
  +----+-----+-------+------+----------+----------+
  |VER | CMD |  RSV  | ATYP | DST.ADDR | DST.PORT |
  +----+-----+-------+------+----------+----------+
  | 1  |  1  | X'00' |  1   | Variable |    2     |
  +----+-----+-------+------+----------+----------+
****/
typedef struct
{
    char ver;
    char cmd;
    char rsv;
    char atyp;
    char addr;
    //Other sections
}REQUEST,*pREQUEST;

/****
  +----+-----+-------+------+----------+----------+
  |VER | REP |  RSV  | ATYP | BND.ADDR | BND.PORT |
  +----+-----+-------+------+----------+----------+
  | 1  |  1  | X'00' |  1   | Variable |    2     |
  +----+-----+-------+------+----------+----------+
****/
typedef struct
{
    char ver;
    char rep;
    char rsv;
    char atyp;
    char bndAddr[4];
    char bndPort[2];
}REQUEST_RESPONSE,*pREQUEST_RESPONSE;

#endif

