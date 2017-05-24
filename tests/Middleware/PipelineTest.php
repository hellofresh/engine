<?php

namespace HelloFresh\Tests\Engine\Middleware;

use HelloFresh\Engine\Middleware\Pipeline;
use HelloFresh\Tests\Engine\Mock\BeforeMiddleware;
use HelloFresh\Tests\Engine\Mock\AfterMiddleware;
use Prophecy\Prophet;
use Prophecy\Argument;

class PipelineTest extends \PHPUnit_Framework_TestCase
{
    public function testNoMiddlewareReturnsTheSame()
    {
        $stack = new Pipeline();

        $this->assertEquals('test', $stack->pass('test')->to());
    }

    public function testMiddlewareProcessesClosure()
    {
        $stack = new Pipeline();

        $this->assertEquals('test', $stack->pass('test')->to(function($passable) {
            return $passable;
        }));
    }

    public function testPassThroughMiddlware()
    {
        $stack = new Pipeline();

        $stack->pass('')
            ->through(new BeforeMiddleware)
            ->through(new AfterMiddleware);

        $this->assertEquals('hello world', $stack->to());
    }

    public function testOrderOfMiddlewareDoesntChangeOutput()
    {
        $stack = new Pipeline();

        $stack->pass('')
            ->through(new AfterMiddleware)
            ->through(new BeforeMiddleware);

        $this->assertEquals('hello world', $stack->to());
    }

    public function testStackConstructedWithMiddleware()
    {
        $stack = new Pipeline([
            new BeforeMiddleware,
            new AfterMiddleware
        ]);

        $this->assertEquals('hello world', $stack->pass('')->to());
    }
}
