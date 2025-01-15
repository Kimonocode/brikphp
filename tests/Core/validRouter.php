<?php 
            use Brikphp\Core\Router\RouterInterface;
            return new class implements RouterInterface {
                public function dispatch($request) {
                    return new class implements ResponseInterface {
                        // Implémentez les méthodes requises...
                    };
                }
            };