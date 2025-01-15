<?php

namespace Brikphp\Core;

use Brikphp\Core\Router\RouterInterface;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Kernel
{   
    /**
     * @var ContainerInterface|null
     */
    private static ?ContainerInterface $container = null;

    /**
     * @var array
     */
    private array $files;

    public function __construct()
    {
        $this->files = [
            self::root() . '/start/routes.php',
        ];
        $this->initializeContainer();
    }

    /**
     * Lance l'application avec les différents fichiers requis.
     *
     * @param ServerRequestInterface $request
     * @param array $filesRequired
     * @throws \RuntimeException
     * @return ResponseInterface
     */
    public function run(ServerRequestInterface $request, array $filesRequired = []): ResponseInterface
    {
        $filesRequired = $filesRequired ?: $this->files;

        $router = null;

        foreach ($filesRequired as $file) {
            if (!file_exists($file)) {
                throw new \RuntimeException("Le fichier $file est manquant.");
            }

            // Inclure le fichier et capturer le résultat
            $result = require_once $file;

            // Si le fichier retourne un routeur, l'utiliser
            if ($result instanceof RouterInterface) {
                $router = $result;
            }
        }

        if (!$router) {
            throw new \RuntimeException("Aucun routeur n'a été initialisé après le chargement des fichiers requis.");
        }

        return $router->dispatch($request);
    }

    /**
     * Retourne le chemin du dossier de l'application.
     *
     * @return string
     */
    public static function root(): string
    {
        return dirname($_SERVER['SCRIPT_FILENAME'], 2);
    }

    /**
     * Retourne une instance du conteneur d'injection de dépendances.
     *
     * @return ContainerInterface
     */
    public static function container(): ContainerInterface
    {
        if (!self::$container) {
            throw new \RuntimeException("Le conteneur n'a pas été initialisé. Assurez-vous d'initialiser Kernel avant d'utiliser Kernel::container().");
        }
        return self::$container;
    }

    /**
     * Remplace le conteneur.
     *
     * @param ContainerInterface $container
     * @return void
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * Initialise le conteneur d'injection de dépendances.
     *
     * @return void
     */
    private function initializeContainer(): void
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions(__DIR__ . '/config.php');
        $builder->addDefinitions(self::root() . '/config.php');

        self::$container = $builder->build();
    }
}
