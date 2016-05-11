<?php

namespace CrystalPlanet\Redshift;

use CrystalPlanet\Redshift\EventLoop\EventLoop;

class Redshift
{
    /**
     * @var EventLoop
     */
    private static $loop;

    /**
     * Executes the $main function within the async context.
     *
     * @param callable $main
     */
    public static function run(callable $main)
    {
        self::$loop = new EventLoop($main);

        self::$loop->run();
    }

    /**
     * Schedules the $callback for asynchronous execution.
     *
     * @param callable $callback
     * @param mixed ...$args Arguments to be supplied to the $callback
     *                       at the time of execution.
     */
    public static function async(callable $callback, ...$args)
    {
        return self::$loop->put($callback, ...$args);
    }
}
