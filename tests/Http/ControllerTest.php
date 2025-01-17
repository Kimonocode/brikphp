<?php

namespace Brikphp\Tests\Http\Controller;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class ControllerTest extends TestCase
{
    private TestController $controller;

    protected function setUp(): void
    {
        // Crée une instance d'une classe testable
        $this->controller = new TestController();
    }

    public function testSetResponse()
    {
        // Test de la méthode `setResponse` avec un code 200
        $this->controller->testSetResponse(200, 'Custom Message', ['key' => 'value']);
        $jsonResponse = $this->controller->testToJson();

        $this->assertInstanceOf(Response::class, $jsonResponse);
        $body = json_decode($jsonResponse->getBody()->getContents(), true);

        // Vérifie le code de statut, le message et les données
        $this->assertEquals(200, $body['status']);
        $this->assertEquals('Custom Message', $body['message']);
        $this->assertEquals(['key' => 'value'], $body['data']);
    }

    public function testDefaultResponse()
    {
        // Test des valeurs par défaut de la réponse (200, OK)
        $jsonResponse = $this->controller->testToJson();

        $this->assertInstanceOf(Response::class, $jsonResponse);
        $body = json_decode($jsonResponse->getBody()->getContents(), true);

        // Vérifie le statut, le message et les données par défaut
        $this->assertEquals(200, $body['status']);
        $this->assertEquals('OK', $body['message']);
        $this->assertEquals([], $body['data']);
    }

    public function testNotFoundResponse()
    {
        // Test de la réponse 404
        $this->controller->testNotFound();
        $jsonResponse = $this->controller->testToJson();

        $body = json_decode($jsonResponse->getBody()->getContents(), true);

        // Vérifie le statut 404 et le message "Not Found"
        $this->assertEquals(404, $body['status']);
        $this->assertEquals('Not Found', $body['message']);
    }

    public function testBadRequestResponse()
    {
        // Test de la réponse 400 avec des erreurs
        $this->controller->testBadRequest(['error' => 'Invalid data']);
        $jsonResponse = $this->controller->testToJson();

        $body = json_decode($jsonResponse->getBody()->getContents(), true);

        // Vérifie le statut 400, le message "Bad Request" et les erreurs
        $this->assertEquals(400, $body['status']);
        $this->assertEquals('Bad Request', $body['message']);
        $this->assertEquals(['error' => 'Invalid data'], $body['data']);
    }

    public function testUnauthorizedResponse()
    {
        // Test de la réponse 401 Unauthorized
        $this->controller->testUnauthorized();
        $jsonResponse = $this->controller->testToJson();

        $body = json_decode($jsonResponse->getBody()->getContents(), true);

        // Vérifie le statut 401 et le message "Unauthorized"
        $this->assertEquals(401, $body['status']);
        $this->assertEquals('Unauthorized', $body['message']);
    }

    public function testInternalServerErrorResponse()
    {
        // Test de la réponse 500 Internal Server Error
        $this->controller->testSetResponse(500, 'Something went wrong');
        $jsonResponse = $this->controller->testToJson();

        $body = json_decode($jsonResponse->getBody()->getContents(), true);

        // Vérifie le statut 500 et le message d'erreur
        $this->assertEquals(500, $body['status']);
        $this->assertEquals('Something went wrong', $body['message']);
    }

    public function testInvalidHttpCode()
    {
        // Test pour s'assurer qu'une exception est levée avec un code HTTP invalide
        $this->expectException(\InvalidArgumentException::class);
        $this->controller->testSetResponse(999); // 999 n'est pas un code HTTP valide
    }

    public function testJsonEncodingError()
    {
        // Injecte une structure récursive pour provoquer une erreur de codage JSON
        $recursiveArray = [];
        $recursiveArray['self'] = &$recursiveArray;

        $this->controller->testSetResponse(200, 'OK', $recursiveArray);
        $jsonResponse = $this->controller->testToJson();

        $body = json_decode($jsonResponse->getBody()->getContents(), true);

        // Vérifie que l'erreur de codage JSON est bien gérée
        $this->assertEquals(500, $body['status']);
        $this->assertEquals('Erreur de codage JSON', $body['message']);
        $this->assertStringContainsString('Recursion', $body['data']);
    }
}
