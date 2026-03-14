<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Infrastructure\Migration;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Factory\MigrationDefinitionFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Migration\FileMigrationRegistry;
use PHPUnit\Framework\TestCase;

final class FileMigrationRegistryTest extends TestCase
{
    public function testItLoadsOnlyValidMigrationClasses(): void
    {
        $configuration = new MigrationConfiguration(
            'test_database',
            'GeorgeBent\\MongoDBMigrationsBundle\\Tests\\Fixtures\\Migrations',
            __DIR__ . '/../../Fixtures/Migrations',
            'migrations',
            'mongodb://localhost:27017',
        );

        $migrationDefinitions = (new FileMigrationRegistry(
            new MigrationDefinitionFactory(),
            new MigrationVersionFactory(),
        ))->all($configuration);

        self::assertCount(2, $migrationDefinitions);
        self::assertSame('20260221000000', $migrationDefinitions[0]->version()->value());
        self::assertSame('20260222000000', $migrationDefinitions[1]->version()->value());
    }

    public function testItFindsMigrationByVersion(): void
    {
        $configuration = new MigrationConfiguration(
            'test_database',
            'GeorgeBent\\MongoDBMigrationsBundle\\Tests\\Fixtures\\Migrations',
            __DIR__ . '/../../Fixtures/Migrations',
            'migrations',
            'mongodb://localhost:27017',
        );
        $migrationVersion = new MigrationVersion('20260222000000');

        $migrationDefinition = (new FileMigrationRegistry(
            new MigrationDefinitionFactory(),
            new MigrationVersionFactory(),
        ))->find($configuration, $migrationVersion);

        self::assertNotNull($migrationDefinition);
        self::assertSame('20260222000000', $migrationDefinition->version()->value());
    }
}
