<?php

namespace Brikphp\Tests\Http\Controller;

use Brikphp\Http\Controller\Controller;

class TestController extends Controller
{
    /**
     * Expose la méthode protégée `setResponse` pour les tests.
     *
     * @param int $code
     * @param string|null $message
     * @param array|string $data
     * @return self
     */
    public function testSetResponse(int $code, ?string $message = null, array|string $data = []): self
    {
        return $this->setResponse($code, $message, $data);
    }

    /**
     * Expose la méthode protégée `notFound` pour les tests.
     *
     * @return self
     */
    public function testNotFound(): self
    {
        return $this->notFound();
    }

    /**
     * Expose la méthode protégée `badRequest` pour les tests.
     *
     * @param array|string $errors
     * @return self
     */
    public function testBadRequest(array|string $errors): self
    {
        return $this->badRequest($errors);
    }

    /**
     * Expose la méthode protégée `unauthorized` pour les tests.
     *
     * @return self
     */
    public function testUnauthorized(): self
    {
        return $this->unauthorized();
    }

    /**
     * Expose la méthode protégée `forbidden` pour les tests.
     *
     * @return self
     */
    public function testForbidden(): self
    {
        return $this->forbidden();
    }

    /**
     * Expose la méthode protégée `toJson` pour les tests.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function testToJson(): \Psr\Http\Message\ResponseInterface
    {
        return $this->toJson();
    }
}
