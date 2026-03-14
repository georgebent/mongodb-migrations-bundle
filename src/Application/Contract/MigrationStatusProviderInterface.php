<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

interface MigrationStatusProviderInterface
{
    public function provide(MigrationConfiguration $configuration): MigrationStatusResultInterface;
}
