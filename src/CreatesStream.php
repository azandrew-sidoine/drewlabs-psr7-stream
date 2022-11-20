<?php

namespace Drewlabs\Psr7Stream;

use Psr\Http\Message\StreamInterface;

interface CreatesStream
{

    /**
     * Invoked to create a stream instance
     * 
     * @return StreamInterface 
     */
    public function createStream();
}