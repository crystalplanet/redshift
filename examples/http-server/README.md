## Simple HTTP server

Here is the simplest HTTP server implementation using redshift:

```php
<?php

require '../../vendor/autoload.php';

use CrystalPlanet\Redshift\Redshift;
use CrystalPlanet\Redshift\Stream\Stream;

Redshift::run(function () {
    $stream = stream_socket_server('tcp://0.0.0.0:9000');
    stream_set_blocking($stream, 0);

    $server = new Stream($stream);

    while (true) {
        $client = yield $server->executeAsyncRead('stream_socket_accept');

        async(function ($client) {
            $data = "HTTP/1.1 200 OK\r\nContent-Length: 13\r\n\r\nHello World!\n";

            fwrite($client, $data);
            fclose($client);
        }, $client);
    }
});
```
