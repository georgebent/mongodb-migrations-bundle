<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Infrastructure\Migration;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationRegistryInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Factory\MigrationDefinitionFactory;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationDefinition;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongodbMigrationsBundle\Migration\MigrationInterface;

final readonly class FileMigrationRegistry implements MigrationRegistryInterface
{
    public function __construct(
        private MigrationDefinitionFactory $migrationDefinitionFactory,
        private MigrationVersionFactory $migrationVersionFactory,
    ) {}

    public function find(MigrationConfiguration $configuration, MigrationVersion $migrationVersion): ?MigrationDefinition
    {
        foreach ($this->all($configuration) as $migrationDefinition) {
            if (true === $migrationDefinition->version()->isSame($migrationVersion)) {
                return $migrationDefinition;
            }
        }

        return null;
    }

    /**
     * @return MigrationDefinition[]
     */
    public function all(MigrationConfiguration $configuration): array
    {
        $migrationDefinitions = [];

        foreach ($this->migrationFilePaths($configuration->migrationsDirectory()) as $migrationFilePath) {
            $migrationClassName = $configuration->migrationsNamespace()
                . '\\'
                . pathinfo($migrationFilePath, PATHINFO_FILENAME);

            require_once $migrationFilePath;

            if (false === is_a($migrationClassName, MigrationInterface::class, true)) {
                continue;
            }

            $migrationDefinitions[] = $this->migrationDefinitionFactory->create(
                $this->migrationVersionFactory->fromString(
                    substr(pathinfo($migrationFilePath, PATHINFO_FILENAME), strlen(MigrationVersion::CLASS_NAME_PREFIX)),
                ),
                $migrationClassName,
            );
        }

        usort(
            $migrationDefinitions,
            static fn (MigrationDefinition $leftDefinition, MigrationDefinition $rightDefinition): int
                => $leftDefinition->version()->value() <=> $rightDefinition->version()->value(),
        );

        return $migrationDefinitions;
    }

    /**
     * @return string[]
     */
    private function migrationFilePaths(string $migrationDirectory): array
    {
        if (false === is_dir($migrationDirectory)) {
            return [];
        }

        $migrationFilePaths = glob($migrationDirectory . DIRECTORY_SEPARATOR . MigrationVersion::CLASS_NAME_PREFIX . '*.php');

        if (false === $migrationFilePaths) {
            return [];
        }

        sort($migrationFilePaths);

        return $migrationFilePaths;
    }
}
