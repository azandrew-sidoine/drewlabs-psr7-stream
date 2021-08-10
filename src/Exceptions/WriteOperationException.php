<?php

declare(strict_types=1);

/*
 * This file is part of the Drewlabs package.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drewlabs\Psr7Stream\Exceptions;

use Exception;
use Throwable;

class WriteOperationException extends Exception
{
    public function __construct(string $message, ?Throwable $e = null)
    {
        parent::__construct($message, 500, $e);
    }
}