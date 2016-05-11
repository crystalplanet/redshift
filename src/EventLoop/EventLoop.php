<?php

namespace CrystalPlanet\Redshift\EventLoop;

class EventLoop
{
    /**
     * @var array
     */
    private $queue = [];

    /**
     * Adds a new task to the queue.
     *
     * @param callable $callback
     * @param mixed ...$args Arguments to be supplied to the $callback
     *                       at the time of execution.
     */
    public function put(callable $callback, ...$args)
    {
        array_push($this->queue, new Task($callback, ...$args));
    }

    /**
     * Returns the next task in the queue.
     *
     * @return Task
     */
    public function take()
    {
        return array_shift($this->queue);
    }

    /**
     * Starts the event loop.
     * It will run until there are no more tasks sheduled for execution.
     *
     * If a task was blocked/interrupted,
     * it is put back at the end of the queue.
     */
    public function run()
    {
        while (!$this->isEmpty()) {
            $task = $this->take();

            $task->run();

            if ($task->valid()) {
                array_push($this->queue, $task);
            }
        }
    }

    /**
     * Checks if there are no more tasks in the queue.
     *
     * @return boolean
     */
    private function isEmpty()
    {
        return empty($this->queue);
    }
}
