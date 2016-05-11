<?php

namespace CrystalPlanet\Redshift\Channel;

/**
 * This very simple wrapper allows us to track the messages
 * inside the buffer by reference. Sending the same message over and over again
 * is not a problem anymore as each wrapper will be a different object.
 */
final class Message
{
    /**
     * @var mixed Message content
     */
    private $content;

    /**
     * @param mixed $content Message content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed Message content
     */
    public function content()
    {
        return $this->content;
    }
}
