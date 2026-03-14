<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Infrastructure\MongoDb;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use MongoDB\Database;

interface DatabaseFactoryInterface
{
    public function create(MigrationConfiguration $configuration): Database;
}
