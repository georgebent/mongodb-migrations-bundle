<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Domain\Migration;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use PHPUnit\Framework\TestCase;

final class MigrationVersionTest extends TestCase
{
    public function testItExposesClassNameAndFormattedDate(): void
    {
        $migrationVersion = new MigrationVersion('20260221000000');

        self::assertSame('Version20260221000000', $migrationVersion->className());
        self::assertSame('2026-02-21 00:00:00', $migrationVersion->formatted());
        self::assertSame('20260221000000', (string) $migrationVersion);
    }

    public function testItComparesVersions(): void
    {
        $firstMigrationVersion = new MigrationVersion('20260221000000');
        $secondMigrationVersion = new MigrationVersion('20260222000000');

        self::assertTrue($firstMigrationVersion->isSame(new MigrationVersion('20260221000000')));
        self::assertTrue($secondMigrationVersion->isGreaterThan($firstMigrationVersion));
        self::assertTrue($firstMigrationVersion->isLessThan($secondMigrationVersion));
    }

    public function testItRejectsInvalidVersionFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new MigrationVersion('invalid-version');
    }
}
