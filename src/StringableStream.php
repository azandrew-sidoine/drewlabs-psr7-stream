<?php

namespace Drewlabs\Psr7Stream;

trait StringableStream
{
    #[\ReturnTypeWillChange]
    public function __toString(): string
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (\Throwable $e) {
            if (\PHP_VERSION_ID >= 70400) {
                throw $e;
            }
            trigger_error(sprintf('%s::__toString exception: %s', self::class, (string) $e), E_USER_ERROR);
            return '';
        }
    }

    public function toString(int $bufferSize = -1)
    {
        $buffer = '';

        if ($bufferSize === -1) {
            while (!$this->eof()) {
                $buf = $this->read(1048576);
                if ($buf === '') {
                    break;
                }
                $buffer .= $buf;
            }
            return $buffer;
        }

        $length = 0;
        while (!$this->eof() && $length < $bufferSize) {
            $buf = $this->read($bufferSize - $length);
            if ($buf === '') {
                break;
            }
            $buffer .= $buf;
            $length = strlen($buffer);
        }

        return $buffer;
    }
}
