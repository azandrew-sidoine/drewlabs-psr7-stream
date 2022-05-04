<?php

use Drewlabs\Psr7Stream\Exceptions\StreamException;
use Drewlabs\Psr7Stream\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class StreamTest extends TestCase
{

    public function testCreateStream()
    {
        $stream = Stream::new(__DIR__ . '/../examples/test.txt', 'wb+');

        $this->assertTrue($stream instanceof StreamInterface, 'Expect the returned value to be an instanceof PSR7 StreamInterface');
    }

    public function testCreateStreamAndWriteToStrem()
    {

        $stream = Stream::new(null, 'wb+');
        $stream->write("Hello World!\n");
        $stream->rewind();
        file_put_contents(__DIR__ . '/../examples/test.txt', $stream->getContents());
        $stream->close();
        $this->assertTrue(file_get_contents(__DIR__ . '/../examples/test.txt') === "Hello World!\n", 'Expect the strem content to written successfully to file');
    }

    public function testWriteErrorAfterClose()
    {
        $this->expectException(StreamException::class);
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

    public function testConstructorInitializesProperties()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'data');
        $stream = Stream::new($handle);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
        $this->assertIsArray($stream->getMetadata());
        $this->assertEquals(4, $stream->getSize());
        $this->assertFalse($stream->eof());
        $stream->close();
    }

    public function testStreamClosesHandleOnDestruct()
    {
        $handle = fopen('php://temp', 'r');
        $stream = Stream::new($handle);
        unset($stream);
        $this->assertFalse(is_resource($handle));
    }

    public function testConvertsToString()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = Stream::new($handle);
        $this->assertEquals('data', (string) $stream);
        $this->assertEquals('data', (string) $stream);
        $stream->close();
    }

    public function testBuildFromString()
    {
        $stream = Stream::new('data');
        $this->assertEquals('', $stream->getContents());
        $this->assertEquals('data', $stream->__toString());
        $stream->close();
    }
}
