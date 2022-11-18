<?php

use Drewlabs\Psr7Stream\Stream;
use Drewlabs\Psr7Stream\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class ChunkedStreamTest extends TestCase
{

    private function createStream(...$streams)
    {
        return StreamFactory::chunk($streams);
    }

    private function call(\Closure $callback, StreamInterface $stream = null)
    {
        $stream = $stream ?? $this->createStream();
        $callback($stream);
        $stream->close();
    }

    public function test_create_chunked_stream()
    {
        $this->call(function ($stream) {
            $this->assertInstanceOf(StreamInterface::class, $stream);
        });
    }

    public function test_empty_chunk_stream_size_equals_zero()
    {
        $this->call(function ($stream) {
            $this->assertEquals(0, $stream->getSize());
        });
    }

    public function test_chunk_stream_get_size_return_size_of_all_chunked_stream()
    {
        $stream1 = Stream::new('Hello World');
        $stream2 = Stream::new('Welcome to the gaming center');
        $stream = $this->createStream($stream1, $stream2);
        $this->call(function ($stream) use ($stream1, $stream2) {
            $this->assertEquals($stream1->getSize() + $stream2->getSize(), $stream->getSize());
        }, $stream);
    }

    public function test_chunked_stream_to_string()
    {
        $stream1 = Stream::new('Hello World');
        $stream2 = Stream::new('Welcome, ');
        $stream3 = Stream::new('Welcome to the gaming center');
        $stream = $this->createStream($stream1, $stream2, $stream3);
        $this->call(function ($stream) use ($stream1, $stream2, $stream3)  {
            $this->assertEquals($stream1->__toString() . $stream2->__toString() . $stream3->__toString(), $stream->__toString());
        }, $stream);
    }
}
