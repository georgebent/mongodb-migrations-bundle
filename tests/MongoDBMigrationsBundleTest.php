<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests;

use GeorgeBent\MongoDBMigrationsBundle\DependencyInjection\MongoDBMigrationsExtension;
use GeorgeBent\MongoDBMigrationsBundle\MongoDBMigrationsBundle;
use PHPUnit\Framework\TestCase;

final class MongoDBMigrationsBundleTest extends TestCase
{
    public function testItReturnsMongoDBMigrationsExtension(): void
    {
        $bundle = new MongoDBMigrationsBundle();
        $containerExtension = $bundle->getContainerExtension();

        self::assertInstanceOf(MongoDBMigrationsExtension::class, $containerExtension);
        self::assertSame('mongodb_migrations', $containerExtension->getAlias());
    }
}
