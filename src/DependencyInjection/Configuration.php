<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public const string ROOT_NODE = 'mongodb_migrations';

    public const string KEY_DATABASE = 'database';

    public const string KEY_URL = 'url';

    public const string KEY_MIGRATIONS_COLLECTION = 'migrations_collection';

    public const string KEY_MIGRATIONS_DIRECTORY = 'migrations_directory';

    public const string KEY_MIGRATIONS_NAMESPACE = 'migrations_namespace';

    public const string DEFAULT_MIGRATIONS_COLLECTION = 'migrations';

    public const string DEFAULT_MIGRATIONS_NAMESPACE = 'App\\Migrations';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode(self::KEY_URL)
                    ->defaultValue('%env(MONGODB_URL)%')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode(self::KEY_DATABASE)
                    ->defaultValue('%env(MONGODB_DB)%')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode(self::KEY_MIGRATIONS_COLLECTION)
                    ->defaultValue(self::DEFAULT_MIGRATIONS_COLLECTION)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode(self::KEY_MIGRATIONS_DIRECTORY)
                    ->defaultValue('%kernel.project_dir%/migrations')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode(self::KEY_MIGRATIONS_NAMESPACE)
                    ->defaultValue(self::DEFAULT_MIGRATIONS_NAMESPACE)
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
