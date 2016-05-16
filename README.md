## redshift

[![Code Climate](https://codeclimate.com/github/crystalplanet/redshift/badges/gpa.svg)](https://codeclimate.com/github/crystalplanet/redshift) [![Test Coverage](https://codeclimate.com/github/crystalplanet/redshift/badges/coverage.svg)](https://codeclimate.com/github/crystalplanet/redshift/coverage)

A PHP 7 library aimed at providing facilities for asynchronous programming and communication. Based on goroutines and core.async.

```
composer require crystalplanet/redshift
```

## What problem does it solve ?

PHP is great at serving web pages. But today we no longer talk about web pages but web applications, which are more complex than ever and have to meet the ever growing expectations. In order to do this, new ways of writing applications are necessary. Even though the language has drastically evolved, in most cases, it's still used as an oversophisticated template.

Because of it's lack of concurrency features, PHP needs a process manager like Apaches *mod_php* or *php-fpm* to run on the server. Therefor the application has no control over it's lifetime and keeping track of stuff between the requests becomes very complicated.

Redshift aims to provide way to achieve concurrency in PHP, as well as to provide means of communication between the processes, making way for a new generation of 'pure' applications.

## Explanation behind redshift

### Coroutines

When calling normal PHP functions (more generally referred to as *subroutines*), the execution is expected to start at the first line and continue until a ```return``` statement or the end of the function is reached, or an exception is thrown. Once a function returns (or throws), the local state of the function is lost. Execution will start from the beginning if a function is called again.

*Coroutines* are basically functions which keep their state inbetween calls. Instead of returning a value, they ```yield``` a series of values. PHP provides support for coroutines through [generators](http://php.net/manual/en/language.generators.overview.php). Even though it might not be so obvious at first, the very purpose of generators is to return the control of their execution to the caller, with the hope to regain it in the future.

Redshift allows to take advantage of co-routines with minimal code overhead. In fact, the only thing that separates redshift code from any 'normal' PHP application, is the need to use ```yield``` every time there is synchronization between processes.

### Tasks and event loop

By calling ```async()```, you're effectively creating a ```Task``` and queueing it for execution in the event loop. Even though all tasks run on a single thread, as PHP is single threaded, it's good to think of tasks as very very cheap processes. If PHP ever allows for working with threads by default, the library could be tweaked to run its tasks on a fixed thread pool, allowing for parallel execution.

Each task has it's own call stack and works independently from other tasks. Regular functions can be executed asynchronously, but keep in mind that the function will block everything else that's in the queue until it's done. Synchronization of tasks should happen through channels.

The event loop takes one callback as it's argument, with serves as a ```main()```. It then starts executing the ```main()```, along with all other asynchronous tasks that get queued up. It's worth noting that the event loop terminates itself as soon as the main has retrurned. It doesn't wait for any other tasks that might be awaiting execution.

### Channels

Redshift is based on the theory of *Communicating Sequential Processes* (CSP), that also lies at the base of [goroutines](https://github.com/golang/go/wiki/LearnConcurrency) and [core.async](https://github.com/clojure/core.async). Proceses can communicate with each other by exchanging messages through channels.

Operations on channels can be performed asynchronously (```put```, ```take```), and synchronously (```write```, ```read```). In case of the latter, the call is always preceded by the ```yield``` keyword, as the current coroutine is returning control of its execution to the caller - the event loop. This allows to synchronize the execution of different tasks.

Channels use a ```BlockingBuffer``` by default. This means messages that a coroutine isn't allowed to execute past a write call to such channel if there is no other coroutine ready to read from it, and vice versa. Other buffer types can be used to achieve different behaviours.

## Examples

You can find more examples with explanations in the [examples folder](https://github.com/crystalplanet/redshift/tree/master/examples).

### Bootstraping a redshift application

Running a redshift application is as simple as writing:

```php
use CrystalPlanet\Redshift\Redshift;

Redshift::run(function () {
    echo 'Hello world!' . PHP_EOL;
});
```

```
Hello World!
```

### Running an asynchronous task

Asynchronous tasks can be ran using ```async()```, which is actually just an alias for ```Redshift::async()```. Note that the example below will only output ```Quit```, as the main function doesn't wait for the execution of the async block.

```php
use CrystalPlanet\Redshift\Redshift;

Redshift::run(function () {

    async(function () {
        echo 'Hello World!' . PHP_EOL;
    });

    echo 'Quit' . PHP_EOL;
});
```

```
Quit
```

### Using channels

A channel can be used to make the main function wait for an exit signal before exiting. The main will be executed until ```$channel->read()```, which will block since the ```$channel``` is empty. It will resume once the async block has written ```Quit``` to the ```$channel```.

```php
use CrystalPlanet\Redshift\Channel\Channel;
use CrystalPlanet\Redshift\Redshift;

Redshift::run(function () {
    
    $channel = new Channel();

    async(function ($channel) {
        echo 'Hello World!' . PHP_EOL;

        yield $channel->write('Quit');
    }, $channel);

    $message = yield $channel->read();

    echo $message . PHP_EOL;
});
```

```
Hello World!
Quit
```

### Buffered channels

Buffered channels can be created by passing in a buffer as it's first argument. A buffered channel will allow to write to it without blocking, until the buffer is full.

```php
use CrystalPlanet\Redshift\Buffer\Buffer;
use CrystalPlanet\Redshift\Channel\Channel;
use CrystalPlanet\Redshift\Redshift;

Redshift::run(function () {
    
    $channel = new Channel(new Buffer(2));

    // Won't block
    yield $channel->write(true);

    // Won't block
    yield $channel->write(true);

    // Will block
    yield $channel->write(true);
});
```

### Timeouts

In order to perform non-blocking waits, timeout channels can be used. Timeout channels are just regular channels on which a value will be sent after the specified amount of time. The code below will output ```Hello```, and append ``` World!``` after 1 second.

```php
<?php

require_once 'vendor/autoload.php';

use CrystalPlanet\Redshift\Redshift;
use CrystalPlanet\Redshift\Channel\Channel;
use CrystalPlanet\Redshift\Time\Time;

Redshift::run(function () {
    $timeout = Time::after(1000);

    echo 'Hello';

    yield $timeout->read();

    echo ' World!';
});
```

```
Hello World!
```