<?php

namespace CrystalPlanet\Redshift\Time;

use CrystalPlanet\Redshift\EventLoop\AwaitableInterface;

class Timeout implements AwaitableInterface
{
    private $timeout;

    public function __construct($timeout = 0)
    {
        if (!is_int($timeout)) {
            throw new \RuntimeException('$timeout must be a number!');
        }

        $this->timeout = $timeout + intval(microtime(true) * 1000);
    }

    public function shouldWait()
    {
        return $this->timeout - intval(microtime(true) * 1000) < 1;
    }

    public function await(Process $process)
    {
        $process->getEventLoop()->addTimeout($this->timeout, $process);
    }
}
