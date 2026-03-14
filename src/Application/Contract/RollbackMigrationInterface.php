<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

interface RollbackMigrationInterface
{
    public function rollback(MigrationConfiguration $configuration): MigrationExecutionResultInterface;
}
