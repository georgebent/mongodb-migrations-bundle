<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationPlan;
use GeorgeBent\MongodbMigrationsBundle\Domain\Contract\ResultInterface;

interface MigrationPlanResultInterface extends ResultInterface
{
    public function migrationPlan(): ?MigrationPlan;
}
