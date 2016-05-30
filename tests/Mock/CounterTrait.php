<?php

namespace HelloFresh\Tests\Engine\Mock;

trait CounterTrait
{
    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }
}
