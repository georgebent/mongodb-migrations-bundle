<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

interface MigrationFileGeneratorInterface
{
    public function generate(
        MigrationConfiguration $configuration,
        MigrationVersion $migrationVersion,
    ): GenerateMigrationResultInterface;
}
