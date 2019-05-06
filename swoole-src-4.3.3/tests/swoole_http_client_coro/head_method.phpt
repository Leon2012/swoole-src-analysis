--TEST--
swoole_http_client_coro: http client with HEAD method
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php
require __DIR__ . '/../include/bootstrap.php';

Swoole\Coroutine::create(function ()
{
    $cli = new \Swoole\Coroutine\Http\Client('www.baidu.com', 80);
    $cli->set(['timeout' => 10]);
    $cli->setMethod('HEAD');
    $cli->get('/');
    Assert::eq($cli->statusCode, 200);
    assert(count($cli->headers) > 0);
});
?>
--EXPECT--
