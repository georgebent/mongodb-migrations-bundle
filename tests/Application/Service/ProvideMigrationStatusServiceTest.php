<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Application\Service;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationRegistryInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\VersionStorageInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Factory\MigrationDefinitionFactory;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\ProvideMigrationStatusService;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationStatusFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationStatusNumbersFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use PHPUnit\Framework\TestCase;

final class ProvideMigrationStatusServiceTest extends TestCase
{
    public function testItBuildsStatusNumbersFromAvailableAndExecutedMigrations(): void
    {
        $migrationRegistry = $this->createMock(MigrationRegistryInterface::class);
        $versionStorage = $this->createMock(VersionStorageInterface::class);
        $migrationDefinitionFactory = new MigrationDefinitionFactory();
        $configuration = $this->migrationConfiguration();
        $firstMigrationVersion = new MigrationVersion('20260221000000');
        $secondMigrationVersion = new MigrationVersion('20260222000000');
        $missingMigrationVersion = new MigrationVersion('20260220000000');

        $migrationRegistry->expects(self::once())
            ->method('all')
            ->with($configuration)
            ->willReturn([
                $migrationDefinitionFactory->create($firstMigrationVersion, 'App\\Migrations\\Version20260221000000'),
                $migrationDefinitionFactory->create($secondMigrationVersion, 'App\\Migrations\\Version20260222000000'),
            ]);

        $versionStorage->expects(self::once())
            ->method('all')
            ->with($configuration)
            ->willReturn([$firstMigrationVersion, $missingMigrationVersion]);

        $versionStorage->expects(self::once())
            ->method('current')
            ->with($configuration)
            ->willReturn($firstMigrationVersion);

        $migrationStatusResult = $this->service($migrationRegistry, $versionStorage)->provide($configuration);

        self::assertTrue($migrationStatusResult->isSuccess());
        self::assertNotNull($migrationStatusResult->migrationStatus());
        self::assertSame('MongoDB', $migrationStatusResult->migrationStatus()->databaseDriver());
        self::assertSame(2, $migrationStatusResult->migrationStatus()->numbers()->executedMigrations());
        self::assertSame(1, $migrationStatusResult->migrationStatus()->numbers()->executedUnavailableMigrations());
        self::assertSame(2, $migrationStatusResult->migrationStatus()->numbers()->availableMigrations());
        self::assertSame(1, $migrationStatusResult->migrationStatus()->numbers()->newMigrations());
        self::assertSame($secondMigrationVersion->value(), $migrationStatusResult->migrationStatus()->latestVersion()?->value());
    }

    private function service(
        MigrationRegistryInterface $migrationRegistry,
        VersionStorageInterface $versionStorage,
    ): ProvideMigrationStatusService {
        return new ProvideMigrationStatusService(
            $migrationRegistry,
            $versionStorage,
            new MigrationStatusFactory(),
            new MigrationStatusNumbersFactory(),
        );
    }

    private function migrationConfiguration(): MigrationConfiguration
    {
        return new MigrationConfiguration(
            'test_database',
            'App\\Migrations',
            '/tmp/migrations',
            'migrations',
            'mongodb://localhost:27017',
        );
    }
}
