<?php

namespace CrystalPlanet\Redshift\Time;

use CrystalPlanet\Redshift\Channel\Channel;

class Timeout
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * @var int
     */
    private $timeout;

    /**
     * Creates a new timeout.
     *
     * @param int $milliseconds Time to wait in milliseconds.
     * @param Channel $channel Channel to send the signal on.
     */
    public function __construct($milliseconds, Channel $channel)
    {
        $this->channel = $channel;

        $this->timeout = $this->now() + $milliseconds;
    }

    public function __invoke()
    {
        while ($this->now() < $this->timeout) {
            yield;
        }

        $this->channel->put(true);
    }

    /**
     * Returns current time in milliseconds.
     *
     * @return int
     */
    private function now()
    {
        return round(microtime(true) * 1000);
    }
}