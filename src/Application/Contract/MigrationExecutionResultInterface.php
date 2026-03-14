<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

use GeorgeBent\MongoDBMigrationsBundle\Domain\Contract\ResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

interface MigrationExecutionResultInterface extends ResultInterface
{
    /**
     * @return MigrationVersion[]
     */
    public function processedMigrationVersions(): array;
}
