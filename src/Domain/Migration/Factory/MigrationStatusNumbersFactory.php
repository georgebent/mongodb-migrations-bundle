<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationStatusNumbers;

final readonly class MigrationStatusNumbersFactory
{
    public function create(
        int $executedMigrationsCount,
        int $executedUnavailableMigrationsCount,
        int $availableMigrationsCount,
        int $newMigrationsCount,
    ): MigrationStatusNumbers {
        return new MigrationStatusNumbers(
            $executedMigrationsCount,
            $executedUnavailableMigrationsCount,
            $availableMigrationsCount,
            $newMigrationsCount,
        );
    }
}
