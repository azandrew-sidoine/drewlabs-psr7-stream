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

use Drewlabs\Psr7Stream\Exceptions\NotReadableStreamException;
use Drewlabs\Psr7Stream\Exceptions\NotWritableStreamException;
use Drewlabs\Psr7Stream\Exceptions\NullStreamPointerException;
use Drewlabs\Psr7Stream\Exceptions\ReadOperationException;
use Drewlabs\Psr7Stream\Exceptions\WriteOperationException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    /**
     * Hash table of readable and writable stream types.
     *
     * @var array
     */
    protected const READ_WRITE_DICT = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];
    /**
     * @var resource|null A resource reference
     * */
    private $stream_;

    /**
     * @var bool
     *
     * */
    private $seekable;

    /**
     * @var bool
     *
     * */
    private $readable;

    /**
     *  @var bool
     *
     * */
    private $writable;

    /**
     * @var array|mixed|void|bool|null
     *
     * */
    private $uri;

    /**
     * @var int|null
     *
     *  */
    private $size;

    private function __construct()
    {
    }

    /**
     * Closes the stream when the destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }

            return $this->getContents();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Creates an instance of Psr7 StreamInterface
     * @param string|resource|StreamInterface $body 
     * @param string $accessMode 
     * @return Stream 
     * @throws InvalidArgumentException 
     */
    public static function new($body = '', $accessMode = 'rb')
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }
        // Ensure to create an empty string when null is passed as parameter
        $body = $body ?? '';

        if (\is_string($body)) {
            $resource = fopen('php://temp', $accessMode ?? 'rw+');
            fwrite($resource, $body);
            $body = $resource;
        }

        if (\is_resource($body)) {
            $new = new self();
            $new->stream_ = $body;
            $meta = stream_get_meta_data($new->stream_);
            $new->seekable = $meta['seekable'] && 0 === fseek($new->stream_, 0, \SEEK_CUR);
            $new->readable = isset(self::READ_WRITE_DICT['read'][$meta['mode']]);
            $new->writable = isset(self::READ_WRITE_DICT['write'][$meta['mode']]);
            return $new;
        }
        throw new \InvalidArgumentException('First argument to Stream::create() must be a string, resource or StreamInterface.');
    }

    public function close(): void
    {
        if (!isset($this->stream_)) {
            return;
        }
        if (\is_resource($this->stream_)) {
            fclose($this->stream_);
        }
        $this->detach();
    }

    public function detach()
    {
        if (!isset($this->stream_)) {
            return;
        }
        $result = $this->stream_;
        unset($this->stream_);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    public function getSize(): ?int
    {
        if (null !== $this->size) {
            return $this->size;
        }

        if (!isset($this->stream_)) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($uri = $this->getUri()) {
            clearstatcache(true, $uri);
        }

        $stats = fstat($this->stream_);
        if (!isset($stats['size'])) {
            return null;
        }
        $this->size = $stats['size'];

        return $this->size;
    }

    public function tell(): int
    {
        if (false === $result = ftell($this->stream_)) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    public function eof(): bool
    {
        return !$this->stream_ || feof($this->stream_);
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = \SEEK_SET): void
    {
        if (!$this->seekable) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (-1 === fseek($this->stream_, $offset, $whence)) {
            throw new \RuntimeException('Unable to seek to stream position ' . $offset . ' with whence ' . var_export($whence, true));
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * {@inheritDoc}
     * @param string $string 
     * @return int 
     * @throws NullStreamPointerException 
     * @throws NotWritableStreamException 
     * @throws WriteOperationException 
     */
    public function write($string): int
    {
        if (!isset($this->stream_)) {
            throw new NullStreamPointerException('Stream is detached');
        }
        if (!$this->writable) {
            throw new NotWritableStreamException('Cannot write to a non-writable stream');
        }
        // We can't know the size after writing anything
        $this->size = null;
        if (false === $result = fwrite($this->stream_, $string)) {
            throw new WriteOperationException('Unable to write to stream');
        }

        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * {@inheritDoc}
     * @param int $length
     * @return string
     * @throws NullStreamPointerException 
     * @throws InvalidArgumentException 
     * @throws ReadOperationException 
     */
    public function read($length): string
    {
        if (!isset($this->stream_)) {
            throw new NullStreamPointerException('Stream is detached');
        }
        if (!$this->readable) {
            throw new NotReadableStreamException('Cannot read from non-readable stream');
        }
        if ($length < 0) {
            throw new \InvalidArgumentException('Length parameter cannot be negative');
        }

        if (0 === $length) {
            return '';
        }
        if (false === $result = fread($this->stream_, $length)) {
            throw new ReadOperationException('Unable to read from stream');
        }

        return $result;
    }

    public function getContents(): string
    {
        if (!isset($this->stream_)) {
            throw new NullStreamPointerException('Unable to read stream contents');
        }

        if (false === $contents = stream_get_contents($this->stream_)) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    public function getMetadata($key = null)
    {
        if (!isset($this->stream_)) {
            return $key ? null : [];
        }

        $meta = stream_get_meta_data($this->stream_);

        if (null === $key) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    private function getUri()
    {
        if (false !== $this->uri) {
            $this->uri = $this->getMetadata('uri') ?? false;
        }

        return $this->uri;
    }
}
