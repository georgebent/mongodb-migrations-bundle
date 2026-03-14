<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

use GeorgeBent\MongodbMigrationsBundle\Domain\Contract\ResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationStatus;

interface MigrationStatusResultInterface extends ResultInterface
{
    public function migrationStatus(): ?MigrationStatus;
}
