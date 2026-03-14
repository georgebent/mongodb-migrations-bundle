<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\GenerateMigrationInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: self::NAME)]
final class GenerateMigrationCommand extends Command
{
    private const string NAME = 'mongodb:migrations:generate';

    private const string ERROR_CONFIGURATION_INVALID = 'Migration configuration is invalid.';

    private const string ERROR_GENERATION_FAILED = 'Migration generation failed.';

    private const string SUCCESS_PREFIX = 'Generated ';

    public function __construct(
        private readonly MigrationConfigurationProviderInterface $migrationConfigurationProvider,
        private readonly GenerateMigrationInterface $generateMigration,
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

            $generateMigrationResult = $this->generateMigration->generate(
                $migrationConfigurationResult->migrationConfiguration(),
            );

            if (false === $generateMigrationResult->isSuccess() || null === $generateMigrationResult->generatedMigrationVersion()) {
                $io->error($generateMigrationResult->error()?->message() ?? self::ERROR_GENERATION_FAILED);

                return Command::FAILURE;
            }

            $io->success(self::SUCCESS_PREFIX . $generateMigrationResult->generatedMigrationVersion()->className());

            return Command::SUCCESS;
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            return Command::FAILURE;
        }
    }
}
