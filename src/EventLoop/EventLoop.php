<?php

namespace CrystalPlanet\Redshift\EventLoop;

class EventLoop
{
    /**
     * @var array
     */
    private $readStreams = [];

    /**
     * @var \SplDoublyLinkedList
     */
    private $future;

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
        $this->main = new Task($this, $main);
        $this->tick = new \SplQueue();
        $this->future = new \SplDoublyLinkedList();

        $this->addFutureTask($this->main);
    }

    /**
     * @param callable $callback
     * @param mixed ...$args
     */
    public function scheduleTask(callable $callback, ...$args)
    {
        $this->addFutureTask(new Task($this, $callback, ...$args));
    }

    public function addReadStream($stream)
    {
        array_push($this->readStreams, $stream);
    }

    /**
     * Starts the event loop.
     */
    public function run()
    {
        $timeout = 0;

        while (!empty($this->future) && !$this->main->isFinished()) {
            $this->waitForStreamActivity($timeout);
            $this->nextTick();

            $timeout = $this->tick->count() === 0 ? 1 : 0;

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
        foreach ($this->future as $offset => $task) {
            if (!$task->isBlocked() || !$task->isStarted()) {
                $this->future->offsetUnset($offset);
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
        $this->future->push($task);
    }

    private function waitForStreamActivity($timeout = 0)
    {
        $changed = @stream_select($read = $this->readStreams, $write = [], $except = [], $timeout);

        if ($changed > 0) {
            foreach ($this->future as $task) {
                if (in_array($task->getAwaitable()->getResource(), $read)) {
                    $task->getAwaitable()->notify();
                }
            }

            $this->readStreams = array_diff($this->readStreams, $read);
        }
    }
}
