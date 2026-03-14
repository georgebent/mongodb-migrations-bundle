<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Model;

use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

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
