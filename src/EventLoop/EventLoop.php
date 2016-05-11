<?php

namespace CrystalPlanet\Redshift\EventLoop;

class EventLoop
{
    /**
     * @var array
     */
    private $queue = [];

    /**
     * @var Task
     */
    private $main;

    public function __construct(callable $main)
    {
        $this->main = new Task($main);

        array_push($this->queue, $this->main);
    }

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
     * It will run as long as the main function runs.
     *
     * If a task was blocked/interrupted,
     * it is put back at the end of the queue.
     */
    public function run()
    {
        while (!$this->isEmpty()) {
            $task = $this->take();

            $task->run();

            if ($task->isBlocked()) {
                array_push($this->queue, $task);
            }

            if (!$task->isBlocked() && $task === $this->main) {
                return;
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
