<?php

namespace Brikphp\Core\Router;

use Brikphp\Core\Kernel;

class ShadowRouter {

    public function __invoke() 
    {
        return Kernel::container()->get(RouterInterface::class);
    }
}