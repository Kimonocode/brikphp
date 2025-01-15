<?php

namespace Brikphp\Core\Env;

use Brikphp\Core\Env\EnvInterface;
use Brikphp\Core\Kernel;

class Env implements EnvInterface {

    /**
     * Récupère une variable d'environnement, ou une valeur par défaut si elle n'existe pas
     * Vérifie également dans le container DI si la variable n'est pas trouvée dans les variables d'environnement
     * 
     * @param string $key
     * @param mixed $default Valeur par défaut si la variable n'existe pas
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Vérifier que la clé n'est pas vide
        if (empty($key)) {
            throw new \InvalidArgumentException("La clé fournie est invalide ou vide.");
        }

        // 1. Vérifier si la variable existe dans les variables d'environnement
        $value = getenv($key);

         // 2. Si la variable d'environnement n'existe pas, vérifier dans le container DI
        if ($value === false && Kernel::container()->has($key)) {
            $value = Kernel::container()->get($key);
        }

        // 3. Retourner la valeur trouvée ou la valeur par défaut
        return $value !== false ? $value : $default;
    }
}