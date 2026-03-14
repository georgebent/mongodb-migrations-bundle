<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Tests\Infrastructure\Configuration;

use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Configuration\ProvideMigrationConfigurationService;
use PHPUnit\Framework\TestCase;

final class ProvideMigrationConfigurationServiceTest extends TestCase
{
    public function testItBuildsSuccessfulConfigurationResult(): void
    {
        $configurationResult = (new ProvideMigrationConfigurationService(
            'test_database',
            'App\\Migrations',
            '/tmp/migrations',
            'migrations',
            'mongodb://localhost:27017',
            null,
        ))->provide();

        self::assertTrue($configurationResult->isSuccess());
        self::assertNotNull($configurationResult->migrationConfiguration());
        self::assertSame('test_database', $configurationResult->migrationConfiguration()?->databaseName());
        self::assertSame('migrations', $configurationResult->migrationConfiguration()?->versionCollectionName());
    }
}
