<?php

namespace Brikphp\Core;

use Brikphp\Core\Router\RouterInterface;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Kernel
{
    private static ?ContainerInterface $container = null;

    /**
     * fichiers de routes
     * @var string[]
     */
    private array $routesFiles = [];

    /**
     * fichiers de configuration
     * @var array
     */
    private array $configFiles = [];

    public function __construct(array $routes = [], array $config = [])
    {
        $this->routesFiles= array_merge([
            self::root() . '/start/routes.php',
        ], $routes);

        $this->configFiles = array_merge([
            __DIR__ . '/config.php',
            self::root() . '/config.php',
        ], $config);

        $this->initializeContainer();
    }

    /**
     * Démarrage de l'application
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array $routesRequired
     * @throws \RuntimeException
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function run(ServerRequestInterface $request, array $routesRequired = []): ResponseInterface
    {
        $routesRequired = $routesRequired ?: $this->routesFiles;

        /**
         * @var RouterInterface|null
         */
        $router = null;

        foreach ($routesRequired as $file) {
            if (!file_exists($file)) {
                throw new \RuntimeException("Le fichier de routes est manquant : $file");
            }

            $result = require_once $file;

            if ($result instanceof RouterInterface) {
                $router = $result;
            }
        }

        if (empty(!$router)) {
            throw new \RuntimeException("Aucun routeur valide n'a été trouvé dans les fichiers requis.");
        }

        return $router->dispatch($request);
    }

    /**
     * Renvoie le chemin du dossier de l'application
     * @return string
     */
    public static function root(): string
    {
        return dirname($_SERVER['SCRIPT_FILENAME'], 2);
    }

    /**
     * Donne accès au container di
     * @throws \RuntimeException
     * @return \Psr\Container\ContainerInterface
     */
    public static function container(): ContainerInterface
    {
        if (!self::$container) {
            throw new \RuntimeException("Le conteneur n'a pas été initialisé.");
        }
        return self::$container;
    }

    /**
     * Remplace le container
     * @param \Psr\Container\ContainerInterface $container
     * @return void
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * Initialise le container
     * @throws \RuntimeException
     * @return void
     */
    private function initializeContainer(): void
    {
        $builder = new ContainerBuilder();

        foreach ($this->configFiles as $configFile) {
            if (!file_exists($configFile)) {
                throw new \RuntimeException("Le fichier de configuration {$configFile} n'existe pas.");
            }
            $builder->addDefinitions($configFile);
        }

        self::$container = $builder->build();
    }

    public function addConfigFile(string $configFile): void
    {
        if (!in_array($configFile, $this->configFiles)) {
            $this->configFiles[] = $configFile;
        }
    }
}
