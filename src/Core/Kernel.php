<?php

namespace Brikphp\Core;

use Brikphp\Core\Router\Route;
use Brikphp\Core\Router\RouterInterface;
use Brikphp\FileSystem\File;
use Brikphp\FileSystem\FileSystem;
use Brikphp\FileSystem\Json\JsonComposer;
use DI\ContainerBuilder;
use FilesystemIterator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

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
        $this->loadControllersAnnotations(self::root() . '/src/Http/Controller');
        return $this->loadRoutesFiles($routesRequired)->dispatch($request);
    }
    
    /**
     * Ajoute un fichier de configuration
     * @param string $configFile
     * @return void
     */
    public function addConfigFile(string $configFile): void
    {
        if (!in_array($configFile, $this->configFiles)) {
            $this->configFiles[] = $configFile;
        }
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
            throw new RuntimeException("Le conteneur n'a pas été initialisé.");
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
                throw new RuntimeException("Le fichier de configuration {$configFile} n'existe pas.");
            }
            $builder->addDefinitions($configFile);
        }

        self::$container = $builder->build();
    }

    /**
     * Charges les différents fichiers de routes
     * @param string[] $routesRequired
     * @throws \RuntimeException
     * @return RouterInterface
     */
    private function loadRoutesFiles(array $routesRequired): RouterInterface
    {
        $routesRequired = $routesRequired ?: $this->routesFiles;
        
        foreach ($routesRequired as $file) {
            if (!file_exists($file)) {
                throw new RuntimeException("Le fichier de routes est manquant : $file");
            }

            $result = require_once $file;
            if(!$result instanceOf RouterInterface){
                throw new RuntimeException("Aucun router n'a été retourné a la fin du fichiers de route");
            }
            $router = $result;
        }
        return $router;
    }

    /**
     * Permet les annotations de Route sur les controller
     * 
     * <code>
     * 
     *  class UserController extends Controller {
     *      
     *      [Route('GET', '/user', 'user.show')]
     *      public function index()
     *      {
     *          // do something    
     *      }
     * }
     * </code>
     * @param string $path
     * @return void
     */
    private function loadControllersAnnotations(string $path)
    {
        $fileSystem = new FileSystem();
        $fileSystem->iterate($path, function (\RecursiveDirectoryIterator $iterator) {

            $composer = $this->getUserComposer();
            $userNamespace = rtrim($composer->getUserNamespace(), '\\'); // Assurez-vous que le namespace se termine correctement

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    // Appel récursif pour traiter les sous-dossiers
                    $this->loadControllersAnnotations($file->getRealPath());
                    continue;
                }

                if ($file->getExtension() === 'php') {
                    $controller = $this->resolveClassController($file, $userNamespace);
                    $this->resolveControllerAnnotation($controller);
                }
            }
        });
    }

    /**
     * Resoud la class à apeller pour les annotations de routes
     * 
     * @param \FilesystemIterator $file
     * @param string $userNamespace
     * @throws \RuntimeException
     * @return object
     */
    private function resolveClassController(FilesystemIterator $file, string $userNamespace): object
    {
        $realPath = $file->getRealPath();

        // Trouver la position de "src" et extraire après
        $srcPosition = strrpos($realPath, "src");
        if ($srcPosition === false) {
            throw new RuntimeException("Le dossier src à la racine de l'application n'existe pas");
        }
        $relativePath = substr($realPath, $srcPosition + 4); // Ignore "src/"
        $classPath = str_replace(['/', '\\'], '\\', $relativePath); // Convertir en namespace
        $className = "{$userNamespace}\\{$classPath}";

        // Supprimer l'extension .php
        $className = substr($className, 0, -4);

        // Instancier la classe dynamiquement
        if (!class_exists($className)) {
            throw new RuntimeException("La classe {$className} n'existe pas");
        }
        return new $className();
    }

    /**
     * Appelle le Router avec le bon handler selon l'annotation de Route passé au controller
     * @param object $controller
     * @return void
     */
    private function resolveControllerAnnotation(object $controller)
    {
        $router = self::container()->get(RouterInterface::class);
        $reflection = new \ReflectionClass($controller);
        foreach ($reflection->getMethods() as $method) { 
            foreach ($method->getAttributes(Route::class) as $attribute) {
                $route = $attribute->newInstance();
                $controllerMethod = $method->getName();
                $httpMethod = strtolower($route->getMethod());
                    
                call_user_func(
                    [$router, $httpMethod], 
                    $route->getName(), 
                    $route->getPath(), 
                    [$controller::class, $controllerMethod]
                );
            }
        }
    }

    private function getUserComposer()
    {
        $file = new File(self::root() . '/composer.json');
        return new JsonComposer(new FileSystem(), $file);
    }
}
