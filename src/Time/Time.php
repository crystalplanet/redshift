<?php

namespace CrystalPlanet\Redshift\Time;

use CrystalPlanet\Redshift\Channel\Channel;
use CrystalPlanet\Redshift\Redshift;

class Time
{
    /**
     * Returns a channel on which a signal will be written after
     * the specified time.
     *
     * @param int $milliseconds
     * @return Channel
     */
    public static function after($milliseconds)
    {
        $channel = new Channel();

        Redshift::async(new Timeout($milliseconds, $channel));

        return $channel;
    }
}
