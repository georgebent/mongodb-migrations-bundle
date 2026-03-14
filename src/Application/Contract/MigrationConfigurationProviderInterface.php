<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

interface MigrationConfigurationProviderInterface
{
    public function provide(): MigrationConfigurationResultInterface;
}
