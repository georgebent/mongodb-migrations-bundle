<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

interface MigrationConfigurationProviderInterface
{
    public function provide(): MigrationConfigurationResultInterface;
}
