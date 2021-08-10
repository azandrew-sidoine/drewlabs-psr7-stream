<?php

use Drewlabs\Psr7Stream\Exceptions\NullStreamPointerException;
use Drewlabs\Psr7Stream\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class StreamTest extends TestCase
{

    public function testCreateStream()
    {
        $stream = Stream::new(__DIR__ . '/../../examples/test.txt', 'wb+');

        $this->assertTrue($stream instanceof StreamInterface, 'Expect the returned value to be an instanceof PSR7 StreamInterface');
    }

    public function testCreateStreamAndWriteToStrem()
    {

        $stream = Stream::new(null, 'wb+');
        $stream->write("Hello World!\n");
        $stream->rewind();
        file_put_contents(__DIR__ . '/../../examples/test.txt', $stream->getContents());
        $stream->close();
        $this->assertTrue(file_get_contents(__DIR__ . '/../../examples/test.txt') === "Hello World!\n", 'Expect the strem content to written successfully to file');
    }

    public function testWriteErrorAfterClose()
    {
        $this->expectException(NullStreamPointerException::class);
        $stream = Stream::new(null, 'wb+');
        $stream->write("Hello World!\n");
        $stream->close();
        $stream->write("Hello Suckers!\n");
    }

    public function testGetSizeMethod()
    {
        $stream = Stream::new(null, 'wb+');
        $stream->write("Hello World!\n");
        $size = $stream->getSize();
        $this->assertIsInt($size, 'Expect the getSize() to return an integer');
    }

    public function testRewindMethod()
    {
        $stream = Stream::new(null, 'wb+');
        $stream->write("World!\n");
        $stream->seek(0);
        $stream->write("Hello, ");
        $stream->seek(0);
        $haystack = $stream->getContents();
        $needle = "Hello, ";
        $starts_with = ('' === $needle) || (\mb_substr($haystack, 0, \mb_strlen($needle)) === $needle);
        $this->assertTrue($starts_with, 'Expect the Hello, content to be written at the start of the stream');
    }

}