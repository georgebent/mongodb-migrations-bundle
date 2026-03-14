<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Model;

use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\ExecutionDirection;

final readonly class MigrationPlan
{
    /**
     * @param MigrationDefinition[] $migrationDefinitions
     */
    public function __construct(
        private ExecutionDirection $executionDirection,
        private array $migrationDefinitions,
    ) {}

    public function direction(): ExecutionDirection
    {
        return $this->executionDirection;
    }

    /**
     * @return MigrationDefinition[]
     */
    public function migrations(): array
    {
        return $this->migrationDefinitions;
    }

    public function isEmpty(): bool
    {
        return $this->migrationDefinitions === [];
    }
}
