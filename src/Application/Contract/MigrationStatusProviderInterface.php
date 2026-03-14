<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

interface MigrationStatusProviderInterface
{
    public function provide(MigrationConfiguration $configuration): MigrationStatusResultInterface;
}
