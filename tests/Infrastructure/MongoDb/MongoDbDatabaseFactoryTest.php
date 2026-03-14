<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Tests\Infrastructure\MongoDb;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\MongoDb\MongoDbDatabaseFactory;
use PHPUnit\Framework\TestCase;

final class MongoDbDatabaseFactoryTest extends TestCase
{
    public function testItRejectsMissingDatabaseUrl(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('MongoDB database URL is not configured.');

        (new MongoDbDatabaseFactory())->create(new MigrationConfiguration(
            'test_database',
            'App\\Migrations',
            '/tmp/migrations',
            'migrations',
            null,
        ));
    }
}
