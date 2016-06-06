<?php

namespace CrystalPlanet\Redshift\EventLoop;

use CrystalPlanet\Redshift\Channel\Awaitable;

class Task
{
    /**
     * @var callable
     */
    private $func;

    /**
     * @var array
     */
    private $args;

    /**
     * @var boolean
     */
    private $started = false;

    /**
     * @var \Generator
     */
    private $generator;

    /**
     * @var mixed
     */
    private $generatorValue;

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
        if (!$this->generator) {
            $this->start();
        }

        if ($this->generator) {
            while (!$this->shouldWait() && $this->generator->valid()) {
                $this->resume($this->generator);

                $this->generatorValue = $this->getCurrentValue($this->generator);
            }
        }
    }

    /**
     * Returns true if the task is in progress.
     *
     * @return boolean
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Returns true if the task is blocked.
     *
     * @return boolean
     */
    public function isBlocked()
    {
        return $this->generator && $this->generator->valid() && $this->shouldWait();
    }

    /**
     * Returns true if the task is finished.
     *
     * @return boolean
     */
    public function isFinished()
    {
        return $this->started && !($this->generator && $this->generator->valid());
    }

    /**
     * Initiates the generator.
     */
    private function start()
    {
        $this->started = true;

        $this->generator = call_user_func_array($this->task, $this->args);
        $this->generatorValue = $this->getCurrentValue($this->generator);
    }

    /**
     * Resumes the execution of the generator.
     *
     * @param \Generator $generator
     */
    private function resume(\Generator $generator)
    {
        if (!$generator->current() instanceof \Generator) {
            return $generator->send($generator->current());
        }

        if ($generator->current()->valid()) {
            $this->resume($generator->current());
            return;
        }

        $generator->send($generator->current()->getReturn());
    }

    /**
     * Returns the current generator value.
     *
     * @param \Generator $generator
     * @return mixed
     */
    private function getCurrentValue(\Generator $generator)
    {
        if (!$generator->current() instanceof \Generator) {
            return $generator->current();
        }

        return $this->getCurrentValue($generator->current());
    }

    /**
     * Returns true if the task should wait before proceeding with execution.
     */
    private function shouldWait()
    {
        return $this->generatorValue &&
            $this->generatorValue instanceof Awaitable &&
            $this->generatorValue->isAwaiting();
    }
}
