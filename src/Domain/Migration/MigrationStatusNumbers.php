<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Domain\Migration;

final readonly class MigrationStatusNumbers
{
    public function __construct(
        private int $executedMigrationsCount,
        private int $executedUnavailableMigrationsCount,
        private int $availableMigrationsCount,
        private int $newMigrationsCount,
    ) {}

    public function executedMigrations(): int
    {
        return $this->executedMigrationsCount;
    }

    public function executedUnavailableMigrations(): int
    {
        return $this->executedUnavailableMigrationsCount;
    }

    public function availableMigrations(): int
    {
        return $this->availableMigrationsCount;
    }

    public function newMigrations(): int
    {
        return $this->newMigrationsCount;
    }
}
