<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

interface RollbackMigrationInterface
{
    public function rollback(MigrationConfiguration $configuration): MigrationExecutionResultInterface;
}
