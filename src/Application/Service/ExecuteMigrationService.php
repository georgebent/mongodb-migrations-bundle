<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Service;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\ExecuteMigrationInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationExecutionResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationPlanCalculatorInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationRunnerInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationExecutionResult;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

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
