<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Service;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationPlanCalculatorInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationPlanResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationRegistryInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\VersionStorageInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Factory\MigrationPlanFactory;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\ApplicationError;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationDefinition;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationPlanResult;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;

final readonly class CalculateMigrationPlanService implements MigrationPlanCalculatorInterface
{
    private const string ERROR_MIGRATION_NOT_FOUND = 'migration_not_found';

    private const string ERROR_MIGRATION_ALREADY_EXECUTED = 'migration_already_executed';

    private const string ERROR_MIGRATION_NOT_EXECUTED = 'migration_not_executed';

    private const string ERROR_NO_EXECUTED_MIGRATIONS = 'no_executed_migrations';

    private const string ERROR_EXECUTED_MIGRATION_UNAVAILABLE = 'executed_migration_unavailable';

    private const string MESSAGE_MIGRATION_NOT_FOUND = 'Requested migration was not found.';

    private const string MESSAGE_MIGRATION_ALREADY_EXECUTED = 'Requested migration has already been executed.';

    private const string MESSAGE_MIGRATION_NOT_EXECUTED = 'Requested migration has not been executed yet.';

    private const string MESSAGE_NO_EXECUTED_MIGRATIONS = 'No executed migrations are available for rollback.';

    private const string MESSAGE_EXECUTED_MIGRATION_UNAVAILABLE = 'Last executed migration is not available in the configured directory.';

    public function __construct(
        private MigrationRegistryInterface $migrationRegistry,
        private VersionStorageInterface $versionStorage,
        private MigrationPlanFactory $migrationPlanFactory,
    ) {}

    public function forLatest(MigrationConfiguration $configuration): MigrationPlanResultInterface
    {
        $availableMigrationDefinitions = $this->migrationRegistry->all($configuration);
        $executedVersionsMap = $this->executedVersionsMap($configuration);
        $pendingMigrationDefinitions = [];

        foreach ($availableMigrationDefinitions as $migrationDefinition) {
            if (false === isset($executedVersionsMap[$migrationDefinition->version()->value()])) {
                $pendingMigrationDefinitions[] = $migrationDefinition;
            }
        }

        return new MigrationPlanResult(
            true,
            $this->migrationPlanFactory->create(ExecutionDirection::Up, $pendingMigrationDefinitions),
        );
    }

    public function forVersion(
        MigrationConfiguration $configuration,
        MigrationVersion $migrationVersion,
        ExecutionDirection $executionDirection,
    ): MigrationPlanResultInterface {
        $migrationDefinition = $this->migrationRegistry->find($configuration, $migrationVersion);

        if (null === $migrationDefinition) {
            return new MigrationPlanResult(
                false,
                error: new ApplicationError(self::ERROR_MIGRATION_NOT_FOUND, self::MESSAGE_MIGRATION_NOT_FOUND),
            );
        }

        if (ExecutionDirection::Up === $executionDirection && true === $this->versionStorage->has($configuration, $migrationVersion)) {
            return new MigrationPlanResult(
                false,
                error: new ApplicationError(
                    self::ERROR_MIGRATION_ALREADY_EXECUTED,
                    self::MESSAGE_MIGRATION_ALREADY_EXECUTED,
                ),
            );
        }

        if (ExecutionDirection::Down === $executionDirection && false === $this->versionStorage->has($configuration, $migrationVersion)) {
            return new MigrationPlanResult(
                false,
                error: new ApplicationError(self::ERROR_MIGRATION_NOT_EXECUTED, self::MESSAGE_MIGRATION_NOT_EXECUTED),
            );
        }

        return new MigrationPlanResult(
            true,
            $this->migrationPlanFactory->create($executionDirection, [$migrationDefinition]),
        );
    }

    public function forRollback(MigrationConfiguration $configuration): MigrationPlanResultInterface
    {
        $latestExecutedVersion = $this->versionStorage->latestExecuted($configuration);

        if (null === $latestExecutedVersion) {
            return new MigrationPlanResult(
                false,
                error: new ApplicationError(self::ERROR_NO_EXECUTED_MIGRATIONS, self::MESSAGE_NO_EXECUTED_MIGRATIONS),
            );
        }

        $migrationDefinition = $this->migrationRegistry->find($configuration, $latestExecutedVersion);

        if (null === $migrationDefinition) {
            return new MigrationPlanResult(
                false,
                error: new ApplicationError(
                    self::ERROR_EXECUTED_MIGRATION_UNAVAILABLE,
                    self::MESSAGE_EXECUTED_MIGRATION_UNAVAILABLE,
                ),
            );
        }

        return new MigrationPlanResult(
            true,
            $this->migrationPlanFactory->create(ExecutionDirection::Down, [$migrationDefinition]),
        );
    }

    /**
     * @return array<string, true>
     */
    private function executedVersionsMap(MigrationConfiguration $configuration): array
    {
        $executedVersionsMap = [];

        foreach ($this->versionStorage->all($configuration) as $executedMigrationVersion) {
            $executedVersionsMap[$executedMigrationVersion->value()] = true;
        }

        return $executedVersionsMap;
    }
}
