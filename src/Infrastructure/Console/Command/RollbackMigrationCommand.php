<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\RollbackMigrationInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: self::NAME, description: self::DESCRIPTION)]
final class RollbackMigrationCommand extends Command
{
    private const string NAME = 'mongodb:migrations:rollback';

    private const string DESCRIPTION = 'Roll back the latest executed MongoDB migration.';

    private const string ERROR_CONFIGURATION_INVALID = 'Migration configuration is invalid.';

    private const string ERROR_ROLLBACK_FAILED = 'Rollback failed.';

    private const string SUCCESS_ROLLED_BACK_PREFIX = 'Rolled back: ';

    public function __construct(
        private readonly MigrationConfigurationProviderInterface $migrationConfigurationProvider,
        private readonly RollbackMigrationInterface $rollbackMigration,
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

            $migrationExecutionResult = $this->rollbackMigration->rollback(
                $migrationConfigurationResult->migrationConfiguration(),
            );

            if (false === $migrationExecutionResult->isSuccess()) {
                $io->error($migrationExecutionResult->error()?->message() ?? self::ERROR_ROLLBACK_FAILED);

                return Command::FAILURE;
            }

            $io->success(
                self::SUCCESS_ROLLED_BACK_PREFIX . implode(
                    ', ',
                    array_map(
                        static fn (\GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion $migrationVersion): string
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
