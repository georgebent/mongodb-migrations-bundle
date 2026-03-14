<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Service;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationExecutionResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationPlanCalculatorInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationRunnerInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\RollbackMigrationInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationExecutionResult;

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
