# Repository Guidelines

## Project Structure & Module Organization
This package is a reusable Symfony bundle for MongoDB migrations. Keep the source in `src/` and separate layers strictly:

- `src/Domain/` for migration domain models, value objects, and domain services.
- `src/Application/` for use cases and orchestration that depends only on `Domain`.
- `src/Infrastructure/` for Symfony, MongoDB, and persistence adapters.
- `src/DependencyInjection/` and the bundle class belong to the outer layer.
- generated migrations live in `migrations/`
- `tests/` mirrors `src/` by namespace and layer.

`Domain` must not depend on `Application`, `Infrastructure`, or Symfony. Create domain objects through factories in `Factory/`, for example `src/Domain/Migration/Factory/`.

## Build, Test, and Development Commands
Use Composer as the entry point for local work.

- `composer install` installs dependencies.
- `composer validate` checks `composer.json`.
- `composer cs-check` checks coding style without changing files.
- `composer cs-fix` formats files with PHP CS Fixer.
- `composer test` runs the PHPUnit suite.
- `vendor/bin/phpunit --filter Migration` runs a focused test subset.

Keep environment defaults in `.env.dist`. Local overrides belong in `.env.local` or `.env.test.local` and must stay uncommitted.
Bundle configuration should use the `mongodb_migrations` root key. Support `url`, `database`, `migrations_collection`, `migrations_directory`, and `migrations_namespace`.

## Coding Style & Naming Conventions
Target PHP 8.5 and Symfony 8+ components. Follow PSR-12 with 4-space indentation, one class per file, and `declare(strict_types=1);`. Constructors must be `public`; do not use private constructors. Do not leave code comments unless explicitly requested.

Use descriptive variable names; avoid placeholders such as `$value` or `$key`. Mark classes `final` whenever inheritance is not part of the design. Mark classes and promoted constructor properties `readonly` wherever immutability is possible. If a class defines `__toString()`, it must implement `\Stringable`. Do not use traits. Replace repeated or semantic string literals with class constants instead of inline values.

Use Yoda style in conditionals, for example `if (null === $migrationVersion)` and `if (false === $result->isSuccess())`. Whenever a method returns `array` with typed elements, add a `/** @return Type[] */` docblock above every declaration: interface, implementation, and concrete class. Run PHP CS Fixer through the committed `.php-cs-fixer.dist.php` config. Match namespaces to paths exactly. Prefer explicit names such as `MigrationPlan`, `MigrationExecutor`, and `MongoDBConnectionProvider`.

Only entry points may use `try/catch`, primarily Symfony console commands. Domain and Application layers must not catch exceptions. Model operational outcomes through `ErrorInterface` and `ResultInterface`, and let commands translate failures to console output and exit codes.

## Migration Conventions
Model each migration as a dedicated class with symmetric `up()` and `down()` operations. The execution contract should accept `MongoDB\Database`, for example `public function up(Database $database): void` and `public function down(Database $database): void`.

Use the database object only for persistence concerns such as collections and indexes. Keep migration intent in the domain layer; raw MongoDB calls belong in infrastructure. Prefer explicit index names in `up()` so `down()` can remove the same index, for example `'name' => 'idx_email'` and then `dropIndex('idx_email')`. Every `down()` must revert the matching `up()` change directly.

Generated migration classes should use version-style names such as `Version20260221000000`. Store them in `migrations/` and keep one migration per file.

## Console Commands
Expose these commands:

- `mongodb:migrations:generate` creates a new migration in `migrations/` using the `VersionYYYYMMDDHHMMSS` naming pattern.
- `mongodb:migrations:status` prints migration state in a console table, including driver, database name, config source, version collection, namespace, directory, current version, latest version, executed count, unavailable count, available count, and new count.
- `mongodb:migrations:migrate` runs pending migrations.
- `mongodb:migrations:execute Version20260221000000 --up` executes one migration explicitly; support `--down` for the reverse direction.
- `mongodb:migrations:rollback` reverts the last applied migration.

Database name and MongoDB URL may come from bundle configuration or env vars. Command handlers should read normalized configuration from the outer layer, not directly from domain services.

## Testing Guidelines
Use PHPUnit. Name test files with the `*Test.php` suffix and mirror the class under test, for example `tests/Application/MigrationExecutorTest.php`. In tests, domain objects may be instantiated directly with `new DomainObject(...)`. Cover new public behavior with tests before merging.

## Commit & Pull Request Guidelines
There is no project history yet, so use imperative commit messages such as `Add migration executor` or `Introduce MongoDB client factory`. Keep commits small and coherent. Pull requests should include the problem being solved, the architectural impact by layer, and verification commands.

## Security & Configuration Tips
Require `ext-mongodb` locally before running tests or commands. Expose MongoDB settings through env vars, currently `MONGODB_URL` and `MONGODB_DB`, and resolve them from the Symfony container. Default the migration versions collection to `migrations`, but allow overriding it through bundle config. Do not hardcode connection details in domain or application code.
