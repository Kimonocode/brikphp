<?php

namespace Brikphp\Core\Router;

use Psr\Http\Server\MiddlewareInterface;

interface RouteInterface {
    
    /**
     * Renvoie la méthode de la route
     *
     * @return string
     */
    public function getMethod(): string;
    
    /**
     * Renvoie le nom de la route
     *
     * @return string
     */
    public function getName(): string;
    
    /**
     * Renvoie le chemin de la route
     *
     * @return string
     */
    public function getPath(): string;
    
    /**
     * Renvoie la fonction à appeler 
     *
     * @return callable|array
     */
    public function getHandler(): callable|array;

    /**
     * Ajoute un middleware dans la liste
     * 
     * @param string|MiddlewareInterface $middleware
     * @return RouteInterface
     */
    public function middleware(string|MiddlewareInterface $middleware): static;

    /**
     * Renvoie la liste des middlewares associés à cette route
     *
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array;
}