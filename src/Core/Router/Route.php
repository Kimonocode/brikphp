<?php

namespace Brikphp\Core\Router;

use Psr\Http\Server\MiddlewareInterface;

class Route implements RouteInterface {
    
    /**
     * method
     *
     * @var string GET | POST
     */
    private $method;
        
    /**
     * name
     *
     * @var string
     */
    private $name;
    
    /**
     * path
     *
     * @var string
     */
    private $path;
    
    /**
     * hanlder
     *
     * @var callable|array
     */
    private $handler;

    /**
     * Tableau de middlewares
     * @var MiddlewareInterface[]
     */
    private array $middlewares = [];

    public function __construct(string $method, string $name, string $path, callable|array $handler)
    {
        $this->method = $method;
        $this->name = $name;
        $this->path = $path;
        $this->handler = $handler;
    }
    
    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }
    
     /**
     * @inheritDoc
     */
    public function getHandler(): callable|array
    {
        return $this->handler;
    }

    /**
     * @inheritDoc
     */
    public function middleware(string|MiddlewareInterface $middleware):static
    {
        if (is_string($middleware)) {
            if (!class_exists($middleware)) {
                throw new \InvalidArgumentException("La classe middleware $middleware n'existe pas.");
            }
            $middlewareInstance = new $middleware();

            if (!$middlewareInstance instanceof MiddlewareInterface) {
                throw new \RuntimeException("Le middleware $middleware doit implémenter MiddlewareInterface.");
            }

            $this->middlewares[] = $middlewareInstance;
        } elseif ($middleware instanceof MiddlewareInterface) {
            $this->middlewares[] = $middleware;
        } else {
            throw new \InvalidArgumentException("Le middleware doit être une instance de MiddlewareInterface ou une chaîne de caractères représentant un nom de classe valide.");
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}