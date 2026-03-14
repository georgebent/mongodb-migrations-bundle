<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

interface MigrateInterface
{
    public function migrate(MigrationConfiguration $configuration): MigrationExecutionResultInterface;
}
