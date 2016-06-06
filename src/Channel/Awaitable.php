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
}
