<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Contract\ResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationStatus;

interface MigrationStatusResultInterface extends ResultInterface
{
    public function migrationStatus(): ?MigrationStatus;
}
