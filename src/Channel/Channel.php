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
     * @var Queue
     */
    private $messageQueue;

    /**
     * @var Queue
     */
    private $readersQueue;

    /**
     * Waits until any of the passed instructions can be executed and returns the
     * result.
     * Passing in a channel will attempt a read, and passing in a tuple
     * in form of [$channel $message] will attempt a read.
     * The result is a tuple in form of [$message, $channel].
     *
     * @param mixed ...$args
     * @return array
     */
    public static function any(...$args)
    {
        $selector = new ChannelSelector(...$args);

        $result = yield $selector->select();

        return $result;
    }

    /**
     * Creates a new channel.
     *
     * @param BufferInterface|null $buffer The buffer to use in the channel.
     *                                     Defaults to 'new BlockingBuffer();'.
     */
    public function __construct(BufferInterface $buffer = null)
    {
        $this->buffer = $buffer;

        $this->messageQueue = new Queue();
        $this->readersQueue = new Queue();
    }

    /**
     * Writes a message to the channel.
     * It will block if the message cannot be written.
     *
     * @param mixed $messageContent
     */
    public function write($messageContent)
    {
        $message = new Message($messageContent);

        $this->messageQueue->enqueue($message);

        if ($this->buffer) {
            while ($this->messageQueue->peek() !== $message || !$this->buffer->isWriteable()) {
                yield;
            }

            $this->buffer->write($this->messageQueue->dequeue());

            return;
        }

        while ($this->readersQueue->size() - 1 < $this->messageQueue->indexOf($message)) {
            yield;
        }
    }


    public function cancelWrite(Message $message = null)
    {
        if ($message) {
            $this->buffer->remove($message);
            $this->messageQueue->remove($message);
        }
    }

    /**
     * Reads a message from the channel.
     * It will block if no message can be read.
     *
     * @return mixed
     */
    public function read()
    {
        $reader = new Reader();

        $this->readersQueue->enqueue($reader);

        while ($this->readersQueue->peek() !== $reader) {
            yield $reader;
        }

        if ($this->buffer) {
            while (!$this->buffer->isReadable) {
                yield;
            }

            $this->readersQueue->dequeue();

            return $this->buffer->read()->content();
        }

        while ($this->messageQueue->isEmpty()) {
            yield;
        }

        $this->readersQueue->dequeue();

        return $this->messageQueue->dequeue()->content();
    }

    /**
     * Cancels a read from the channel.
     *
     * @param Reader $reader
     */
    public function cancelRead(Reader $reader)
    {
        $this->readersQueue->remove($reader);
    }

    /**
     * Asynchronously writes the message to the channel.
     *
     * @param mixed $messageContent
     */
    public function put($messageContent)
    {
        Redshift::async(function ($channel, $message) {
            yield $channel->write($message);
        }, $this, $messageContent);
    }

    /**
     * Asynchronously removes a message from the channel.
     */
    public function take()
    {
        Redshift::async(function ($channel) {
            yield $channel->read();
        }, $this);
    }
}
