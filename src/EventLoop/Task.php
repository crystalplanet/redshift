<?php

namespace CrystalPlanet\Redshift\EventLoop;

class Task
{
    /**
     * @var callable
     */
    private $task;

    /**
     * @var array
     */
    private $args;

    /**
     * @var \Generator
     */
    private $generator;

    /**
     * Creates a new task.
     *
     * @param callable $task
     * @param mixed ...$args
     */
    public function __construct(callable $task, ...$args)
    {
        $this->task = $task;
        $this->args = $args;
    }

    /**
     * Executes/Resumes the task.
     */
    public function run()
    {
        if ($this->generator) {
            $this->resumeGenerator($this->generator);
            return;
        }

        $this->start();
    }

    /**
     * Begins the execution of the task.
     */
    private function start()
    {
        $this->generator = call_user_func_array($this->task, $this->args);
    }

    /**
     * Resumes the execution of the generator.
     */
    private function resumeGenerator(\Generator $generator)
    {
        if (!$generator->valid()) {
            throw new InvalidTaskStatusException("Failed to resume a task!");
        }

        if (!$generator->current() instanceof \Generator) {
            return $generator->send($generator->current());
        }

        if ($generator->current()->valid()) {
            $this->resumeGenerator($generator->current());
            return;
        }

        $generator->send($generator->current()->getReturn());
    }

    /**
     * Returns true if the task is blocked and should be put back to the queue.
     *
     * @return boolean
     */
    public function isBlocked()
    {
        return $this->generator && $this->generator->valid();
    }
}
