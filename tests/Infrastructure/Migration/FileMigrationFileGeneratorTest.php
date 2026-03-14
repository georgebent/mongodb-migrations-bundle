<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Infrastructure\Migration;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Migration\FileMigrationFileGenerator;
use PHPUnit\Framework\TestCase;

final class FileMigrationFileGeneratorTest extends TestCase
{
    private string $temporaryDirectory;

    protected function setUp(): void
    {
        $this->temporaryDirectory = sys_get_temp_dir() . '/mongodb-migrations-bundle-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        if (false === is_dir($this->temporaryDirectory)) {
            return;
        }

        foreach (glob($this->temporaryDirectory . '/*') ?: [] as $filePath) {
            unlink($filePath);
        }

        rmdir($this->temporaryDirectory);
    }

    public function testItGeneratesMigrationFile(): void
    {
        $configuration = new MigrationConfiguration(
            'test_database',
            'App\\Migrations',
            $this->temporaryDirectory,
            'migrations',
            'mongodb://localhost:27017',
        );
        $migrationVersion = new MigrationVersion('20260221000000');

        $generateMigrationResult = (new FileMigrationFileGenerator())->generate($configuration, $migrationVersion);

        self::assertTrue($generateMigrationResult->isSuccess());
        self::assertFileExists($this->temporaryDirectory . '/Version20260221000000.php');
        self::assertStringContainsString(
            'final class Version20260221000000 implements MigrationInterface',
            (string) file_get_contents($this->temporaryDirectory . '/Version20260221000000.php'),
        );
    }

    public function testItRejectsDuplicateMigrationFile(): void
    {
        mkdir($this->temporaryDirectory, 0777, true);
        file_put_contents($this->temporaryDirectory . '/Version20260221000000.php', '<?php');

        $configuration = new MigrationConfiguration(
            'test_database',
            'App\\Migrations',
            $this->temporaryDirectory,
            'migrations',
            'mongodb://localhost:27017',
        );
        $migrationVersion = new MigrationVersion('20260221000000');

        $generateMigrationResult = (new FileMigrationFileGenerator())->generate($configuration, $migrationVersion);

        self::assertFalse($generateMigrationResult->isSuccess());
        self::assertSame('migration_already_exists', $generateMigrationResult->error()?->identifier());
    }
}
