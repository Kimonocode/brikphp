<?php

namespace Brikphp\Http\Controller;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Controller
{
    private int $code = 200; // Default to HTTP 200 OK
    private string $message = 'OK';
    private array|string $data = [];

    /**
     * Table des messages HTTP par défaut.
     */
    private const HTTP_MESSAGES = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        418 => 'Teapo',
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
    public function setResponse(int $code, ?string $message = null, array|string $data = []): self
    {
        
        if (!array_key_exists($code, self::HTTP_MESSAGES)) {
            throw new \InvalidArgumentException("Code HTTP invalide : $code");
        }

        $this->code = $code;
        $this->message = $message ?? self::HTTP_MESSAGES[$code] ?? 'Unknown Status';
        $this->data = $data;

        return $this;
    }

    /**
     * Envoie des données au format json avec une réponse 200 o
     * @param array $data
     * @return Controller
     */
    public function send(array $data)
    {
        return $this->setResponse($this->code, $this->message, $data);
    }

    /**
     * Prépare une réponse 404 Not Found.
     *
     * @return self
     */
    public function notFound(): self
    {
        return $this->setResponse(404);
    }

    /**
     * Prépare une réponse 403 Forbidden.
     *
     * @return self
     */
    public function forbidden(): self
    {
        return $this->setResponse(403);
    }

    /**
     * Prépare une réponse 401 Unauthorized.
     *
     * @return self
     */
    public function unauthorized(): self
    {
        return $this->setResponse(401);
    }

    /**
     * Prépare une réponse 400 Bad Request.
     *
     * @param string|array $errors
     * @return self
     */
    public function badRequest(string|array $errors): self
    {
        return $this->setResponse(400, null, $errors);
    }

    /**
     * Retourne une réponse au format JSON.
     *
     * @return ResponseInterface
     */
    public function toJson(): ResponseInterface
    {
        $json = json_encode([
            'status' => $this->code,
            'message' => $this->message,
            'data' => $this->data,
        ], JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            $json = json_encode([
                'status' => 500,
                'message' => 'JSON Encoding Error',
                'data' => json_last_error_msg(),
            ], JSON_UNESCAPED_UNICODE);

            $this->code = 500;
        }

        return new Response(
            $this->code,
            ['Content-Type' => 'application/json'],
            $json
        );
    }
}
