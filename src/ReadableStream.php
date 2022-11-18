<?php

namespace Drewlabs\Psr7Stream;

use Drewlabs\Psr7Stream\Exceptions\StreamException;

trait ReadableStream
{

    public function isWritable()
    {
        return false;
    }

    public function write($string)
    {
        throw StreamException::notWritable(__CLASS__);
    }

    public function isReadable()
    {
        return true;
    }
}
