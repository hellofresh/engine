<?php

namespace HelloFresh\Tests\Engine\Mock;

class TestHandler
{
    use CounterTrait;

    public function handle(TestCommand $command)
    {
        $this->counter++;
    }
}
