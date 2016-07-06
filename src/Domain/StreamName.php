<?php

namespace HelloFresh\Engine\Domain;

use Assert\Assertion;

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
        Assertion::string($name, 'StreamName must be a string');
        Assertion::notEmpty($name, 'StreamName must not be empty');
        Assertion::maxLength($name, 200, 'StreamName should not be longer than 200 chars');
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
