<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

use GeorgeBent\MongodbMigrationsBundle\Domain\Contract\ResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

interface MigrationExecutionResultInterface extends ResultInterface
{
    /**
     * @return MigrationVersion[]
     */
    public function processedMigrationVersions(): array;
}
