<?php

namespace Tests\Brikphp\Http\Controller;

use Brikphp\Http\Controller\Controller;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ControllerTest extends TestCase
{
    private Controller $controller;

    protected function setUp(): void
    {
        $this->controller = new Controller();
    }

    public function testNotFoundResponse(): void
    {
        $response = $this->controller->notFound()->toJson();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Not Found', $body['message']);
        $this->assertEmpty($body['data']);
    }

    public function testForbiddenResponse(): void
    {
        $response = $this->controller->forbidden()->toJson();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(403, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Forbidden', $body['message']);
        $this->assertEmpty($body['data']);
    }

    public function testUnauthorizedResponse(): void
    {
        $response = $this->controller->unauthorized()->toJson();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Unauthorized', $body['message']);
        $this->assertEmpty($body['data']);
    }

    public function testBadRequestResponse(): void
    {
        $errors = ['field' => 'Invalid value'];
        $response = $this->controller->badRequest($errors)->toJson();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertEquals('Bad Request', $body['message']);
        $this->assertEquals($errors, $body['data']);
    }

    public function testCustomResponse(): void
    {
        $response = $this->controller->setResponse(418, "I'm a teapot", ['info' => 'Custom message'])->toJson();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(418, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertEquals("I'm a teapot", $body['message']);
        $this->assertEquals(['info' => 'Custom message'], $body['data']);
    }

    public function testJsonEncodingErrorHandling(): void
    {
        // Simulate an invalid UTF-8 sequence
        $invalidData = "\xB1\x31";
        $response = $this->controller->setResponse(200, 'Test', $invalidData)->toJson();

        $this->assertEquals(500, $response->getStatusCode());

        $body = json_decode($response->getBody(), true);
        $this->assertEquals('JSON Encoding Error', $body['message']);
        $this->assertStringContainsString('Malformed UTF-8', $body['data']);
    }
}
