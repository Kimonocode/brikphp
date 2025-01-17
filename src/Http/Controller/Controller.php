<?php

namespace Brikphp\Http\Controller;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

abstract class Controller
{
    private int $code = 200; // HTTP 200 OK par défaut
    private string $message = 'OK';
    private array|string $data = [];

    /**
     * Messages HTTP par défaut.
     */
    private const HTTP_MESSAGES = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];

    /**
     * Définit une réponse HTTP générique.
     *
     * @param int $code
     * @param string|null $message
     * @param array|string $data
     * @return self
     */
    protected function setResponse(int $code, ?string $message = null, array|string $data = []): self
    {
        if (!isset(self::HTTP_MESSAGES[$code])) {
            throw new \InvalidArgumentException("Code HTTP invalide : $code");
        }

        $this->code = $code;
        $this->message = $message ?? self::HTTP_MESSAGES[$code];
        $this->data = $data;

        return $this;
    }

    /**
     * Prépare une réponse JSON générique avec code 200.
     *
     * @param array $data
     * @return self
     */
    protected function send(array $data): self
    {
        return $this->setResponse(200, null, $data);
    }

    /**
     * Prépare une réponse 404 Not Found.
     *
     * @return self
     */
    protected function notFound(): self
    {
        return $this->setResponse(404);
    }

    /**
     * Prépare une réponse 403 Forbidden.
     *
     * @return self
     */
    protected function forbidden(): self
    {
        return $this->setResponse(403);
    }

    /**
     * Prépare une réponse 401 Unauthorized.
     *
     * @return self
     */
    protected function unauthorized(): self
    {
        return $this->setResponse(401);
    }

    /**
     * Prépare une réponse 400 Bad Request.
     *
     * @param string|array $errors
     * @return self
     */
    protected function badRequest(string|array $errors): self
    {
        return $this->setResponse(400, null, $errors);
    }

    /**
     * Prépare une réponse 500 Internal Server Error.
     *
     * @param string|null $message
     * @return self
     */
    protected function internalServerError(?string $message = null): self
    {
        return $this->setResponse(500, $message ?? 'Une erreur interne s’est produite.');
    }

    /**
     * Retourne une réponse au format JSON.
     *
     * @return ResponseInterface
     */
    protected function toJson(): ResponseInterface
    {
        $responseBody = [
            'status' => $this->code,
            'message' => $this->message,
            'data' => $this->data,
        ];

        $json = json_encode($responseBody, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // Gestion des erreurs de codage JSON
        if ($json === false) {
            $responseBody = [
                'status' => 500,
                'message' => 'Erreur de codage JSON',
                'data' => json_last_error_msg(),
            ];
            $json = json_encode($responseBody, JSON_UNESCAPED_UNICODE);
            $this->code = 500;
        }

        return new Response(
            $this->code,
            ['Content-Type' => 'application/json; charset=utf-8'],
            $json
        );
    }
}
