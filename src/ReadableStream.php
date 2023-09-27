<?php

namespace Drewlabs\Psr7Stream;

use Drewlabs\Psr7Stream\Exceptions\StreamException;

trait ReadableStream
{

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw StreamException::notWritable(__CLASS__);
    }

    public function isReadable(): bool
    {
        return true;
    }
}
