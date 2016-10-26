<?php

namespace CrystalPlanet\Redshift\Stream;

class StreamReader implements AwaitableInterface
{
    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function setEventLoop(EventLoop $loop)
    {
        $this->loop = $loop;
    }

    public function shouldWait()
    {
        return 
    }
}
