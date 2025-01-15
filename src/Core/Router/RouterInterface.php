<?php

namespace Brikphp\Core\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface RouterInterface {
    
    /**
     * Renvoie un tableau de toutes les routes enregistrées par leurs méthodes.
     *
     * @param string $method
     * @return RouteInterface[]
     */
    public function getRoutes(string $method): array;

    /**
     * Trouve une Route par son NOm
     * 
     * @param string $name
     * @return RouteInterface|null
     */
    public function getRouteByName(string $name): ?RouteInterface;

    /**
     * Enregistre une route en GET
     *
     * @param  string $name Nom de la route
     * @param  string $path CHemin de la route
     * @param  callable|array $handler Function ou Controller à appeler
     * @return RouteInterface
     */
    public function get(string $name, string $path, callable|array $handler): RouteInterface;  

    /**
     * Enregistre une route POST
     *
     * @param  string $name Nom de la route
     * @param  string $path Chemin de la route
     * @param  callable|array $handler Function ou Controller à appeler
     * @return RouteInterface
     */
    public function post(string $name, string $path, callable|array $handler): RouteInterface;

    /**
     * Enregistre une route PUT
     *
     * @param  string $name Nom de la route
     * @param  string $path CHemin de la route
     * @param  callable|array $handler Function ou COntroller à appeler
     * @return RouteInterface
     */
    public function put(string $name, string $path, callable|array $handler): RouteInterface;  

    /**
     * Enregistre une route DELETE
     *
     * @param  string $name Nom de la route
     * @param  string$path Chemin de la roure
     * @param  callable|array $handler Function ou Controller à appeler
     * @return RouteInterface
     */
    public function delete(string $name, string $path, callable|array $handler): RouteInterface;

    /**
     * Construit un groupe de routes Pour ajouter un préfix ou un middleware commun
     * 
     * @param array $attributes
     * @param callable $router
     * @return void
     */
    public function group(array $attributes, callable $router): void;

    /**
     * Ajoute un middleware global
     * 
     * @param string|MiddlewareInterface $middleware
     * @return void
     */
    public function addGlobalMiddleware(string|MiddlewareInterface $middleware): void;

    /**
     * Parcourt toutes les routes du tableau et appelle la fonction de la route si elle est matchée.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;
}