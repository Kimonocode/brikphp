<?php

namespace Brikphp\Tests\Core\Env;

use Brikphp\Core\Env\Env;
use Brikphp\Core\Kernel;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class EnvTest extends TestCase
{
    private Dotenv $dotenv;

    protected function setUp(): void
    {
        // Initialisation manuelle du conteneur
        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions([]); // Ajouter des définitions nécessaires ici
        $container = $builder->build();
        Kernel::setContainer($container);
    }

    /**
     * Teste la récupération d'une variable d'environnement existante.
     */
    public function testGetEnvVariable()
    {
        putenv('DB_HOST=127.0.0.1');

        $dbHost = Env::get('DB_HOST');
        $this->assertEquals('127.0.0.1', $dbHost);
    }

    /**
     * Teste la récupération d'une variable avec une valeur par défaut.
     */
    public function testGetEnvVariableWithDefault()
    {
        $value = Env::get('NON_EXISTING_KEY', 'default_value');
        $this->assertEquals('default_value', $value);
    }

    /**
     * Teste la récupération d'une clé inexistante.
     */
    public function testGetNonExistingKey()
    {
        $this->expectException(\DI\NotFoundException::class);
        Kernel::container()->get('non_existing_key');
    }
    
    /**
     * Teste le fallback sur le conteneur DI.
     */
    public function testFallbackToContainer()
    {
        // Mock du conteneur DI
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('CONTAINER_KEY')->willReturn(true);
        $container->method('get')->with('CONTAINER_KEY')->willReturn('CONTAINER_VALUE');

        // Injection du conteneur dans Kernel
        Kernel::setContainer($container);

        $value = Env::get('CONTAINER_KEY');
        $this->assertEquals('CONTAINER_VALUE', $value);
    }

    /**
     * Teste le comportement lorsque le conteneur ne trouve pas la clé.
     */
    public function testContainerKeyNotFound()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('MISSING_KEY')->willReturn(false);

        Kernel::setContainer($container);

        $value = Env::get('MISSING_KEY', 'fallback');
        $this->assertEquals('fallback', $value);
    }

    protected function tearDown(): void
    {
        // Nettoyer toutes les variables d'environnement
        putenv('DB_HOST');
    }
}