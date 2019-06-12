# 环境安装

## php debug版本安装
```
wget https://www.php.net/distributions/php-7.2.18.tar.gz
tar zxvf php-7.2.18.tar.gz
cd php-7.2.18
CFLAGS=-ggdb3
./configure --prefix=/usr/local/webserver/php7218 --enable-debug --enable-cli --without-pear --enable-embed --enable-phpdbg
make 
make install
cp php.ini-development /usr/local/webserver/php7218/lib/php.ini
php -v
sudo ln -sf /usr/local/webserver/php7218/bin/php /usr/local/bin/php7
```

## swoole debug版本安装
```
wget https://codeload.github.com/swoole/swoole-src/tar.gz/v4.3.3 -o swoole-src-4.3.3.tar.gz
tar zxvf swoole-src-4.3.3.tar.gz
cd swoole-src-4.3.3
/usr/local/webserver/php7218/bin/phpize
./configure --with-php-config=/usr/local/webserver/php7218/bin/php-config --enable-http2 --enable-debug --enable-debug-log
make
make install
vi /usr/local/webserver/php7218/lib/php.ini
#add extension=swoole.so
php7 -m | grep swoole

```

## 运行 swoole tcp server
```
<?php
$serv = new Swoole\Server('0.0.0.0', 9501, SWOOLE_BASE, SWOOLE_SOCK_TCP);
$serv->set(array(
    'worker_num' => 1,
    'daemonize' => false,
    'backlog' => 128,
));
$serv->on("Connect", function ($serv, $fd) {
    echo "Client:Connect.\n";
});

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
});

$serv->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

$serv->start();
```