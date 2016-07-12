<?php

namespace HelloFresh\Engine\Middleware;

use Collections\Stack;

class Pipeline implements PipelineInterface
{
    /**
     * @var Stack
     */
    protected $stack;

    /**
     * @var mixed
     */
    protected $passable;

    /**
     * @param array $stack
     */
    public function __construct(array $stack = [])
    {
        $this->stack = Stack::fromArray($stack);
    }

    /**
     * @param mixed $passable
     * @return MiddlewareStack
     */
    public function pass($passable)
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * @param MiddlewareInterface $middleware
     */
    public function through(MiddlewareInterface $middleware)
    {
        $this->stack->push($middleware);

        return $this;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        // If there's no middleware on the stack then just return the passable
        if (empty($this->stack)) {
            return $this->passable;
        }

        // Run the middleware stack and return it's value
        return call_user_func(array_reduce(
            $this->stack->toArray(),
            $this->layer(),
            $this->core()
        ), $this->passable);
    }

    /**
     * @return \Closure
     */
    protected function layer()
    {
        return function($next, $middleware)
        {
            return function($passable) use ($next, $middleware)
            {
                return $middleware->handle($passable, $next);
            };
        };
    }

    /**
     * @return \Closure
     */
    protected function core()
    {
        return function($passable)
        {
            return is_callable($passable) ? $passable() : $passable;
        };
    }
}
