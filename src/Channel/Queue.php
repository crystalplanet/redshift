<?php

namespace CrystalPlanet\Redshift\Channel;

class Queue
{
    /**
     * @var mixed
     */
    private $queue = [];

    /**
     * Adds the $value to the queue.
     *
     * @param mixed $value
     */
    public function enqueue($value)
    {
        array_push($this->queue, $value);
    }

    /**
     * Returns the head of the queue and removes it.
     *
     * @return mixed
     */
    public function dequeue()
    {
        return array_shift($this->queue);
    }

    /**
     * Returns the head of the queue.
     *
     * @return mixed
     */
    public function peek()
    {
        return empty($this->queue) ? null : $this->queue[0];
    }

    /**
     * Removes the $value from the queue.
     *
     * @param mixed $value
     */
    public function remove($value)
    {
        $this->queue = array_filter(
            $this->queue,
            function ($current) use ($value) {
                return $current !== $value;
            }
        );
    }

    /**
     * Returns true if the queue is empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->queue);
    }

    /**
     * Returns the amount of items in the queue.
     *
     * @return int
     */
    public function size()
    {
        return count($this->queue);
    }

    /**
     * Returns the position of $value in the queue.
     * Returns -1 if the $value is not found.
     *
     * @param mixed $value
     * @return int
     */
    public function indexOf($value)
    {
        foreach ($this->queue as $key => $val) {
            if ($value === $val) {
                return $key;
            }
        }

        return -1;
    }
}
