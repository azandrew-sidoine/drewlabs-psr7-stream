# drewlabs-psr7-stream

Provides an implementation of the PSR7 Stream and StreamFactory interfaces

## Usage

- Creating a Psr7 Stream

```php
// ...
use Drewlabs\Psr7Stream\Stream;

// ...

// Creates PHP built-in streams such as php://memory and php://temp
$stream = Stream::new('', 'wb+');

```

- Stream Factory

* Creating a Psr7 Stream from a PHP resource

```php
// ...
use Drewlabs\Psr7Stream\StreamFactory;

// ...

// Create a PSR7 stream factory instance
$factory = new StreamFactory();

// Creates stream from resources
$stream = $factory->createStreamFromResource(fopen(__DIR__ . '/../../examples/test.txt', 'rb'));

```

- Creating a Psr7 Stream from file path

```php
// ...
use Drewlabs\Psr7Stream\StreamFactory;

// ...

// Create a PSR7 stream factory instance
$factory = new StreamFactory();

// Creates stream from path
$stream = $factory->createStreamFromFile(__DIR__ . '/../../examples/test.txt');

```

### +v1.2x

From v1.2.x releases a stacked stream implemenation and a lazy stream implementations has been added.

- Stacked Streams

Stacked stream is an abstraction of the stream interface that creates a stack of `StreamInterface` instances using contiguous memory (Array) and provides same stream interface API for working with the group as whole. For operations like `close()`, `detach()`, `read()`, `getSize()`, etc... every item is visited in the order they are inserted.

To create a Stacked stream:

```php
use Drewlabs\Psr7Stream\StreamFactory as Factory;

$stream = Factory::stack();

// TO initialize the instance at contruction time
// The stream instance is initialized with 2 chunks
$stream = Factory::stack(Stream::new(''), Stream::new(__DIR__ . '/vendor/autoload.php'))
```

**Note**
The stacked stream provide 2 additional methods for adding and removing element.

To add a new stream to the stack:

```php
use Drewlabs\Psr7Stream\StreamFactory as Factory;

$stream = Factory::stack();

// Add a new stream instance
$stream->push(Stream::new('/path/to/resource'));
```

To remove the last inserted stack:

```php
use Drewlabs\Psr7Stream\StreamFactory as Factory;

$stream = Factory::stack();

// Pop the last stream from the stack and return it
$s = $stream->pop();
```

**Warning**
Be careful when using the `pop()` method as it reset the internal pointer of the stream to avoid data corrumption.

- Lazy Stream

Lazy stream is simply an abstraction arround a stream object which resolve the stream only when the developper is ready to operate on it like `read()`, `write()`, etc... It provides a lazy creating implementation of an instance psr7 `StreamInterface`.

Lazy stream can be created passing a callable to the contructor method:

```php
use Drewlabs\Psr7Stream\StreamFactory as Factory;
use Drewlabs\Psr7Stream\Stream;
use Drewlabs\Psr7Stream\LazyStream;
//

$stream_source = 'Hello world...';

$stream = Factory::lazy(function() use (&$stream_source) {
    return Stream::new($stream_source);
});

// Or using constructor

$stream = new LazyStream(function() use (&$stream_source) {
    return Stream::new($stream_source);
});
```

or using an instance of `CreatesStream` interface, with offer an object oriented way to create a new stream:

```php
use Drewlabs\Psr7Stream\CreatesStream;
use Drewlabs\Psr7Stream\Stream;

// CreateTextStream.php
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

// main.php
$stream = new LazyStream(new CreateTextStream('/path/to/resource'));

// Or using factory function
$stream = Factory::lazy(new CreateTextStream('/path/to/resource'));
```
