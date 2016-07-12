<?php

namespace HelloFresh\Tests\Engine\Mock;

use HelloFresh\Engine\Middleware\MiddlewareInterface;

class AfterMiddleware implements MiddlewareInterface
{
    public function handle($passable, $next)
    {
        $result = $next($passable);

        $result .= ' world';

        return $result;
    }
}
