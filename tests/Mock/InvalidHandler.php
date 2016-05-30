<?php

namespace HelloFresh\Tests\Engine\Mock;

class InvalidHandler
{
    /**
     * @var int
     */
    private $counter = 0;

    public function invalidHandleMethod(TestCommand $command)
    {
        $this->counter++;
    }

    /**
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }
}
