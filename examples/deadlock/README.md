# Deadlock

While it's natural for the ```read()``` operation to be blocking if there is nothing to read from, redshift will actually also block on ```write()``` operations if the channel is full, or there is no consumer ready to receive the message.

This behavior allows for synchronization between the running functions. However, it can also cause deadlocks when performing synchronous reads and writes. Let's take a look at the following code:

```php
use CrystalPlanet\Redshift\Redshift;
use CrystalPlanet\Redshift\Channel\Channel;

Redshift::run(function () {
    $channel = new Channel();

    // Write 'foo' to a channel
    yield from $channel->write('foo');

    async(function ($channel) {
        // Read 'foo' from a channel
        $message = yield from $channel->read();

        // Print 'foo'
        echo $message . PHP_EOL;
    }, $channel);
});
```

When you run the code, it would be natural to expect it to print ```foo``` and exit. But instead of doing that, it will actually freeze and won't print anything.

That's because the function will pause after ```yield from $channel->write('foo');```, and wait until some other function is ready to consume the value. Therefor any code following the write statement cannot be reached.

To prevent the deadlock, you need to asynchronously execute the write operation:

```php
Redshift::run(function () {
    $channel = new Channel();

    // Make the write asynchronous
    async(function ($channel) {
        // Write 'foo' to a channel
        yield from $channel->write('foo');
    }, $channel);

    async(function ($channel) {
        // Read 'foo' from a channel
        $message = yield from $channel->read();

        // Print 'foo'
        echo $message . PHP_EOL;
    }, $channel);
});
```

You can also use the asynchronous `put()` instead of `write()`:

```php
Redshift::run(function () {
    $channel = new Channel();

    $channel->put('foo');

    async(function ($channel) {
        // Read 'foo' from a channel
        $message = yield from $channel->read();

        // Print 'foo'
        echo $message . PHP_EOL;
    }, $channel);
});
```
