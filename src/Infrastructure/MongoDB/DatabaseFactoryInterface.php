<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Infrastructure\MongoDB;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use MongoDB\Database;

interface DatabaseFactoryInterface
{
    public function create(MigrationConfiguration $configuration): Database;
}
