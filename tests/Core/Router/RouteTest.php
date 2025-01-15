<?php

namespace Brikphp\Tests\Core\Router;

use Brikphp\Core\Router\Route;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;

class RouteTest extends TestCase
{
    public function testRouteInitialization()
    {
        $method = 'GET';
        $name = 'home';
        $path = '/home';
        $handler = fn() => 'handler';

        $route = new Route($method, $name, $path, $handler);

        $this->assertSame($method, $route->getMethod());
        $this->assertSame($name, $route->getName());
        $this->assertSame($path, $route->getPath());
        $this->assertSame($handler, $route->getHandler());
    }

    public function testAddMiddlewareWithValidClass()
    {
        $middlewareMock = $this->createMock(MiddlewareInterface::class);

        $route = new Route('GET', 'test', '/test', fn() => 'handler');
        $route->middleware($middlewareMock);

        $middlewares = $route->getMiddlewares();

        $this->assertCount(1, $middlewares);
        $this->assertSame($middlewareMock, $middlewares[0]);
    }

    public function testAddMiddlewareWithInvalidClassName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("La classe middleware InvalidMiddleware n'existe pas.");

        $route = new Route('GET', 'test', '/test', fn() => 'handler');
        $route->middleware('InvalidMiddleware');
    }

    public function testGetMiddlewares()
    {
        $middlewareMock1 = $this->createMock(MiddlewareInterface::class);
        $middlewareMock2 = $this->createMock(MiddlewareInterface::class);

        $route = new Route('POST', 'test', '/test', fn() => 'handler');
        $route->middleware($middlewareMock1)->middleware($middlewareMock2);

        $middlewares = $route->getMiddlewares();

        $this->assertCount(2, $middlewares);
        $this->assertSame($middlewareMock1, $middlewares[0]);
        $this->assertSame($middlewareMock2, $middlewares[1]);
    }
}
