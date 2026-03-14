# MongoDB Migrations Bundle

Symfony bundle for MongoDB migrations with explicit layer separation and console-driven workflow.

## Install

```bash
composer require georgebent/mongodb-migrations-bundle
```

Register the bundle manually only if your application does not use Symfony Flex auto-registration.

## Environment

Add MongoDB connection values to an environment-specific file such as `.env.local`:

```dotenv
MONGODB_URL=mongodb://admin:admin@172.20.0.12:27017
MONGODB_DB=api-push-notification
```

## Configuration

Create `config/packages/mongodb_migrations.yaml` in your Symfony application:

```yaml
mongodb_migrations:
    url: '%env(MONGODB_URL)%'
    database: '%env(MONGODB_DB)%'
    migrations_collection: migrations
    migrations_directory: '%kernel.project_dir%/migrations'
    migrations_namespace: 'App\Migrations'
```

`migrations_collection` defaults to `migrations`, but you can override it per application.

## Architecture

The bundle follows explicit layer separation:

- `src/Domain/` contains value objects and domain contracts.
- `src/Application/` contains use cases, result objects, and orchestration contracts.
- `src/Infrastructure/` contains MongoDB, filesystem, Symfony Console, and configuration adapters.
- `src/DependencyInjection/` contains Symfony bundle configuration and service wiring.
- `src/Migration/` contains the public migration contract used by generated migration classes.

`try/catch` is limited to entry points such as Symfony console commands. Application and Domain communicate through result objects instead of catching exceptions internally.

## Migration Example

Generated migration classes implement `GeorgeBent\MongoDBMigrationsBundle\Migration\MigrationInterface` and use the `VersionYYYYMMDDHHMMSS` naming pattern.

Example:

```php
<?php

declare(strict_types=1);

namespace App\Migrations;

use GeorgeBent\MongoDBMigrationsBundle\Migration\MigrationInterface;
use MongoDB\Database;

final class Version20260221000000 implements MigrationInterface
{
    public function up(Database $database): void
    {
        $database->selectCollection('users')->createIndex(
            [
                'email' => 1,
            ],
            [
                'unique' => true,
                'name' => 'idx_email',
            ],
        );
    }

    public function down(Database $database): void
    {
        $database->selectCollection('users')->dropIndex('idx_email');
    }
}
```

Use explicit index names in `up()` so `down()` can rollback deterministically.

## Commands

Generate a migration:

```bash
php bin/console mongodb:migrations:generate
```

Show migration status:

```bash
php bin/console mongodb:migrations:status
```

Run pending migrations:

```bash
php bin/console mongodb:migrations:migrate
```

Execute one migration explicitly:

```bash
php bin/console mongodb:migrations:execute Version20260221000000 --up
```

Rollback the latest executed migration:

```bash
php bin/console mongodb:migrations:rollback
```

## Status Output

`mongodb:migrations:status` prints a summary table with:

- database driver
- database name
- version collection name
- migrations namespace
- migrations directory
- current version
- latest version
- executed migrations count
- executed unavailable migrations count
- available migrations count
- new migrations count

## Notes

- Generated migrations are stored in `migrations/`.
- Migration class names use the `VersionYYYYMMDDHHMMSS` pattern.
- The bundle expects the `ext-mongodb` PHP extension and `mongodb/mongodb`.
