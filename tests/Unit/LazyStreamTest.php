<?php

use Drewlabs\Psr7Stream\LazyStream;
use Drewlabs\Psr7Stream\Stream;
use Drewlabs\Psr7Stream\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Drewlabs\Psr7Stream\CreatesStream;
use Drewlabs\Psr7Stream\Tests\Unit\NotSeekableStream;

class CreateTextStream implements CreatesStream
{
    /**
     * 
     * @var string
     * */
    private $source;

    public function __construct($source)
    {
        $this->source = $source;
    }

    public function createStream()
    {
        return Stream::new($this->source);
    }
}


class LazyStreamTest extends TestCase
{

    /**
     * 
     * @param callable|CreatesStream $createsStream 
     * @return LazyStream 
     */
    private function createStream($createsStream = null)
    {
        return StreamFactory::lazy($createsStream);
    }

    private function call(\Closure $callback, $createsStream = null)
    {
        $stream = $this->createStream($createsStream);
        $callback($stream);
        $stream->close();
    }

    public function test_lazy_stream_does_not_create_stream_unless_get_stream_invoked_and_inviked_only_once()
    {
        $invoke_counts = 0;
        $this->call(function (LazyStream $stream) use (&$invoke_counts) {
            $this->assertEquals(0, $invoke_counts);
            $stream->getStream();
            $this->assertEquals(1, $invoke_counts);

            // Assert create stream function is invoked only once
            $stream->getStream();
            $this->assertEquals(1, $invoke_counts);

            // Assert create stream function is invoked only once
            $stream->__toString();
            $this->assertEquals(1, $invoke_counts);

        }, function () use (&$invoke_counts) {
            $invoke_counts++;
            return Stream::new('');
        });
    }

    public function test_create_lazy_stream()
    {
        $this->call(function ($stream) {
            $this->assertInstanceOf(StreamInterface::class, $stream);
        });
    }

    public function test_lazy_stream_get_size()
    {
        $this->call(function ($stream) {
            $this->assertEquals(0, $stream->getSize());
        }, function () {
            return Stream::new('');
        });

        $this->call(function ($stream) {
            $this->assertEquals(5, $stream->getSize());
        }, function () {
            return Stream::new('Hello');
        });
    }

    public function test_lazy_stream_to_string()
    {
        $this->call(function ($stream) {
            $this->assertEquals('Hello World!', (string)$stream);
        }, function () {
            return Stream::new('Hello World!');
        });
    }

    public function test_lazy_stream_eof_return_true_if_get_stream_content()
    {
        $this->call(function (LazyStream $stream) {
            $stream->rewind();
            $stream->getContents();
            $this->assertEquals(true, $stream->eof());
        }, new CreateTextStream('I am a lazy stream'));
    }

    public function test_lazy_stream_not_seekable_for_not_seekable_stream()
    {
        $this->call(function (LazyStream $stream) {
            $this->assertTrue(!$stream->isSeekable());
        }, function () {
            return new NotSeekableStream('Not seekable stream');
        });
    }

    public function test_lazy_stream_read()
    {
        $this->call(function (LazyStream $stream) {
            $this->assertEquals('Hello, B', $stream->read(8));
        }, function () {
            return new NotSeekableStream('Hello, Besame Mucho');
        });
    }
}
