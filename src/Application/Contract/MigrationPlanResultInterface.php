<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationPlan;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Contract\ResultInterface;

interface MigrationPlanResultInterface extends ResultInterface
{
    public function migrationPlan(): ?MigrationPlan;
}
