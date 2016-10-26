<?php

namespace CrystalPlanet\Redshift\EventLoop;

use CrystalPlanet\Redshift\Utility\TimePriorityQueue;

class EventLoop
{
    /**
     * @var array
     */
    private $readStreams = [];

    /**
     * @var \SplDoublyLinkedList
     */
    private $timeouts;

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
        $this->timeouts = new \TimePriorityQueue();

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

    public function addTimeout(Process $process, $timeout)
    {
        $this->timeouts->insert($process, $timeout);

        // $this->timeouts-> wut wut wut;
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

            $timeout = $this->tick->count() === 0 ? 1 : 0; // take min from the shortest timeout and big default value

            $this->tick();
        }
    }

    /**
     */
    private function nextTick()
    {
        $this->future->rewind();

        while ($this->future->valid() && $task = $this->future->current()) {
            if (!$task->isBlocked() || !$task->isStarted()) {
                $this->tick->enqueue($task);
                $this->future->offsetUnset($this->future->key());
                $this->future->prev();
            }

            $this->future->next();
        }
    }

    private function tick()
    {
        while (!$this->tick->isEmpty() && !$this->main->isFinished()) {
            $task = $this->tick->dequeue();

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

    private function getNextTimeout()
    {
        return 
    }

    private function waitForStreamActivity($timeout = 0)
    {
        $changed = @stream_select($read = $this->readStreams, $write = [], $except = [], 0, 1000 * $timeout);

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
