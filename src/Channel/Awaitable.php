<?php

namespace CrystalPlanet\Redshift\Channel;

class Awaitable
{
    /**
     * @var boolean
     */
    private $awaiting = false;

    /**
     * @var boolean
     */
    private $notified = false;

    /**
     * @var resource
     */
    private $resource;

    /**
     * Creates an awaitable.
     *
     * @param stream $resource Optional stream resource to be awaited.
     */
    public function __construct($resource = null)
    {
        $this->resource = $resource;
    }

    /**
     * Marks the awaitable as awaiting and returns it.
     *
     * @return self
     */
    public function await()
    {
        $this->awaiting = true;
        $this->notified = false;

        return $this;
    }

    /**
     * Returns true if the awaitable is awaiting a notification.
     *
     * @return boolean
     */
    public function isAwaiting()
    {
        return $this->awaiting && !$this->notified;
    }

    /**
     * Notifies the awaitable.
     */
    public function notify()
    {
        $this->notified = true;
    }

    /**
     * @return boolean
     */
    public function awaitsResource()
    {
        return isset($this->resource);
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
