# Symfony App Example

## Install Bundle

Add the bundle to your Symfony application:

```bash
composer require georgebent/mongodb-migrations-bundle
```

Register the bundle if your application does not use Symfony Flex auto-registration.

## Environment

Add MongoDB connection values to `.env.local` or another environment-specific file:

```dotenv
MONGODB_URL=mongodb://admin:admin@172.20.0.12:27017
MONGODB_DB=api-push-notification
```

## Package Config

Create `config/packages/mongodb_migrations.yaml`:

```yaml
mongodb_migrations:
    url: '%env(MONGODB_URL)%'
    database: '%env(MONGODB_DB)%'
    migrations_collection: migrations
    migrations_directory: '%kernel.project_dir%/migrations'
    migrations_namespace: 'App\Migrations'
```

`migrations_collection` defaults to `migrations`, but you can override it per application.

## Commands

Generate the first migration:

```bash
php bin/console mongodb:migrations:generate
```

Check status:

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
