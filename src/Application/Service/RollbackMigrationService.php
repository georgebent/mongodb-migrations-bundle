<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Service;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationExecutionResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationPlanCalculatorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationRunnerInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\RollbackMigrationInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationExecutionResult;

final readonly class RollbackMigrationService implements RollbackMigrationInterface
{
    public function __construct(
        private MigrationPlanCalculatorInterface $migrationPlanCalculator,
        private MigrationRunnerInterface $migrationRunner,
    ) {}

    public function rollback(MigrationConfiguration $configuration): MigrationExecutionResultInterface
    {
        $migrationPlanResult = $this->migrationPlanCalculator->forRollback($configuration);

        if (false === $migrationPlanResult->isSuccess() || null === $migrationPlanResult->migrationPlan()) {
            return new MigrationExecutionResult(false, error: $migrationPlanResult->error());
        }

        return $this->migrationRunner->run($configuration, $migrationPlanResult->migrationPlan());
    }
}
