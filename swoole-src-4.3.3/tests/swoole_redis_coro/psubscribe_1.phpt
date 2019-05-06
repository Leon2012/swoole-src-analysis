--TEST--
swoole_redis_coro: redis psubscribe
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php
require __DIR__ . '/../include/bootstrap.php';

go(function () {
    $redis = new Co\Redis();
    $redis->connect(REDIS_SERVER_HOST, REDIS_SERVER_PORT);
    $val = $redis->psubscribe(['test.*']);
    assert($val);
    $val = $redis->recv();
    assert($val[0] == 'psubscribe' && $val[1] == 'test.*');

    for ($i = 0; $i < MAX_REQUESTS; $i++) {
        $val = $redis->recv();
        Assert::eq($val and $val[0], 'pmessage');
    }

    $redis->close();
});

go(function () {
    $redis = new Co\redis;
    $redis->connect(REDIS_SERVER_HOST, REDIS_SERVER_PORT);
    co::sleep(0.1);

    for ($i = 0; $i < MAX_REQUESTS; $i++) {
        $ret = $redis->publish('test.a', 'hello-' . $i);
        assert($ret);
    }
});

?>
--EXPECT--
