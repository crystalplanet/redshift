<?php

namespace CrystalPlanet\Redshift\Channel;

interface ChannelInterface
{
    /**
     * Writes a message to the channel.
     * Blocks if the channel isn't ready to revieve the message.
     *
     * @param mixed $messageContent
     */
    function write($messageContent);

    /**
     * Reads a message from the channel.
     * Blocks if there are no messages to read.
     *
     * @return mixed
     */
    function read();
}
