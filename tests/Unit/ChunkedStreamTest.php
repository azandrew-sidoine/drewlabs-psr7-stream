<?php

use Drewlabs\Psr7Stream\ChunkedStream;
use Drewlabs\Psr7Stream\Stream;
use Drewlabs\Psr7Stream\StreamFactory;
use Drewlabs\Psr7Stream\Tests\Unit\NotSeekableStream;
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
        $this->call(function ($stream) use ($stream1, $stream2, $stream3) {
            $this->assertEquals($stream1->__toString() . $stream2->__toString() . $stream3->__toString(), $stream->__toString());
        }, $stream);
    }

    public function test_chunked_stream_eof_return_true_if_get_stream_content()
    {
        $this->call(function (ChunkedStream $stream) {
            $this->assertTrue($stream->eof());

            $stream->push(Stream::new('Hello World!'));
            $stream->push(Stream::new('Welcome to the gaming center'));

            $this->assertFalse($stream->eof());

            $stream->rewind();
            $stream->getContents();

            $this->assertTrue($stream->eof());
        });
    }

    public function test_chunked_stream_seekable_if_all_stream_are_seakable()
    {
        $this->call(function(ChunkedStream $stream) {
            $stream->push(Stream::new('Wheezy...'));
            $stream->push(new NotSeekableStream('Hello World'));

            $this->assertFalse($stream->isSeekable());

            // When pop the not seekable stream, the chunk becomes seekable
            $stream->pop();
            $this->assertFalse($stream->eof());
            $stream->push(Stream::new('Trusty Tar...'));
            $this->assertTrue($stream->isSeekable());

            // When push a not seekable stream, the chunk stream becomes not seekable
            $stream->push(new NotSeekableStream('Hello World'));

            $this->assertFalse($stream->isSeekable());
        });
    }

    public function test_chunked_stream_read()
    {
        $this->call(function(ChunkedStream $stream) {
            $stream->push(Stream::new('Hello, '));
            $stream->push(Stream::new('Besame Mucho'));
            $stream->rewind();
            $this->assertEquals('Hello, B', $stream->read(8));
        });
    }
}
