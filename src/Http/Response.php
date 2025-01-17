<?php

namespace Brikphp\Http;

class Response extends \GuzzleHttp\Psr7\Response {

    public function __construct($status = 200, $headers = [], $body = "") {
        parent::__construct($status, $headers, $body);
    }
    
}