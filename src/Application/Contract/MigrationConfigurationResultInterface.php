<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Contract\ResultInterface;

interface MigrationConfigurationResultInterface extends ResultInterface
{
    public function migrationConfiguration(): ?MigrationConfiguration;
}
