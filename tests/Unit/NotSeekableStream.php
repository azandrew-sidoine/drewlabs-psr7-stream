<?php

namespace Drewlabs\Psr7Stream\Tests\Unit;

use Drewlabs\Psr7Stream\ReadableStream;
use Drewlabs\Psr7Stream\StringableStream;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class NotSeekableStream implements StreamInterface
{
    use StringableStream, ReadableStream;

    private $content;

    /**
     * Tracks the current pointer position in the stream
     * 
     * @var int
     */
    private $pos = 0;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    public function close()
    {
        $this->pos = 0;
    }

    public function detach()
    {
        $this->pos = 0;
        return $this;
    }

    public function getSize()
    {
        return strlen($this->content);
    }

    public function tell()
    {
        return $this->pos;
    }

    public function eof()
    {
        return $this->pos === strlen($this->content);
    }

    public function isSeekable()
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new RuntimeException('Cannot seek an not seekable stream');
    }

    public function rewind()
    {
        $this->pos = 0;
    }

    public function read($length)
    {
        $buffer = substr($this->content, $this->pos, $length);
        $pos = $this->pos + strlen($buffer);
        $this->pos = min($pos, strlen($this->content));
        return $buffer;
    }

    public function getContents()
    {
        return $this->read(strlen($this->content));
    }

    public function getMetadata($key = null)
    {
        return $key ? null : [];
    }
}