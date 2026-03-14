<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class MongodbMigrationsExtension extends Extension
{
    public const string PARAMETER_DATABASE = 'mongodb_migrations.database';

    public const string PARAMETER_URL = 'mongodb_migrations.url';

    public const string PARAMETER_MIGRATIONS_COLLECTION = 'mongodb_migrations.migrations_collection';

    public const string PARAMETER_MIGRATIONS_DIRECTORY = 'mongodb_migrations.migrations_directory';

    public const string PARAMETER_MIGRATIONS_NAMESPACE = 'mongodb_migrations.migrations_namespace';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $processedConfiguration = $this->processConfiguration($configuration, $configs);

        $container->setParameter(self::PARAMETER_DATABASE, $processedConfiguration[Configuration::KEY_DATABASE]);
        $container->setParameter(self::PARAMETER_URL, $processedConfiguration[Configuration::KEY_URL]);
        $container->setParameter(
            self::PARAMETER_MIGRATIONS_COLLECTION,
            $processedConfiguration[Configuration::KEY_MIGRATIONS_COLLECTION],
        );
        $container->setParameter(
            self::PARAMETER_MIGRATIONS_DIRECTORY,
            $processedConfiguration[Configuration::KEY_MIGRATIONS_DIRECTORY],
        );
        $container->setParameter(
            self::PARAMETER_MIGRATIONS_NAMESPACE,
            $processedConfiguration[Configuration::KEY_MIGRATIONS_NAMESPACE],
        );

        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/config'));
        $loader->load('services.php');
    }

    public function getAlias(): string
    {
        return Configuration::ROOT_NODE;
    }
}
