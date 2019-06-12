<?php
$http = new swoole_http_server("0.0.0.0", 9501);
$http->set(array(
    'reactor_num' => 2, //reactor thread num
    'worker_num' => 4,    //worker process num
    'backlog' => 128,   //listen backlog
    'max_request' => 50,
    'dispatch_mode' => 1,
));
$http->on("start", function ($server) {
    echo "Swoole http server is started at http://0.0.0.0:9501\n";
});
$http->on("request", function ($request, $response) {
    $response->header("Content-Type", "text/plain");
    $response->end("Hello World\n");
});
$http->start();