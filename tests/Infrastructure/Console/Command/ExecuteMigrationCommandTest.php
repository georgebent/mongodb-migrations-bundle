<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Infrastructure\Console\Command;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\ExecuteMigrationInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationConfigurationResult;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationExecutionResult;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command\ExecuteMigrationCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ExecuteMigrationCommandTest extends TestCase
{
    public function testItRequiresDirectionOption(): void
    {
        $migrationConfigurationProvider = $this->createMock(MigrationConfigurationProviderInterface::class);
        $executeMigration = $this->createMock(ExecuteMigrationInterface::class);

        $migrationConfigurationProvider->expects(self::never())->method('provide');
        $executeMigration->expects(self::never())->method('execute');

        $commandTester = new CommandTester(new ExecuteMigrationCommand(
            $migrationConfigurationProvider,
            $executeMigration,
            new MigrationVersionFactory(),
        ));

        $exitCode = $commandTester->execute([
            'version' => '20260221000000',
        ]);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('Either --up or --down must be provided.', $commandTester->getDisplay());
    }

    public function testItExecutesMigrationWithUpDirection(): void
    {
        $migrationConfigurationProvider = $this->createMock(MigrationConfigurationProviderInterface::class);
        $executeMigration = $this->createMock(ExecuteMigrationInterface::class);
        $configuration = $this->migrationConfiguration();

        $migrationConfigurationProvider->expects(self::once())
            ->method('provide')
            ->willReturn(new MigrationConfigurationResult(true, $configuration));

        $executeMigration->expects(self::once())
            ->method('execute')
            ->with(
                $configuration,
                self::callback(static fn (MigrationVersion $migrationVersion): bool => '20260221000000' === $migrationVersion->value()),
                ExecutionDirection::Up,
            )
            ->willReturn(new MigrationExecutionResult(true, [new MigrationVersion('20260221000000')]));

        $commandTester = new CommandTester(new ExecuteMigrationCommand(
            $migrationConfigurationProvider,
            $executeMigration,
            new MigrationVersionFactory(),
        ));
        $exitCode = $commandTester->execute([
            'version' => '20260221000000',
            '--up' => true,
        ]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Processed: Version20260221000000', $commandTester->getDisplay());
    }

    private function migrationConfiguration(): MigrationConfiguration
    {
        return new MigrationConfiguration(
            'test_database',
            'App\\Migrations',
            '/tmp/migrations',
            'migrations',
            'mongodb://localhost:27017',
        );
    }
}
