<?php

namespace CrystalPlanet\Redshift\Channel;

use CrystalPlanet\Redshift\Buffer\BufferInterface;
use CrystalPlanet\Redshift\Redshift;

class Channel implements ChannelInterface
{
    /**
     * @var BufferInterface
     */
    private $buffer;

    /**
     * @var \SplQueue
     */
    private $writersQueue;

    /**
     * @var \SplQueue
     */
    private $readersQueue;

    /**
     * @var \SplQueue
     */
    private $messageQueue;

    /**
     * @param BufferInterface|null $buffer
     */
    public function __construct(BufferInterface $buffer = null)
    {
        $this->buffer = $buffer;

        $this->writersQueue = new \SplQueue();
        $this->readersQueue = new \SplQueue();
        $this->messageQueue = new \SplQueue();
    }

    /**
     * {@inheritDoc}
     */
    public function write($messageContent)
    {
        $writer = new Awaitable();
        $message = new Message($messageContent);

        $this->writersQueue->enqueue($writer);
        $this->messageQueue->enqueue($message);

        if ($this->buffer) {
            while ($this->messageQueue->bottom() !== $message || !$this->buffer->isWriteable()) {
                yield $writer->await();
            }

            $this->buffer->write($this->messageQueue->dequeue());
            $this->notifyAwaiting($this->readersQueue);

            return;
        }

        $this->notifyAwaiting($this->readersQueue);

        while ($this->readersQueue->count() <= $this->indexOf($message, $this->messageQueue)) {
            yield $writer->await();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function read()
    {
        $reader = new Awaitable();

        $this->readersQueue->enqueue($reader);

        if ($this->buffer) {
            while (!$this->buffer->isReadable()) {
                yield $reader->await();
            }

            $this->readersQueue->dequeue();
            $this->notifyAwaiting($this->writersQueue);

            return $this->buffer->read()->content();
        }

        while ($this->messageQueue->count() <= $this->indexOf($reader, $this->readersQueue)) {
            yield $reader->await();
        }        

        $index = $this->indexOf($reader, $this->readersQueue);

        $msg = $this->messageQueue->offsetGet($index);

        $this->readersQueue->offsetUnset($index);
        $this->messageQueue->offsetUnset($index);

        $this->notifyAwaiting($this->readersQueue);
        $this->notifyAwaiting($this->writersQueue);

        return $msg->content();
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

    /**
     * Returns the position of the $element in the $queue.
     *
     * @param mixed $element
     * @param \SplQueue $queue
     *
     * @return integer
     */
    private function indexOf($element, \SplQueue $queue)
    {
        foreach ($queue as $index => $value) {
            if ($element === $value) {
                return $index;
            }
        }

        return -1;
    }

    /**
     * Notifies the waiting awaitables.
     *
     * @param \SplQueue $awaitables
     */
    private function notifyAwaiting(\SplQueue $awaitables)
    {
        foreach ($awaitables as $awaitable) {
            if ($awaitable->isAwaiting()) {
                $awaitable->notify();
            }
        }
    }
}
