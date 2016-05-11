## redshift

A PHP library aimed at providing facilities for asynchronous programming and communication. Based on goroutines and core.async.

## What problem does it solve ?

PHP is great at serving web pages. But today we no longer talk about web pages but web applications, which are more complex than ever, and have to meet the ever growing expectations. To meet those expectations, a new ways of writing applications are necessary. Even though the language has drastically evolved, in most cases, it's still used as an oversophisticated template.

Because of it's lack of concurrency features, PHP needs a process manager like Apaches *mod_php* or *php-fpm* to run on the server. Therefor the application has no control over it's lifetime and keeping track of stuff between the requests becomes very complicated.

Redshift aims to provide way to achieve concurrency in PHP, making way for a new generation of 'pure' applications.

## Bootstrapping the application

A redshift application is initiated throught a ```Redshift::run()``` call. It expects a function/method as an argument that will act as a `main()`.

```php
use CrystalPlanet\Redshift\Redshift;

Redshift::run(function () {
    // write code here
});
```

## async()

You can add asynchronous callbacks by using the ```async()``` function, which actually is just an alias for ```Redshift::async()```.

```php
Redshift::run(function () {
    
    async(function () {
        echo "World!\n";
    });

    echo "Hello ";
});
```
The above example will output:

```
Hello World!
```

Since redshift runs on one thread, ```async()``` is basically just altering the execution order. Everything within the function still happens synchronously and blocks the execution of everything else. Because of that, to take full advantage of the library, it's best to split the code up into as many ```async()``` blocks as possible and synchronize them when necessary.

## Channels

Communication between processes in redshift happens through channels, objects which accept and return messages.

By default, channels use a ```BlockingBuffer```, which blocks on both reads and writes. It means that the execution can't proceed past a `read()` call if there are no messages in the channel, but it also can't proceed past a `write()` call if the channel is full, or there is no process ready to read the message from the channel. Because of that, we can guarantee the state of the two processes at the time of a read and write operation.

```php
use CrystalPlanet\Redshift\Channel\Channel;

Redshift::run(function () {

    // Create a new channel
    $channel = new Channel();

    async(function ($channel) {
        for ($i = 0; $i<5; ++$i) {
            echo "write: $i\n";

            yield from $channel->write($i);
        }
    }, $channel);

    for ($i = 0; $i<5; ++$i) {
        $x = yield from $channel->read();
        echo "read: $x\n";
    }
});
```

The above example will output:

```
write: 0
write: 1
read: 0
read: 1
write: 2
write: 3
read: 2
read: 3
write: 4
read: 4
```

### Asynchronously writes and reads

Channels also provide ```put()` and ```take()``` methods which write and remove the messages from a channel asynchronously.

```php
use CrystalPlanet\Redshift\Channel\Channel;

Redshift::run(function () {
    $channel = new Channel();

    async(function ($channel) {
        for ($i = 0; $i<5; ++$i) {
            echo "write\n";
            $channel->put($i);
        }
    }, $channel);

    for ($i = 0; $i<5; ++$i) {
        $channel->take();
        echo "read\n";
    }
});
```

The above example will output:

```
write
write
write
write
write
read
read
read
read
read
```
