<?php

namespace Brikphp\Tests\Core;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Brikphp\Core\Kernel;

class KernelTest extends TestCase
{
    private Kernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = new Kernel();
    }

    public function testRunWithMissingFile()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Le fichier /path/to/missing/file.php est manquant.");

        $this->kernel->run($this->createMock(ServerRequestInterface::class), ['/path/to/missing/file.php']);
    }

    public function testRunWithInvalidRouter()
    {
        $invalidFile = __DIR__ . '/invalidRouter.php';
        file_put_contents($invalidFile, '<?php return null;');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Aucun routeur n'a été initialisé après le chargement des fichiers requis.");

        $this->kernel->run($this->createMock(ServerRequestInterface::class), [$invalidFile]);

        unlink($invalidFile);
    }
}
