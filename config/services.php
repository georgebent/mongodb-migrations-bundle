<?php

declare(strict_types=1);

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\ExecuteMigrationInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\GenerateMigrationInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrateInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationFileGeneratorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationPlanCalculatorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationRegistryInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationRunnerInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationStatusProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\RollbackMigrationInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\VersionStorageInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Factory\MigrationDefinitionFactory;
use GeorgeBent\MongoDBMigrationsBundle\Application\Factory\MigrationPlanFactory;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\CalculateMigrationPlanService;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\ExecuteMigrationService;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\GenerateMigrationService;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\MigrateService;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\ProvideMigrationStatusService;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\RollbackMigrationService;
use GeorgeBent\MongoDBMigrationsBundle\DependencyInjection\MongoDBMigrationsExtension;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationStatusFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationStatusNumbersFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Configuration\ProvideMigrationConfigurationService;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command\ExecuteMigrationCommand;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command\GenerateMigrationCommand;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command\MigrateCommand;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command\RollbackMigrationCommand;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command\StatusCommand;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Migration\FileMigrationFileGenerator;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Migration\FileMigrationRegistry;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Migration\MongoDBMigrationRunner;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\MongoDB\DatabaseFactoryInterface;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\MongoDB\MongoDBDatabaseFactory;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\MongoDB\MongoDBVersionStorage;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services->set(MigrationVersionFactory::class);
    $services->set(MigrationStatusFactory::class);
    $services->set(MigrationStatusNumbersFactory::class);
    $services->set(MigrationDefinitionFactory::class);
    $services->set(MigrationPlanFactory::class);

    $services->set(ProvideMigrationConfigurationService::class)
        ->args([
            '$databaseName' => param(MongoDBMigrationsExtension::PARAMETER_DATABASE),
            '$migrationsNamespace' => param(MongoDBMigrationsExtension::PARAMETER_MIGRATIONS_NAMESPACE),
            '$migrationsDirectory' => param(MongoDBMigrationsExtension::PARAMETER_MIGRATIONS_DIRECTORY),
            '$versionCollectionName' => param(MongoDBMigrationsExtension::PARAMETER_MIGRATIONS_COLLECTION),
            '$databaseUrl' => param(MongoDBMigrationsExtension::PARAMETER_URL),
            '$configurationSource' => null,
        ]);
    $services->alias(MigrationConfigurationProviderInterface::class, ProvideMigrationConfigurationService::class);

    $services->set(FileMigrationFileGenerator::class);
    $services->alias(MigrationFileGeneratorInterface::class, FileMigrationFileGenerator::class);

    $services->set(FileMigrationRegistry::class);
    $services->alias(MigrationRegistryInterface::class, FileMigrationRegistry::class);

    $services->set(MongoDBDatabaseFactory::class);
    $services->alias(DatabaseFactoryInterface::class, MongoDBDatabaseFactory::class);
    $services->set(MongoDBVersionStorage::class);
    $services->alias(VersionStorageInterface::class, MongoDBVersionStorage::class);
    $services->set(MongoDBMigrationRunner::class);
    $services->alias(MigrationRunnerInterface::class, MongoDBMigrationRunner::class);

    $services->set(CalculateMigrationPlanService::class);
    $services->alias(MigrationPlanCalculatorInterface::class, CalculateMigrationPlanService::class);

    $services->set(ProvideMigrationStatusService::class);
    $services->alias(MigrationStatusProviderInterface::class, ProvideMigrationStatusService::class);

    $services->set(GenerateMigrationService::class);
    $services->alias(GenerateMigrationInterface::class, GenerateMigrationService::class);
    $services->set(MigrateService::class);
    $services->alias(MigrateInterface::class, MigrateService::class);
    $services->set(ExecuteMigrationService::class);
    $services->alias(ExecuteMigrationInterface::class, ExecuteMigrationService::class);
    $services->set(RollbackMigrationService::class);
    $services->alias(RollbackMigrationInterface::class, RollbackMigrationService::class);

    $services->set(GenerateMigrationCommand::class)
        ->arg('$migrationConfigurationProvider', service(MigrationConfigurationProviderInterface::class))
        ->arg('$generateMigration', service(GenerateMigrationInterface::class))
        ->tag('console.command');
    $services->set(StatusCommand::class)
        ->arg('$migrationConfigurationProvider', service(MigrationConfigurationProviderInterface::class))
        ->arg('$migrationStatusProvider', service(MigrationStatusProviderInterface::class))
        ->tag('console.command');
    $services->set(MigrateCommand::class)
        ->arg('$migrationConfigurationProvider', service(MigrationConfigurationProviderInterface::class))
        ->arg('$migrate', service(MigrateInterface::class))
        ->tag('console.command');
    $services->set(ExecuteMigrationCommand::class)
        ->arg('$migrationConfigurationProvider', service(MigrationConfigurationProviderInterface::class))
        ->arg('$executeMigration', service(ExecuteMigrationInterface::class))
        ->arg('$migrationVersionFactory', service(MigrationVersionFactory::class))
        ->tag('console.command');
    $services->set(RollbackMigrationCommand::class)
        ->arg('$migrationConfigurationProvider', service(MigrationConfigurationProviderInterface::class))
        ->arg('$rollbackMigration', service(RollbackMigrationInterface::class))
        ->tag('console.command');
};
