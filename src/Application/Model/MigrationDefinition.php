<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Model;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

final readonly class MigrationDefinition
{
    public function __construct(
        private MigrationVersion $migrationVersion,
        private string $migrationClassName,
    ) {}

    public function version(): MigrationVersion
    {
        return $this->migrationVersion;
    }

    public function className(): string
    {
        return $this->migrationClassName;
    }
}
