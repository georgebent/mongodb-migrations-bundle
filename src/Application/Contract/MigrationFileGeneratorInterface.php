<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

interface MigrationFileGeneratorInterface
{
    public function generate(
        MigrationConfiguration $configuration,
        MigrationVersion $migrationVersion,
    ): GenerateMigrationResultInterface;
}
