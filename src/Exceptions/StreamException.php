<?php


namespace Drewlabs\Psr7Stream\Exceptions;

use Exception;

class StreamException extends Exception
{
    /**
     * 
     * @return StreamException 
     */
    public static function notWritable()
    {
        return new self('Cannot write to a non-writable stream');
    }

    /**
     * 
     * @return StreamException 
     */
    public static function detached()
    {
        return new self('Stream is detached');
    }

}