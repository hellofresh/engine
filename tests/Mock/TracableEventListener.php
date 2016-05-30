<?php

namespace HelloFresh\Tests\Engine\Mock;

class TracableEventListener
{
    /**
     * @var bool
     */
    private $isCalled = false;

    public function isCalled()
    {
        return $this->isCalled;
    }

    public function handleEvent($value1, $value2)
    {
        $this->isCalled = true;
    }
}
