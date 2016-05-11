<?php

namespace CrystalPlanet\Redshift\Buffer;

use CrystalPlanet\Redshift\Channel\Message;

interface BufferInterface
{
    /**
     * Checks if a message can be written to the buffer.
     * It is used to block the execution before a write operation.
     *
     * @return boolean
     */
    function isWriteable();

    /**
     * Writes a message to the buffer.
     *
     * @param Message $message
     */
    function write(Message $message);

    /**
     * Checks if a message can be read from the buffer.
     * It is used to block the execution before a read operation.
     *
     * @return boolean
     */
    function isReadable();

    /**
     * Returns the next message in the buffer to a consumer.
     * The message and the consumer are removed from the buffer.
     *
     * @return Message
     */
    function read();

    /**
     * Notifies the buffer there is a new consumer awaiting a message.
     */
    function addConsumer();

    /**
     * Checks if the message can be consumed.
     * It is used to block the execution after a write,
     * until the message is consumed.
     *
     * @param Message $message
     * @return boolean
     */
    function hasConsumer(Message $message);
}
