<?php

namespace Brikphp\Core\Env;

interface EnvInterface {

    public static function get(string $key, $default = null);

}