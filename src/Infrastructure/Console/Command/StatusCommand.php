<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationStatusProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: self::NAME, description: self::DESCRIPTION)]
final class StatusCommand extends Command
{
    private const string NAME = 'mongodb:migrations:status';

    private const string DESCRIPTION = 'Show MongoDB migration status information.';

    private const string FALLBACK_CONFIGURATION_SOURCE = 'n/a';

    private const string FALLBACK_VERSION = '0';

    private const string ERROR_CONFIGURATION_INVALID = 'Migration configuration is invalid.';

    private const string ERROR_STATUS_LOADING_FAILED = 'Migration status loading failed.';

    private const string LABEL_DATABASE_DRIVER = 'Database Driver';

    private const string LABEL_DATABASE_NAME = 'Database Name';

    private const string LABEL_CONFIGURATION_SOURCE = 'Configuration Source';

    private const string LABEL_VERSION_COLLECTION_NAME = 'Version Collection Name';

    private const string LABEL_MIGRATIONS_NAMESPACE = 'Migrations Namespace';

    private const string LABEL_MIGRATIONS_DIRECTORY = 'Migrations Directory';

    private const string LABEL_CURRENT_VERSION = 'Current Version';

    private const string LABEL_LATEST_VERSION = 'Latest Version';

    private const string LABEL_EXECUTED_MIGRATIONS = 'Executed Migrations';

    private const string LABEL_EXECUTED_UNAVAILABLE_MIGRATIONS = 'Executed Unavailable Migrations';

    private const string LABEL_AVAILABLE_MIGRATIONS = 'Available Migrations';

    private const string LABEL_NEW_MIGRATIONS = 'New Migrations';

    public function __construct(
        private readonly MigrationConfigurationProviderInterface $migrationConfigurationProvider,
        private readonly MigrationStatusProviderInterface $migrationStatusProvider,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $migrationConfigurationResult = $this->migrationConfigurationProvider->provide();

            if (false === $migrationConfigurationResult->isSuccess() || null === $migrationConfigurationResult->migrationConfiguration()) {
                $io->error($migrationConfigurationResult->error()?->message() ?? self::ERROR_CONFIGURATION_INVALID);

                return Command::FAILURE;
            }

            $migrationStatusResult = $this->migrationStatusProvider->provide(
                $migrationConfigurationResult->migrationConfiguration(),
            );

            if (false === $migrationStatusResult->isSuccess() || null === $migrationStatusResult->migrationStatus()) {
                $io->error($migrationStatusResult->error()?->message() ?? self::ERROR_STATUS_LOADING_FAILED);

                return Command::FAILURE;
            }

            $migrationStatus = $migrationStatusResult->migrationStatus();
            $table = new Table($output);
            $table->setRows([
                [self::LABEL_DATABASE_DRIVER, $migrationStatus->databaseDriver()],
                [self::LABEL_DATABASE_NAME, $migrationStatus->databaseName()],
                [self::LABEL_CONFIGURATION_SOURCE, $migrationStatus->configurationSource() ?? self::FALLBACK_CONFIGURATION_SOURCE],
                [self::LABEL_VERSION_COLLECTION_NAME, $migrationStatus->versionCollectionName()],
                [self::LABEL_MIGRATIONS_NAMESPACE, $migrationStatus->migrationsNamespace()],
                [self::LABEL_MIGRATIONS_DIRECTORY, $migrationStatus->migrationsDirectory()],
                [self::LABEL_CURRENT_VERSION, $this->formatCurrentVersion($migrationStatus->currentVersion())],
                [self::LABEL_LATEST_VERSION, $this->formatLatestVersion($migrationStatus->latestVersion())],
                [self::LABEL_EXECUTED_MIGRATIONS, (string) $migrationStatus->numbers()->executedMigrations()],
                [self::LABEL_EXECUTED_UNAVAILABLE_MIGRATIONS, (string) $migrationStatus->numbers()->executedUnavailableMigrations()],
                [self::LABEL_AVAILABLE_MIGRATIONS, (string) $migrationStatus->numbers()->availableMigrations()],
                [self::LABEL_NEW_MIGRATIONS, (string) $migrationStatus->numbers()->newMigrations()],
            ]);
            $table->render();

            return Command::SUCCESS;
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            return Command::FAILURE;
        }
    }

    private function formatCurrentVersion(?MigrationVersion $migrationVersion): string
    {
        return $migrationVersion?->value() ?? self::FALLBACK_VERSION;
    }

    private function formatLatestVersion(?MigrationVersion $migrationVersion): string
    {
        if (null === $migrationVersion) {
            return self::FALLBACK_VERSION;
        }

        return $migrationVersion->formatted() . ' (' . $migrationVersion->value() . ')';
    }
}
