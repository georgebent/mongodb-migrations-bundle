<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Infrastructure\Console\Command;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrateInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: self::NAME)]
final class MigrateCommand extends Command
{
    private const string NAME = 'mongodb:migrations:migrate';

    private const string ERROR_CONFIGURATION_INVALID = 'Migration configuration is invalid.';

    private const string ERROR_EXECUTION_FAILED = 'Migration execution failed.';

    private const string SUCCESS_NO_NEW_MIGRATIONS = 'No new migrations to execute.';

    private const string SUCCESS_EXECUTED_PREFIX = 'Executed: ';

    public function __construct(
        private readonly MigrationConfigurationProviderInterface $migrationConfigurationProvider,
        private readonly MigrateInterface $migrate,
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

            $migrationExecutionResult = $this->migrate->migrate($migrationConfigurationResult->migrationConfiguration());

            if (false === $migrationExecutionResult->isSuccess()) {
                $io->error($migrationExecutionResult->error()?->message() ?? self::ERROR_EXECUTION_FAILED);

                return Command::FAILURE;
            }

            if ([] === $migrationExecutionResult->processedMigrationVersions()) {
                $io->success(self::SUCCESS_NO_NEW_MIGRATIONS);

                return Command::SUCCESS;
            }

            $io->success(
                self::SUCCESS_EXECUTED_PREFIX . implode(
                    ', ',
                    array_map(
                        static fn (\GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion $migrationVersion): string
                            => $migrationVersion->className(),
                        $migrationExecutionResult->processedMigrationVersions(),
                    ),
                ),
            );

            return Command::SUCCESS;
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            return Command::FAILURE;
        }
    }
}
