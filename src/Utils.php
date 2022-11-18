<?php

namespace Drewlabs\Psr7Stream;

use RuntimeException;

class Utils
{

    /**
     * Try open a file resource throws exception if fails
     * 
     * @param string $path 
     * @param string $mode 
     * @return resource|false 
     * @throws RuntimeException 
     */
    public static function tryFopen(string $path, string $mode)
    {
        $exception = null;
        set_error_handler(static function (int $errno, string $errstr) use ($path, $mode, &$exception): bool {
            $exception = new \RuntimeException(sprintf(
                'Unable to open "%s" using mode "%s": %s',
                $path,
                $mode,
                $errstr
            ));
            return true;
        });

        try {
            $options = substr($path, 0, 5) === 's3://' ? ['s3' => ['seekable' => true]] : [];
            $fd = fopen($path, $mode, false, stream_context_create($options));
        } catch (\Throwable $e) {
            $exception = new \RuntimeException(sprintf(
                'Unable to open "%s" using mode "%s": %s',
                $path,
                $mode,
                $e->getMessage()
            ), 0, $e);
        }
        restore_error_handler();
        if ($exception) {
            throw $exception;
        }
        return $fd;
    }
}
