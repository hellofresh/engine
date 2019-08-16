<?php

namespace HelloFresh\Engine\Domain;

/**
 * Class StreamName
 *
 * @package Prooph\EventStore\Stream
 * @author Alexander Miertsch <contact@prooph.de>
 */
class StreamName
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param $name
     */
    public function __construct($name)
    {
        if (!\is_string($name)) {
            throw new \InvalidArgumentException('StreamName must be a string!');
        }
        $len = \strlen($name);
        if ($len === 0 || $len > 200) {
            throw new \InvalidArgumentException('StreamName must not be empty and not longer than 200 chars!');
        }
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
