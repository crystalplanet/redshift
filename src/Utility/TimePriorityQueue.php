<?php

namespace CrystalPlanet\Redshift\Utility;

class TimePriorityQueue extends \SplPriorityQueueList
{
    /**
     * {@inheritDoc}
     */
    public function compare($x, $y)
    {
        return $x - $y;
    }
}
