<?php

namespace HelloFresh\Engine\Middleware;

interface PipelineInterface
{
    /**
     * @param mixed $passable
     */
    public function pass($passable);

    /**
     * @param MiddlwareInterface $middleware
     */
    public function through(MiddlewareInterface $middleware);

    /**
     * @return mixed
     */
    public function run();
}
