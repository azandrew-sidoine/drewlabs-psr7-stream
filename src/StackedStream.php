<?php

namespace Drewlabs\Psr7Stream;

use Drewlabs\Psr7Stream\Exceptions\StreamException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

class StackedStream implements StreamInterface
{
    use StringableStream, ReadableStream;

    /**
     * 
     * @var StreamInterface[]
     */
    private $streams = [];

    /**
     * 
     * @var true
     */
    private $seekable = true;


    /**
     * 
     * @var int
     */
    private $current = 0;

    /**
     * Tracks the current pointer position in the stream
     * 
     * @var int
     */
    private $pos = 0;

    /**
     * Creates a chunked stream object that we track all of the streams
     * during read, seek, close, detach action calls
     * 
     * **Notee**
     * The chunked stream class is 
     * 
     * @param (StreamInterface|string)[] $streams 
     * 
     * @throws InvalidArgumentException 
     */
    public function __construct(...$streams)
    {
        $streams = ($arguments = func_get_args()) && is_array($arguments[0] ?? null) ? $arguments[0] : $arguments;
        foreach ($streams as $stream) {
            if (!($stream instanceof StreamInterface)) {
                $stream = StreamFactory::lazy($stream);
            }
            $this->push($stream);
        }
    }

    /**
     * 
     * @param StreamInterface $stream 
     * @return void 
     * @throws InvalidArgumentException 
     */
    public function push(StreamInterface $stream)
    {
        if (null === $this->streams) {
            $this->streams = [];
        }
        if (!$stream->isReadable()) {
            throw new \InvalidArgumentException('Each stream appended should be readable');
        }
        if (!$stream->isSeekable()) {
            $this->seekable = false;
        }
        array_push($this->streams, $stream);
    }

    /**
     * Remove and returned the last stream of the chunks
     * 
     * @return null|StreamInterface 
     */
    public function pop()
    {
        if (null === $this->streams) {
            return null;
        }
        $stream = array_pop($this->streams);

        $this->seekable = true;
        
        foreach ($this->streams as $stream) {
            if (!$stream->isSeekable()) {
                $this->seekable = false;
                break;
            }
        }
        $this->rewind();
        return $stream;
    }

    public function close()
    {
        $this->pos = $this->current = 0;
        $this->seekable = true;

        foreach ($this->streams ?? [] as $stream) {
            $stream->close();
        }
        $this->streams = null;
    }

    public function detach()
    {
        $this->pos = $this->current = 0;
        $this->seekable = true;

        // Detach all stream and return null as it's not clear which underlying
        // stream to return
        foreach ($this->streams ?? [] as $stream) {
            $stream->detach();
        }
        $this->streams = null;
        return null;
    }

    public function getSize()
    {
        $size = 0;
        foreach ($this->streams as $stream) {
            if (null === ($size_ = $stream->getSize())) {
                return null;
            }
            $size += $size_;
        }
        return $size;
    }

    public function tell()
    {
        return $this->pos;
    }

    public function eof()
    {
        // We reach an end of file there is no stream in the $this->streams
        // array or the current position if greater that the streams last index
        // & the last stream reached an end of file
        return !$this->streams ||
            (($this->current >= count($this->streams) - 1) &&
                isset($this->streams[$this->current]) && $this->streams[$this->current]->eof());
    }

    public function isSeekable()
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->seekable) {
            throw StreamException::notSeekable(__CLASS__);
        } else if ($whence !== \SEEK_SET) {
            throw StreamException::notSeekable(__CLASS__, ' Can only be set with SEEK_SET');
        }
        $this->pos = $this->current = 0;
        foreach ($this->streams as $stream) {
            try {
                $stream->rewind();
            } catch (\Exception $e) {
                throw StreamException::seekException(__CLASS__, 0);
            }
        }

        // Seek the actual position by reading position of each stream
        while ($this->pos < $offset && !$this->eof()) {
            $result = $this->read(min(8096, $offset - $this->pos));
            if ($result === '') {
                break;
            }
        }
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function read($length)
    {
        // Read the appendded streams until the length is reached or EOF
        $buffer = '';
        $lastIndex = count($this->streams) - 1;
        // Initialize the countdown variable with the length parameter
        $iteration = $length;
        // Indicates whether to proceed to the next chunk of stream
        $next = false;

        while ($iteration > 0) {
            // 
            if ($next || $this->streams[$this->current]->eof()) {
                $next = false;
                // When we reach last chunk and we are at the end of the stream
                // we stop the read operation
                if ($this->current === $lastIndex) {
                    break;
                }
                // When we should proceed to the next stream we advance the stream chunk cursor
                $this->current++;
            }
            // we read the current stream content
            $result = $this->streams[$this->current]->read($iteration);
            // If the result of the read operation return and empty string
            // we have completed reading the current chunk, we processed to the next chunk
            if ($result === '') {
                $next = true;
                continue;
            }
            $buffer .= $result;
            $iteration = $length - strlen($buffer);
        }
        $this->pos += strlen($buffer);
        return $buffer;
    }

    public function getContents()
    {
        return $this->toString();
    }

    public function getMetadata($key = null)
    {
        return $key ? null : [];
    }
}
