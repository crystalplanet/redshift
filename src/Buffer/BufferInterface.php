<?php

namespace CrystalPlanet\Redshift\Buffer;

use CrystalPlanet\Redshift\Channel\Message;

interface BufferInterface
{
    /**
     * Returns true if a message can be written to the buffer.
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
     * Returns true if a message can be read from the buffer.
     *
     * @return boolean
     */
    function isReadable();

    /**
     * Returns the next message in the buffer and removes it.
     *
     * @return Message
     */
    function read();
}
