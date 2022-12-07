<?php

namespace Drewlabs\Psr7Stream;

use Drewlabs\Psr7Stream\Exceptions\StreamException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

class LazyStream implements StreamInterface
{

    use StringableStream;

    /**
     * 
     * @var callable|null|string
     */
    private $createsStream;

    /**
     * 
     * @var StreamInterface
     */
    private $stream;

    /**
     * Creates a {@see Drewlabs\Psr7Stream\LazyStream} instance
     * 
     * @param callable|CreatesStream|null|string $createStream 
     */
    public function __construct($createStream = null)
    {
        $this->createsStream = $createStream;
    }

    /**
     * Returns the lazy created stream instance
     * 
     * @return StreamInterface 
     * @throws InvalidArgumentException 
     * @throws StreamException 
     */
    public function getStream()
    {
        if (null === $this->stream) {
            $this->stream = $this->createsStream instanceof CreatesStream ?
                $this->createsStream->createStream() : (is_callable($this->createsStream) ?
                    ($this->createsStream)() :
                    Stream::new($this->createsStream ?? ''));

            if (!($this->stream instanceof StreamInterface)) {
                throw new StreamException('Stream creator function must return an instance of ' . StreamInterface::class . ' . Instance of ' . (!is_null($this->stream) && is_object($this->stream) ? get_class($this->stream) : gettype($this->stream)) . ' given');
            }
        }
        return $this->stream;
    }

    public function close()
    {
        return $this->getStream()->close();
    }

    public function detach()
    {
        return $this->getStream()->detach();
    }

    public function getSize()
    {
        return $this->getStream()->getSize();
    }

    public function tell()
    {
        return $this->getStream()->tell();
    }

    public function eof()
    {
        return $this->getStream()->eof();
    }

    public function isSeekable()
    {
        return $this->getStream()->isSeekable();
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->getStream()->seek($offset, $whence);
    }

    public function rewind()
    {
        return $this->getStream()->rewind();
    }

    public function isWritable()
    {
        return $this->getStream()->isWritable();
    }

    public function write($string)
    {
        return $this->getStream()->write($string);
    }

    public function isReadable()
    {
        return $this->getStream()->isReadable();
    }

    public function read($length)
    {
        return $this->getStream()->read($length);
    }

    public function getContents()
    {
        return $this->getStream()->getContents();
    }

    public function getMetadata($key = null)
    {
        return $this->getStream()->getMetadata($key);
    }
}
