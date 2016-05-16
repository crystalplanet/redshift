<?php

namespace CrystalPlanet\Redshift\Buffer;

use CrystalPlanet\Redshift\Channel\Message;

class DroppingBuffer implements BufferInterface
{
    /**
     * @var int
     */
    private $size;

    /**
     * @var array
     */
    private $buffer = [];

    /**
     * Creates a new SlidingBuffer
     *
     * @param int $size Buffer size
     */
    public function __construct($size)
    {
        $this->size = $size;
    }

    /**
     * {@inheritDoc}
     */
    public function isWriteable()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function write(Message $message)
    {
        if (count($this->buffer) < $this->size) {
            array_push($this->buffer, $message);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable()
    {
        return !empty($this->buffer);
    }

    /**
     * {@inheritDoc}
     */
    public function read()
    {
        return array_shift($this->buffer);
    }
}
