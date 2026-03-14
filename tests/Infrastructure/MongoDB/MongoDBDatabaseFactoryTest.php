<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Infrastructure\MongoDB;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\MongoDB\MongoDBDatabaseFactory;
use PHPUnit\Framework\TestCase;

final class MongoDBDatabaseFactoryTest extends TestCase
{
    public function testItRejectsMissingDatabaseUrl(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('MongoDB database URL is not configured.');

        (new MongoDBDatabaseFactory())->create(new MigrationConfiguration(
            'test_database',
            'App\\Migrations',
            '/tmp/migrations',
            'migrations',
            null,
        ));
    }
}
