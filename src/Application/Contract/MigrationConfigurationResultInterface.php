<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

use GeorgeBent\MongodbMigrationsBundle\Domain\Contract\ResultInterface;

interface MigrationConfigurationResultInterface extends ResultInterface
{
    public function migrationConfiguration(): ?MigrationConfiguration;
}
