<?php

namespace HelloFresh\Tests\Engine\Mock;

class TestCommand
{
    /**
     * @var string
     */
    private $message;

    /**
     * TestCommand constructor.
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
