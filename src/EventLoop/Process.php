<?php

namespace CrystalPlanet\Redshift\Process;

class Process
{
    private $started = false;

    /**
     * @param EventLoop $loop
     * @param callable $func
     * @param mixed ...$args
     */
    public function __construct(EventLoop $loop, callable $func, ...$args)
    {
        $this->loop = $loop;
        $this->func = $func;
        $this->args = $args;
    }

    public function shouldWait()
    {
        return $this->started
    }

    public function isValid()
    {
        return !($this->generator && $this->generator->valid());
    }

    public function run()
    {
        if ($this->started) {
            throw new \RuntimeException('Cannot start an already running process!');
        }

        $this->running = true;

        $routine = call_user_func($this->func, $this->args);

        if ($routine && $routine instanceof \Generator) {
            $this->
        }
    }

    public function resume()
    {
        if (!$this->isValid()) {
            throw new \RuntimeException('Cannot resume a task that already finished execution.');
        }

        while () {
            $this->resumeGenerator($this->generator);
        }
    }

    private function resumeGenerator(\Generator $generator)
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

    private function getCurrentValue(\Generator $generator)
    {
        if (!$generator->current() instanceof \Generator) {
            return $generator->current();
        }

        return $this->getCurrentValue($generator->current());
    }
}
