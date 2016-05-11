<?php

use CrystalPlanet\Redshift\Redshift;

/**
 * Alias for Redshift::async().
 * Schedules the $callback for asynchronous execution.
 *
 * @param callable $callback
 * @param mixed ...$args Arguments to be supplied to the $callback
 *                       at the time of execution.
 */
function async(callable $callback, ...$args)
{
    Redshift::async($callback, ...$args);
}
