<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Service;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\ExecuteMigrationInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationExecutionResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationPlanCalculatorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationRunnerInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationExecutionResult;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

final readonly class ExecuteMigrationService implements ExecuteMigrationInterface
{
    public function __construct(
        private MigrationPlanCalculatorInterface $migrationPlanCalculator,
        private MigrationRunnerInterface $migrationRunner,
    ) {}

    public function execute(
        MigrationConfiguration $configuration,
        MigrationVersion $migrationVersion,
        ExecutionDirection $executionDirection,
    ): MigrationExecutionResultInterface {
        $migrationPlanResult = $this->migrationPlanCalculator->forVersion(
            $configuration,
            $migrationVersion,
            $executionDirection,
        );

        if (false === $migrationPlanResult->isSuccess() || null === $migrationPlanResult->migrationPlan()) {
            return new MigrationExecutionResult(false, error: $migrationPlanResult->error());
        }

        return $this->migrationRunner->run($configuration, $migrationPlanResult->migrationPlan());
    }
}
