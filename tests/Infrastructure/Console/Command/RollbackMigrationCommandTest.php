<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Tests\Infrastructure\Console\Command;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\RollbackMigrationInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationConfigurationResult;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationExecutionResult;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Console\Command\RollbackMigrationCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class RollbackMigrationCommandTest extends TestCase
{
    public function testItPrintsRolledBackVersion(): void
    {
        $migrationConfigurationProvider = $this->createMock(MigrationConfigurationProviderInterface::class);
        $rollbackMigration = $this->createMock(RollbackMigrationInterface::class);
        $configuration = $this->migrationConfiguration();

        $migrationConfigurationProvider->expects(self::once())
            ->method('provide')
            ->willReturn(new MigrationConfigurationResult(true, $configuration));

        $rollbackMigration->expects(self::once())
            ->method('rollback')
            ->with($configuration)
            ->willReturn(new MigrationExecutionResult(true, [new MigrationVersion('20260221000000')]));

        $commandTester = new CommandTester(new RollbackMigrationCommand($migrationConfigurationProvider, $rollbackMigration));
        $exitCode = $commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Rolled back: Version20260221000000', $commandTester->getDisplay());
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
