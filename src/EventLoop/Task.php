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
            $this->resume();
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
     * Resumes the execution of the task.
     */
    private function resume()
    {
        if (!$this->generator->valid()) {
            throw new InvalidTaskStatusException("Failed to resume a task!");
        }

        $this->generator->send(null);
    }

    /**
     * Returns true if the task can be executed/resumed.
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->generator->valid();
    }
}
