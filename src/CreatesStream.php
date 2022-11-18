<?php

namespace Drewlabs\Psr7Stream;

use Psr\Http\Message\StreamInterface;

interface CreatesStream
{
    /**
     * The __invoke method is added for compatibility reason to make the
     * interface invokable
     * 
     * @return StreamInterface 
     */
    public function __invoke();

    /**
     * Invoked to create a stream instance
     * 
     * @return StreamInterface 
     */
    public function createStream();
}