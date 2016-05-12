<?php

namespace CrystalPlanet\Redshift\Channel;

class ChannelSelector
{
    /**
     * @var array
     */
    private $actions = [];

    /**
     * Creates a new ChannelSelector
     *
     * @param mixed... $args
     */
    public function __construct(...$args)
    {
        $this->actions = array_map([$this, 'parseActions'], $args);
    }

    /**
     * Waits until any of the actions can be executed and returns the result.
     */
    public function select()
    {
        $result = $this->init();

        while (!$result) {
            yield;

            foreach ($this->actions as list($chan, $gen,,)) {
                $gen->send(null);

                if ($result = $this->checkResult($gen, $chan)) {
                    return $result;
                }
            }
        }

        return $result;
    }

    private function init()
    {
        foreach ($this->actions as $i => list($chan,, $do,)) {
            $gen = $do();
            $this->actions[$i][1] = $gen;

            if ($result = $this->checkResult($gen, $chan)) {
                return $result;
            }
        }

        return false;
    }

    private function checkResult(\Generator $generator, Channel $channel)
    {
        if (!$generator->valid()) {
            foreach ($this->actions as list(, $gen,, $undo)) {
                if ($gen && $gen !== $generator) {
                    $undo($gen->current());
                }
            }

            return [$generator->getReturn(), $channel];
        }

        return false;
    }

    private function parseActions($action)
    {
        if ($action instanceof Channel) {
            return $this->parseRead($action);
        }

        if (is_array($action) && count($action) === 2 && $action[0] instanceof Channel) {
            return $this->parseWrite(...$action);
        }

        throw new \InvalidArgumentException(
            'Channel::any() expects Channels or tuples in form of ' .
                '[$channel $message] as arguments!'
        );
    }

    private function parseRead(Channel $channel)
    {
        return [
            $channel,
            null,
            function () use ($channel) {
                return $channel->read();
            },
            function ($current) use ($channel) {
                $channel->cancelRead();
            }
        ];
    }

    private function parseWrite(Channel $channel, $message)
    {
        return [
            $channel,
            null,
            function () use ($channel, $message) {
                return $channel->write($message);
            },
            function ($current) use ($channel) {
                $channel->cancelWrite($current);
            }
        ];
    }
}
