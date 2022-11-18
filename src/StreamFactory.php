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

use Drewlabs\Psr7Stream\Exceptions\IOException;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use TypeError;

class StreamFactory implements StreamFactoryInterface
{

    /**
     * Creates a LazyStream instance
     * 
     * 
     * @param callable|CreatesStream|null|string $arg
     * @return LazyStream 
     */
    public static function lazy($arg)
    {
        return new LazyStream($arg);
    }

    /**
     * Creates a {@see ChunkedStream} instance
     * 
     * **Note**
     * Chunked Stream instances are combines a list of streams
     * in a single contiguous data structure (array) and work with
     * them as working with a normal psr7 stream
     * 
     * @param (StreamInterface|string)[] $chunks 
     * @return ChunkedStream 
     */
    public static function chunk(array $chunks = [])
    {
        return new ChunkedStream($chunks);
    }

    /**
     * Create a new stream from the specified resource
     *
     * @param mixed $resource 
     * @param string $mode 
     * @return StreamInterface|void 
     * @throws InvalidArgumentException 
     */
    public static function createStreamFrom($resource, $mode = 'r+b')
    {
        if ($resource instanceof \Psr\Http\Message\StreamInterface) {
            return $resource;
        }
        // We read from path is it's a file path
        try {
            $file_exists = file_exists($resource);
            if (!empty($resource) &&  $file_exists || in_array(mb_strtolower($resource), ["php://memory", "php://temp"])) {
                return (new self)->createStreamFromFile($resource, $mode);
            }
            return static::createFromString($resource);
        } catch (Exception $e) {
            return static::createFromString($resource);
        } catch (TypeError $e) {
            return static::createFromString($resource);
        }
    }

    private static function createFromString($resource)
    {
        if (\is_string($resource)) {
            return (new self)->createStream($resource);
        }
        if (\is_resource($resource)) {
            return (new self)->createStreamFromResource($resource);
        }
        throw new InvalidArgumentException("Resource must be of type string");
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

        return Stream::new($resource, 'w+b');
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
        return Stream::new($content, 'w+b');
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
     * @throws IOException 
     */
    public function createStreamFromFile($path, $mode = 'r+b'): StreamInterface
    {
        if (@file_exists($path) || in_array(mb_strtolower($path), ["php://memory", "php://temp"])) {
            $stream = Utils::tryFopen($path, $mode);
            return $stream ? Stream::new($stream)  : Stream::new('');
        }
        throw IOException::notFound($path);
    }
}
