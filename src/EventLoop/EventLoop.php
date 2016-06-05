<?php

namespace CrystalPlanet\Redshift\EventLoop;

class EventLoop
{
    /**
     * @var Task[]
     */
    private $future = [];

    /**
     * @var \SplQueue
     */
    private $tick;

    /**
     * @var Task
     */
    private $main;

    /**
     * @param callable $main
     */
    public function __construct(callable $main)
    {
        $this->main = new Task($main);
        $this->tick = new \SplQueue();

        $this->addFutureTask($this->main);
    }

    /**
     * @param callable $callback
     * @param mixed ...$args
     */
    public function scheduleTask(callable $callback, ...$args)
    {
        $this->addFutureTask(new Task($callback, ...$args));
    }

    /**
     * Starts the event loop.
     */
    public function run()
    {
        while (!empty($this->future) && !$this->main->isFinished()) {
            $this->nextTick();
            $this->tick();
        }
    }

    /**
     * @return Task
     */
    private function nextTask()
    {
        return $this->tick->dequeue();
    }

    private function nextTick()
    {
        foreach ($this->future as $task) {
            if (!$task->isBlocked() || !$task->isStarted()) {
                $this->tick->enqueue($task);
            }
        }
    }

    private function tick()
    {
        while (!$this->tick->isEmpty() && !$this->main->isFinished()) {
            $task = $this->nextTask();

            $task->run();

            if ($task->isBlocked()) {
                $this->addFutureTask($task);
            }
        }
    }

    private function addFutureTask(Task $task)
    {
        array_push($this->future, $task);
    }
}
