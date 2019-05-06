--TEST--
swoole_channel_coro: pop timeout 2
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php
require __DIR__ . '/../include/bootstrap.php';

$chan = new \Swoole\Coroutine\Channel(1);

go(function () use ($chan) {
    co::sleep(0.5);
    Assert::eq($chan->pop(0.1), 1);
    Assert::eq($chan->pop(0.1), 'swoole');
});

go(function () use ($chan) {
    assert($chan->push(1, 0.1));
    assert(!$chan->push(2, 0.1));
    assert($chan->push('swoole', 1));
});

swoole_event_wait();
echo "DONE\n";
?>
--EXPECT--
DONE
