<?php
/**
 * Bootstraper for PHPUnit tests.
 */
error_reporting(E_ALL | E_STRICT);

require_once __DIR__ . '/../vendor/autoload.php';

use Brikphp\Core\Kernel;
use DI\ContainerBuilder;

$builder = new ContainerBuilder();
$builder->addDefinitions([]); // Ajouter les définitions nécessaires ici
$container = $builder->build();

Kernel::setContainer($container);