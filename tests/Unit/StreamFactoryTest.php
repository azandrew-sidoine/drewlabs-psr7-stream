<?php

use Drewlabs\Psr7Stream\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class StreamFactoryTest extends TestCase
{

    public function testCreateStreamFromMethod()
    {
        $factory = new StreamFactory();

        $stream = $factory->createStreamFrom('', 'w+b');

        $this->assertInstanceOf(StreamInterface::class, $stream, 'Expect the stream to be an instance of the streaminterface class');
    }

    public function testCreateStreamFromResource()
    {
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromResource(fopen(__DIR__ . '/../../examples/test.txt', 'rb'));
        $this->assertInstanceOf(StreamInterface::class, $stream, 'Expect the stream to be an instance of the streaminterface class');
    }

    public function testCreateStreamFromFile()
    {
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromFile(__DIR__ . '/../../examples/test.txt');
        var_dump($stream->getMetadata());
        $this->assertInstanceOf(StreamInterface::class, $stream, 'Expect the stream to be an instance of the streaminterface class');
    }
}