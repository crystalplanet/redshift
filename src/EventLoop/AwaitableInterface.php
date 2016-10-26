<?php

namespace CrystalPlanet\Redshift\EventLoop\AwaitableInterface;

interface AwaitableInterface
{
    /**
     */
    function setEventLoop();

    /**
     * Sets the awaitable status to awaiting.
     */
    function await(Process $process);

    /**
     * Returns true if awaitable is still waiting for
     * an event to fulfill its condition.
     *
     * @return boolean
     */
    function isAwaiting();

    /**
     * Notifies the awaitable it 
     */
    function notify();

    /**
     * The delay in milliseconds.
     *
     * @return integer
     */
    function delay();
}
