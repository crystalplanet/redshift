<?php

namespace CrystalPlanet\Redshift\Stream;

use CrystalPlanet\Redshift\Channel\Awaitable;

class Stream
{
    /**
     * @var stream
     */
    private $stream;

    /**
     * @var Awaitable
     */
    private $reader;

    /**
     * Creates a new stream.
     *
     * @param stream $stream
     */
    public function __construct($stream)
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new \RuntimeException(
                self::class . '::__construct($stream) expects a stream as the first argument!'
            );
        }

        $this->stream = $stream;
    }

    /**
     * Performs a non-blocking fgets() on a stream.
     */
    public function fgets()
    {
        return yield $this->executeAsyncRead('fgets');
    }

    /**
     * Executes a reading function on the stream asynchronously.
     *
     * @param callable $callback Function to execute.
     * @return mixed Return from the function.
     */
    public function executeAsyncRead(callable $callback)
    {
        if ($this->reader) {
            throw new \RuntimeException('Only one process may read from a stream at a time!');
        }

        $this->reader = new Awaitable($this->stream);

        while (true) {
            $changed = @stream_select($read = [$this->stream], $write = [], $except = [], 0);

            if ($changed === 1) {
                $this->reader = null;
                return $callback($this->stream);
            }

            yield $this->reader->await();
        }
    }
}
