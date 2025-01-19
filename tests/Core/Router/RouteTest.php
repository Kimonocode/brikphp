<?php

use PHPUnit\Framework\TestCase;
use Brikphp\Core\Router\Route;
use Psr\Http\Server\MiddlewareInterface;

class MockMiddleware implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Server\RequestHandlerInterface $handler): Psr\Http\Message\ResponseInterface 
    {
        return $handler->handle($request);
    }
}

class RouteTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $method = 'GET';
        $path = '/test';
        $name = 'test_route';
        $handler = function () {
            return 'Handler executed';
        };

        $route = new Route($method, $path, $name, $handler);

        $this->assertSame($method, $route->getMethod());
        $this->assertSame($path, $route->getPath());
        $this->assertSame($name, $route->getName());
        $this->assertSame($handler, $route->getHandler());
    }

    public function testMiddlewareAddingWithInstances()
    {
        $route = new Route('GET', '/test', 'test_route');

        $middleware = new MockMiddleware();
        $route->middleware($middleware);

        $middlewares = $route->getMiddlewares();
        $this->assertCount(1, $middlewares);
        $this->assertInstanceOf(MockMiddleware::class, $middlewares[0]);
    }

    public function testMiddlewareAddingWithClassName()
    {
        $route = new Route('GET', '/test', 'test_route');

        $route->middleware(MockMiddleware::class);

        $middlewares = $route->getMiddlewares();
        $this->assertCount(1, $middlewares);
        $this->assertInstanceOf(MockMiddleware::class, $middlewares[0]);
    }

    public function testMiddlewareThrowsForInvalidClass()
    {
        $this->expectException(\InvalidArgumentException::class);

        $route = new Route('GET', '/test', 'test_route');
        $route->middleware('InvalidClassName');
    }

    public function testMiddlewareThrowsForInvalidInstance()
    {
        $this->expectException(\RuntimeException::class);

        $route = new Route('GET', '/test', 'test_route');
        $route->middleware(\stdClass::class);
    }

    public function testMiddlewareThrowsForInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $route = new Route('GET', '/test', 'test_route');
        $route->middleware(123);
    }
}
