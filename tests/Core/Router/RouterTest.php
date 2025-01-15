<?php

namespace Brikphp\Tests\Core\Router;

use Brikphp\Core\Router\Router;
use Brikphp\Core\Router\RouterInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class RouterTest extends TestCase
{
    public function testRouteWithGetMethod()
    {
        $router = new Router();
        $router->get('Home', '/', function () {
            return new Response(200, [], 'Hello World');
        });

        $request = new ServerRequest('GET', '/');
        $response = $router->dispatch($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello World', (string)$response->getBody());
    }

    public function testRouteWithPostMethod()
    {
        $router = new Router();
        $router->post('home', '/', function (ServerRequestInterface $request) {
            $message = $request->getParsedBody()['message'];
            return new Response(200, [], $message);
        });

        $request = (new ServerRequest('POST', '/'))->withParsedBody(['message' => 'hello']);

        $response = $router->dispatch($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('hello', (string)$response->getBody());
    }

    public function testRouteWithDynamicParameter()
    {
        $router = new Router();
        $router->get('user.show', '/user/{id}', function (ServerRequestInterface $request) {
            $params = $request->getAttribute('params');
            return new Response(200, [], "User ID: " . $params['id']);
        });

        $request = new ServerRequest('GET', '/user/42');
        $response = $router->dispatch($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('User ID: 42', (string)$response->getBody());
    }

    public function testRoutesCanGrouped()
    {
        $router = new Router();

        $router->group([], function(RouterInterface $router) {

            $router->get('home', '/', function() {
                return new Response(200, [], 'hello');
            });

            $router->get('home2', '/home2', function() {
                return new Response(200, [], 'hello2');
            });

            $router->post('home3', '/home3', function() {
                return new Response(200, [], 'hello3');
            });
        });

        $this->assertCount(2, $router->getRoutes('GET'));
        $this->assertCount(1, $router->getRoutes('POST'));

        $request1 = new ServerRequest('GET', '/');
        $request2 = new ServerRequest('GET', '/home2');
        $request3 = new ServerRequest('POST', '/home3');

        $response1 = $router->dispatch($request1);
        $response2 = $router->dispatch($request2);
        $response3 = $router->dispatch($request3);

        $this->assertSame('hello', (string) $response1->getBody());
        $this->assertSame('hello2', (string) $response2->getBody());
        $this->assertSame('hello3', (string) $response3->getBody());
    }

    public function testGroupAndPrefix()
    {
        $router = new Router();

        $router->group([
            'prefix' => '/test'
        ], function(RouterInterface $router) {
            $router->get('test', '/', function () {
                return new Response(200, [], 'test prefix');
            });
        });

        $request = new ServerRequest('GET', "/test");
        $response = $router->dispatch($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('test prefix', (string) $response->getBody());

    }

    public function testRouteIsNotFound()
    {
        $this->expectException(\InvalidArgumentException::class);
        $router = new Router();
        $request = new ServerRequest('GET', '/azeazeze');

        $router->dispatch($request);
    }

    public function testExceptionIfRouteAlReadyExistByNameInGet()
    {
        $this->expectException(RuntimeException::class);
        $router = new Router();
        $router->get('home', '/', function() {});
        $router->get('home', '/', function() {});
    }

    public function testExceptionIfRouteAlReadyExistByNameInPost()
    {
        $this->expectException(RuntimeException::class);
        $router = new Router();
        $router->post('home', '/', function() {});
        $router->post('home', '/', function() {});
    }

    public function testExceptionIfRouteAlReadyExistByNameInPut()
    {
        $this->expectException(RuntimeException::class);
        $router = new Router();
        $router->put('home', '/', function() {});
        $router->put('home', '/', function() {});
    }

    public function testExceptionIfRouteAlReadyExistByNameInDelete()
    {
        $this->expectException(RuntimeException::class);
        $router = new Router();
        $router->delete('home', '/', function() {});
        $router->delete('home', '/', function() {});
    }

}