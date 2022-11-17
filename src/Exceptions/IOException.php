<?php

namespace Drewlabs\Psr7Stream\Exceptions;

use Exception;

class IOException extends Exception
{
    /**
     * 
     * @param string $message 
     * @return IOException 
     */
    public static function read(string $message)
    {
        return new self($message);
    }

    /**
     * 
     * @param string $message 
     * @return IOException 
     */
    public static function write(string $message)
    {
        return new self($message);
    }

    /**
     * 
     * @param string $path 
     * @return IOException 
     */
    public static function notFound(string $path)
    {
        return new self("File not found at path $path");
    }

}