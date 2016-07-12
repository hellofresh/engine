<?php

namespace HelloFresh\Tests\Engine\Mock;

use HelloFresh\Engine\Middleware\MiddlewareInterface;

class BeforeMiddleware implements MiddlewareInterface
{
    public function handle($passable, $next)
    {
        $passable = 'hello' . $passable;

        return $next($passable);
    }
}
