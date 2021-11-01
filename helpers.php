<?php

use Drewlabs\Psr7Stream\StreamFactory;

if (!function_exists('create_psr_stream')) {

    /**
     * Creates a new Psr Stream object.
     *
     * @param \Psr\Http\Message\StreamInterface|ressource|string $ressource
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    function create_psr_stream($ressource, $accessMode = null)
    {
        return StreamFactory::createStreamFrom($ressource, $accessMode ?? 'w+b');
    }
}