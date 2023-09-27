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

    public function close(): void
    {
        $this->getStream()->close();
    }

    public function detach()
    {
        return $this->getStream()->detach();
    }

    #[\ReturnTypeWillChange]
    public function getSize(): ?int
    {
        return $this->getStream()->getSize();
    }

    #[\ReturnTypeWillChange]
    public function tell(): int
    {
        return $this->getStream()->tell();
    }

    #[\ReturnTypeWillChange]
    public function eof(): bool
    {
        return $this->getStream()->eof();
    }

    #[\ReturnTypeWillChange]
    public function isSeekable(): bool
    {
        return $this->getStream()->isSeekable();
    }

    #[\ReturnTypeWillChange]
    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->getStream()->seek($offset, $whence);
    }

    #[\ReturnTypeWillChange]
    public function rewind(): void
    {
        $this->getStream()->rewind();
    }

    #[\ReturnTypeWillChange]
    public function isWritable(): bool
    {
        return $this->getStream()->isWritable();
    }

    #[\ReturnTypeWillChange]
    public function write($string): int
    {
        return $this->getStream()->write($string);
    }

    #[\ReturnTypeWillChange]
    public function isReadable(): bool
    {
        return $this->getStream()->isReadable();
    }

    #[\ReturnTypeWillChange]
    public function read($length): string
    {
        return $this->getStream()->read($length);
    }

    #[\ReturnTypeWillChange]
    public function getContents(): string
    {
        return $this->getStream()->getContents();
    }

    public function getMetadata($key = null)
    {
        return $this->getStream()->getMetadata($key);
    }
}
