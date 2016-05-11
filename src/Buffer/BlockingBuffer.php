<?php

namespace CrystalPlanet\Redshift\Buffer;

use CrystalPlanet\Redshift\Channel\Message;

class BlockingBuffer implements BufferInterface
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
     * @var integer
     */
    private $consumers = 0;

    /**
     * Creates a new blocking buffer.
     *
     * @param integer $size
     */
    public function __construct($size = 1)
    {
        $this->size = $size;
    }

    /**
     * {@inheritDoc}
     */
    public function isWriteable()
    {
        return count($this->buffer) < $this->size;
    }

    /**
     * {@inheritDoc}
     */
    public function write(Message $message)
    {
        array_push($this->buffer, $message);
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
        --$this->consumers;

        return array_shift($this->buffer);
    }

    /**
     * {@inheritDoc}
     */
    public function addConsumer()
    {
        ++$this->consumers;
    }

    /**
     * {@inheritDoc}
     */
    public function hasConsumer(Message $message)
    {
        return $this->indexOf($message) < $this->consumers;
    }

    /**
     * Returns the position of the $message inside the buffer.
     * If the message is not found, returns -1.
     *
     * @param Message $message
     * @return integer
     */
    private function indexOf(Message $message)
    {
        foreach ($this->buffer as $pos => $val) {
            if ($message === $val) {
                return $pos;
            }
        }

        return -1;
    }
}
