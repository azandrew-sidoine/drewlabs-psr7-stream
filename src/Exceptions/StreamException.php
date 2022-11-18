<?php


namespace Drewlabs\Psr7Stream\Exceptions;

use Exception;

class StreamException extends Exception
{
    /**
     * Throw a new StreamException with a write error message
     * 
     * @param string $streamClass 
     * @return StreamException 
     */
    public static function notWritable($streamClass = '')
    {
        return new self('Cannot write to a non-writable stream ' . $streamClass ?? '');
    }

    /**
     * 
     * @return StreamException 
     */
    public static function detached()
    {
        return new self('Stream is detached');
    }

    /**
     * Throw an exception with a not seekable error message
     * 
     * @param string $streamClass 
     * @return StreamException 
     */
    public static function notSeekable($streamClass = '', $message = '')
    {
        return new self(($streamClass ?? 'Stream') . ($message ?? ' is not seekable!'));
    }

    /**
     * 
     * @param string $streamClass 
     * @param int $pos 
     * @return StreamException 
     */
    public static function seekException($streamClass = '', int $pos)
    {
        return new self('unable to seek ' . $pos . ' of ' . ($streamClass ?? '') . ' stream');
    }

}