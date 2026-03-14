<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Infrastructure\MongoDb;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use MongoDB\Client;
use MongoDB\Database;

final readonly class MongoDbDatabaseFactory
implements DatabaseFactoryInterface
{
    private const string ERROR_DATABASE_URL_MISSING = 'MongoDB database URL is not configured.';

    public function create(MigrationConfiguration $configuration): Database
    {
        $databaseUrl = $configuration->databaseUrl();

        if (null === $databaseUrl || '' === $databaseUrl) {
            throw new \LogicException(self::ERROR_DATABASE_URL_MISSING);
        }

        return (new Client($databaseUrl))->selectDatabase($configuration->databaseName());
    }
}
