<?php

use Brikphp\Core\Router\Router;
use Brikphp\Core\Router\RouterInterface;

return [
    RouterInterface::class => \DI\get(Router::class),
];