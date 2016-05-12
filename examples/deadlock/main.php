<?php

require_once '../../vendor/autoload.php';

use CrystalPlanet\Redshift\Redshift;
use CrystalPlanet\Redshift\Channel\Channel;

Redshift::run(function () {
    $channel = new Channel();

    // Write 'foo' to a channel
    yield $channel->write('foo');

    // Read 'foo' from a channel
    $message = yield $channel->read();

    // Print 'foo'
    echo $message . PHP_EOL;
});
