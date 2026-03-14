<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\ExecuteMigrationInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: self::NAME)]
final class ExecuteMigrationCommand extends Command
{
    private const string NAME = 'mongodb:migrations:execute';

    private const string ARGUMENT_VERSION = 'version';

    private const string OPTION_UP = 'up';

    private const string OPTION_DOWN = 'down';

    private const string ERROR_DIRECTION_REQUIRED = 'Either --up or --down must be provided.';

    private const string ERROR_DIRECTION_CONFLICT = 'Use only one execution direction at a time.';

    private const string ERROR_CONFIGURATION_INVALID = 'Migration configuration is invalid.';

    private const string ERROR_EXECUTION_FAILED = 'Migration execution failed.';

    private const string SUCCESS_PROCESSED_PREFIX = 'Processed: ';

    public function __construct(
        private readonly MigrationConfigurationProviderInterface $migrationConfigurationProvider,
        private readonly ExecuteMigrationInterface $executeMigration,
        private readonly MigrationVersionFactory $migrationVersionFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_VERSION, InputArgument::REQUIRED)
            ->addOption(self::OPTION_UP, null, InputOption::VALUE_NONE)
            ->addOption(self::OPTION_DOWN, null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $executionDirection = $this->executionDirection($input);

            if (null === $executionDirection) {
                $io->error($this->directionErrorMessage($input));

                return Command::FAILURE;
            }

            $migrationConfigurationResult = $this->migrationConfigurationProvider->provide();

            if (false === $migrationConfigurationResult->isSuccess() || null === $migrationConfigurationResult->migrationConfiguration()) {
                $io->error($migrationConfigurationResult->error()?->message() ?? self::ERROR_CONFIGURATION_INVALID);

                return Command::FAILURE;
            }

            $migrationExecutionResult = $this->executeMigration->execute(
                $migrationConfigurationResult->migrationConfiguration(),
                $this->migrationVersionFactory->fromString((string) $input->getArgument(self::ARGUMENT_VERSION)),
                $executionDirection,
            );

            if (false === $migrationExecutionResult->isSuccess()) {
                $io->error($migrationExecutionResult->error()?->message() ?? self::ERROR_EXECUTION_FAILED);

                return Command::FAILURE;
            }

            $io->success(
                self::SUCCESS_PROCESSED_PREFIX . implode(
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

    private function executionDirection(InputInterface $input): ?ExecutionDirection
    {
        $executeUp = (bool) $input->getOption(self::OPTION_UP);
        $executeDown = (bool) $input->getOption(self::OPTION_DOWN);

        if ($executeUp === $executeDown) {
            return null;
        }

        return $executeUp ? ExecutionDirection::Up : ExecutionDirection::Down;
    }

    private function directionErrorMessage(InputInterface $input): string
    {
        return (bool) $input->getOption(self::OPTION_UP) === (bool) $input->getOption(self::OPTION_DOWN)
            && ((bool) $input->getOption(self::OPTION_UP) || (bool) $input->getOption(self::OPTION_DOWN))
                ? self::ERROR_DIRECTION_CONFLICT
                : self::ERROR_DIRECTION_REQUIRED;
    }
}
