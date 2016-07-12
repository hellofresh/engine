<?php

namespace HelloFresh\Engine\Middleware;

interface MiddlewareInterface
{
    /**
     * @param mixed $passable
     * @param \Closure $next
     * @return mixed
     */
    public function handle($passable, $next);
}
