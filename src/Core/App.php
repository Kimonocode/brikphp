<?php 

namespace Brikphp\Core;

use Brikphp\Core\Env\Env;

class App extends Kernel {
    
    /**
     * Retourne si l'application est en mode development
     * @return bool
     */
    public static function debug(): bool
    {
        return Env::get('mode', 'production') === 'development';
    }

}