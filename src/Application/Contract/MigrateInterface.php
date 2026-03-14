<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

interface MigrateInterface
{
    public function migrate(MigrationConfiguration $configuration): MigrationExecutionResultInterface;
}
