<?php

declare(strict_types=1);

/*
 * This file is part of the Drewlabs package.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drewlabs\Psr7Stream;

use Drewlabs\Psr7Stream\Exceptions\FileNotFoundException;
use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * Create a new stream from the specified resource
     *
     * @param mixed $resource 
     * @param string $mode 
     * @return StreamInterface|void 
     * @throws InvalidArgumentException 
     */
    public static function createStreamFrom($resource, $mode = 'w+b')
    {
        if ($resource instanceof \Psr\Http\Message\StreamInterface) {
            return $resource;
        }

        if (\is_string($resource)) {
            return (new self())->createStream($resource);
        }
        if (\is_resource($resource)) {
            return (new self())->createStreamFromResource($resource);
        }
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param resource|StreamInterface $resource
     *
     * @throws \InvalidArgumentException
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        /*
         * @asking What if $resource is not already a resource of type *"stream"*?
         * How can I transform it into a stream, e.g. into a resource of type *"stream"*,
         * so that I can pass it further, to the call for creating a new `Stream` object
         * with it? Is there a way (a PHP method, a function, or maybe a casting function)
         * of achieving this task?
         */
        //...

        return Stream::new($resource, 'rw+b');
    }
    
    /**
     * Create a new stream from a string.
     * The stream SHOULD be created with a temporary resource.
     * 
     * @param string $content 
     * @return StreamInterface 
     * @throws InvalidArgumentException 
     */
    public function createStream($content = ''): StreamInterface
    {
        return Stream::new($content, 'rw+b');
    }

    /**
     * Create a stream from an existing file.
     *
     * The file MUST be opened using the given mode, which may be any mode
     * supported by the `fopen` function.
     *
     * The `$path` MAY be any string supported by `fopen()`.
     *
     * @param string $path
     * @param string $mode
     * @return StreamInterface 
     * @throws InvalidArgumentException 
     * @throws FileNotFoundException 
     */
    public function createStreamFromFile($path, $mode = 'r+b'): StreamInterface
    {
        if (file_exists($path) || in_array(mb_strtolower($path), ["php://memory", "php://temp"])) {
            $resource = fopen($path, $mode);
            return Stream::new($resource);
        }
        throw new FileNotFoundException($path);
    }
}
