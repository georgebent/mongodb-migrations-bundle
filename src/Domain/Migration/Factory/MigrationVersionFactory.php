<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

final readonly class MigrationVersionFactory
{
    public function fromString(string $versionString): MigrationVersion
    {
        return new MigrationVersion($versionString);
    }

    public function fromDateTime(\DateTimeInterface $migrationDateTime): MigrationVersion
    {
        return new MigrationVersion($migrationDateTime->format(MigrationVersion::FORMAT));
    }
}
