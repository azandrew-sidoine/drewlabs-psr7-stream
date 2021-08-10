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

* Creating a Psr7 Stream from file path

```php
// ...
use Drewlabs\Psr7Stream\StreamFactory;

// ...

// Create a PSR7 stream factory instance
$factory = new StreamFactory();

// Creates stream from path
$stream = $factory->createStreamFromFile(__DIR__ . '/../../examples/test.txt');

```
