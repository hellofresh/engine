<?php

namespace HelloFresh\Tests\Engine\Mock;

use Predis\ClientInterface;

/**
 * A custom predis client mock with some of the magic methods added so prophecy can play nice and not mice.
 */
abstract class PredisClient implements ClientInterface
{
    abstract public function hset($key, $field, $value);

    abstract public function hexists($key, $field);

    abstract public function hget($key, $field);
}
