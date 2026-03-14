<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Infrastructure\Migration;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationExecutionResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationRunnerInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\VersionStorageInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\ApplicationError;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationExecutionResult;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationPlan;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\MongoDb\DatabaseFactoryInterface;
use GeorgeBent\MongodbMigrationsBundle\Migration\MigrationInterface;

final readonly class MongoDbMigrationRunner implements MigrationRunnerInterface
{
    private const string ERROR_MIGRATION_INVALID_CLASS = 'migration_invalid_class';

    private const string MESSAGE_MIGRATION_INVALID_CLASS = 'Configured migration class must implement the bundle migration interface.';

    public function __construct(
        private DatabaseFactoryInterface $databaseFactory,
        private VersionStorageInterface $versionStorage,
    ) {}

    public function run(
        MigrationConfiguration $configuration,
        MigrationPlan $migrationPlan,
    ): MigrationExecutionResultInterface {
        $database = $this->databaseFactory->create($configuration);
        $processedMigrationVersions = [];

        foreach ($migrationPlan->migrations() as $migrationDefinition) {
            $migration = new ($migrationDefinition->className())();

            if (false === ($migration instanceof MigrationInterface)) {
                return new MigrationExecutionResult(
                    false,
                    error: new ApplicationError(
                        self::ERROR_MIGRATION_INVALID_CLASS,
                        self::MESSAGE_MIGRATION_INVALID_CLASS,
                    ),
                );
            }

            if (ExecutionDirection::Up === $migrationPlan->direction()) {
                $migration->up($database);
                $this->versionStorage->markExecuted($configuration, $migrationDefinition->version());
            } else {
                $migration->down($database);
                $this->versionStorage->markRolledBack($configuration, $migrationDefinition->version());
            }

            $processedMigrationVersions[] = $migrationDefinition->version();
        }

        return new MigrationExecutionResult(true, $processedMigrationVersions);
    }
}
