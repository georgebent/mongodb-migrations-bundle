<?php

declare(strict_types=1);

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\ExecuteMigrationInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\GenerateMigrationInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrateInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationFileGeneratorInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationPlanCalculatorInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationRegistryInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationRunnerInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationStatusProviderInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\RollbackMigrationInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\VersionStorageInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Factory\MigrationDefinitionFactory;
use GeorgeBent\MongodbMigrationsBundle\Application\Factory\MigrationPlanFactory;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\CalculateMigrationPlanService;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\ExecuteMigrationService;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\GenerateMigrationService;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\MigrateService;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\ProvideMigrationStatusService;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\RollbackMigrationService;
use GeorgeBent\MongodbMigrationsBundle\DependencyInjection\MongodbMigrationsExtension;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\Factory\MigrationStatusFactory;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\Factory\MigrationStatusNumbersFactory;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Configuration\ProvideMigrationConfigurationService;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Console\Command\ExecuteMigrationCommand;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Console\Command\GenerateMigrationCommand;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Console\Command\MigrateCommand;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Console\Command\RollbackMigrationCommand;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Console\Command\StatusCommand;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Migration\FileMigrationFileGenerator;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Migration\FileMigrationRegistry;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Migration\MongoDbMigrationRunner;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\MongoDb\MongoDbDatabaseFactory;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\MongoDb\MongoDbVersionStorage;
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
            '$databaseName' => param(MongodbMigrationsExtension::PARAMETER_DATABASE),
            '$migrationsNamespace' => param(MongodbMigrationsExtension::PARAMETER_MIGRATIONS_NAMESPACE),
            '$migrationsDirectory' => param(MongodbMigrationsExtension::PARAMETER_MIGRATIONS_DIRECTORY),
            '$versionCollectionName' => param(MongodbMigrationsExtension::PARAMETER_MIGRATIONS_COLLECTION),
            '$databaseUrl' => param(MongodbMigrationsExtension::PARAMETER_URL),
            '$configurationSource' => null,
        ]);
    $services->alias(MigrationConfigurationProviderInterface::class, ProvideMigrationConfigurationService::class);

    $services->set(FileMigrationFileGenerator::class);
    $services->alias(MigrationFileGeneratorInterface::class, FileMigrationFileGenerator::class);

    $services->set(FileMigrationRegistry::class);
    $services->alias(MigrationRegistryInterface::class, FileMigrationRegistry::class);

    $services->set(MongoDbDatabaseFactory::class);
    $services->set(MongoDbVersionStorage::class);
    $services->alias(VersionStorageInterface::class, MongoDbVersionStorage::class);
    $services->set(MongoDbMigrationRunner::class);
    $services->alias(MigrationRunnerInterface::class, MongoDbMigrationRunner::class);

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
