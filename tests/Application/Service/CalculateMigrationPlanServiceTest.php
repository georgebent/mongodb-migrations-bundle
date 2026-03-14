<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Tests\Application\Service;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationRegistryInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\VersionStorageInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Factory\MigrationDefinitionFactory;
use GeorgeBent\MongodbMigrationsBundle\Application\Factory\MigrationPlanFactory;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\CalculateMigrationPlanService;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;
use PHPUnit\Framework\TestCase;

final class CalculateMigrationPlanServiceTest extends TestCase
{
    public function testForLatestReturnsOnlyPendingMigrations(): void
    {
        $migrationRegistry = $this->createMock(MigrationRegistryInterface::class);
        $versionStorage = $this->createMock(VersionStorageInterface::class);
        $migrationDefinitionFactory = new MigrationDefinitionFactory();
        $configuration = $this->migrationConfiguration();
        $firstMigrationVersion = new MigrationVersion('20260221000000');
        $secondMigrationVersion = new MigrationVersion('20260222000000');

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
            ->willReturn([$firstMigrationVersion]);

        $migrationPlanResult = $this->service($migrationRegistry, $versionStorage)->forLatest($configuration);

        self::assertTrue($migrationPlanResult->isSuccess());
        self::assertNotNull($migrationPlanResult->migrationPlan());
        self::assertSame(ExecutionDirection::Up, $migrationPlanResult->migrationPlan()->direction());
        self::assertCount(1, $migrationPlanResult->migrationPlan()->migrations());
        self::assertSame($secondMigrationVersion->value(), $migrationPlanResult->migrationPlan()->migrations()[0]->version()->value());
    }

    public function testForVersionReturnsErrorWhenMigrationDoesNotExist(): void
    {
        $migrationRegistry = $this->createMock(MigrationRegistryInterface::class);
        $versionStorage = $this->createMock(VersionStorageInterface::class);
        $configuration = $this->migrationConfiguration();
        $migrationVersion = new MigrationVersion('20260221000000');

        $migrationRegistry->expects(self::once())
            ->method('find')
            ->with($configuration, $migrationVersion)
            ->willReturn(null);
        $versionStorage->expects(self::never())->method('has');

        $migrationPlanResult = $this->service($migrationRegistry, $versionStorage)->forVersion(
            $configuration,
            $migrationVersion,
            ExecutionDirection::Up,
        );

        self::assertFalse($migrationPlanResult->isSuccess());
        self::assertSame('migration_not_found', $migrationPlanResult->error()?->identifier());
    }

    public function testForVersionRejectsAlreadyExecutedUpMigration(): void
    {
        $migrationRegistry = $this->createMock(MigrationRegistryInterface::class);
        $versionStorage = $this->createMock(VersionStorageInterface::class);
        $migrationDefinitionFactory = new MigrationDefinitionFactory();
        $configuration = $this->migrationConfiguration();
        $migrationVersion = new MigrationVersion('20260221000000');
        $migrationDefinition = $migrationDefinitionFactory->create($migrationVersion, 'App\\Migrations\\Version20260221000000');

        $migrationRegistry->expects(self::once())
            ->method('find')
            ->with($configuration, $migrationVersion)
            ->willReturn($migrationDefinition);

        $versionStorage->expects(self::once())
            ->method('has')
            ->with($configuration, $migrationVersion)
            ->willReturn(true);

        $migrationPlanResult = $this->service($migrationRegistry, $versionStorage)->forVersion(
            $configuration,
            $migrationVersion,
            ExecutionDirection::Up,
        );

        self::assertFalse($migrationPlanResult->isSuccess());
        self::assertSame('migration_already_executed', $migrationPlanResult->error()?->identifier());
    }

    public function testForRollbackReturnsErrorWhenNothingWasExecuted(): void
    {
        $migrationRegistry = $this->createMock(MigrationRegistryInterface::class);
        $versionStorage = $this->createMock(VersionStorageInterface::class);
        $configuration = $this->migrationConfiguration();

        $versionStorage->expects(self::once())
            ->method('latestExecuted')
            ->with($configuration)
            ->willReturn(null);
        $migrationRegistry->expects(self::never())->method('find');

        $migrationPlanResult = $this->service($migrationRegistry, $versionStorage)->forRollback($configuration);

        self::assertFalse($migrationPlanResult->isSuccess());
        self::assertSame('no_executed_migrations', $migrationPlanResult->error()?->identifier());
    }

    public function testForRollbackBuildsDownPlanForLastExecutedMigration(): void
    {
        $migrationRegistry = $this->createMock(MigrationRegistryInterface::class);
        $versionStorage = $this->createMock(VersionStorageInterface::class);
        $migrationDefinitionFactory = new MigrationDefinitionFactory();
        $configuration = $this->migrationConfiguration();
        $migrationVersion = new MigrationVersion('20260221000000');
        $migrationDefinition = $migrationDefinitionFactory->create($migrationVersion, 'App\\Migrations\\Version20260221000000');

        $versionStorage->expects(self::once())
            ->method('latestExecuted')
            ->with($configuration)
            ->willReturn($migrationVersion);

        $migrationRegistry->expects(self::once())
            ->method('find')
            ->with($configuration, $migrationVersion)
            ->willReturn($migrationDefinition);

        $migrationPlanResult = $this->service($migrationRegistry, $versionStorage)->forRollback($configuration);

        self::assertTrue($migrationPlanResult->isSuccess());
        self::assertNotNull($migrationPlanResult->migrationPlan());
        self::assertSame(ExecutionDirection::Down, $migrationPlanResult->migrationPlan()->direction());
        self::assertCount(1, $migrationPlanResult->migrationPlan()->migrations());
    }

    private function service(
        MigrationRegistryInterface $migrationRegistry,
        VersionStorageInterface $versionStorage,
    ): CalculateMigrationPlanService {
        return new CalculateMigrationPlanService($migrationRegistry, $versionStorage, new MigrationPlanFactory());
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
