<?php

namespace HelloFresh\Engine\Domain;

use Collections\Immutable\ImmVector;

class EventStream extends ImmVector
{
    /**
     * @var StreamName
     */
    private $name;

    public function __construct(StreamName $name, $array)
    {
        parent::__construct($array);
        $this->name = $name;
    }

    /**
     * @return StreamName
     */
    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }
}
