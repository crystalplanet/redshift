<?php

namespace CrystalPlanet\Redshift\Channel;

use CrystalPlanet\Redshift\Buffer\BlockingBuffer;
use CrystalPlanet\Redshift\Buffer\BufferInterface;
use CrystalPlanet\Redshift\Redshift;

class Channel
{
    /**
     * @var BufferInterface
     */
    private $buffer;

    /**
     * Creates a new channel.
     *
     * @param BufferInterface|null $buffer The buffer to use in the channel.
     *                                     Defaults to 'new BlockingBuffer();'.
     */
    public function __construct(BufferInterface $buffer = null)
    {
        $this->buffer = $buffer ?: new BlockingBuffer(1);
    }

    /**
     * Writes a message to the channel.
     * It will block if the message cannot be written or cannot be consumed.
     *
     * @param mixed $messageContent
     */
    public function write($messageContent)
    {
        while (!$this->buffer->isWriteable()) {
            yield;
        }

        $message = new Message($messageContent);

        $this->buffer->write($message);

        while (!$this->buffer->hasConsumer($message)) {
            yield;
        }
    }

    /**
     * Reads a message to the channel.
     * It will block if no message can be read.
     *
     * @return mixed
     */
    public function read()
    {
        $this->buffer->addConsumer();

        while (!$this->buffer->isReadable()) {
            yield;
        }

        return $this->buffer->read()->content();
    }

    /**
     * Asynchronously writes the message to the channel.
     *
     * @param mixed $messageContent
     */
    public function put($messageContent)
    {
        Redshift::async(function ($channel, $message) {
            yield from $channel->write($message);
        }, $this, $messageContent);
    }

    /**
     * Asynchronously removes a message from the channel.
     */
    public function take()
    {
        Redshift::async(function ($channel) {
            yield from $channel->read();
        }, $this);
    }
}
