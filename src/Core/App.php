<?php 

namespace Brikphp\Core;

use Brikphp\Core\Env\Env;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function Http\Response\send;

class App extends Kernel {
    
    /**
     * Retourne si l'application est en mode development
     * @return bool
     */
    public static function debug(): bool
    {
        return Env::get('mode', 'development') === 'development';
    }

    /**
     * Send Http Response to Client
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return void
     */
    public function emit(ResponseInterface $response): void
    {
        send($response);
    }

    /**
     * Return ServerRequet Poulated whit Globals
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function fromGlobals(): ServerRequestInterface
    {
        return ServerRequest::fromGlobals();
    }

}