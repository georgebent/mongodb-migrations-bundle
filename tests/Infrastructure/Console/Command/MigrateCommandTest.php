<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Infrastructure\Console\Command;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrateInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationConfigurationResult;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationExecutionResult;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command\MigrateCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class MigrateCommandTest extends TestCase
{
    public function testItPrintsExecutedVersions(): void
    {
        $migrationConfigurationProvider = $this->createMock(MigrationConfigurationProviderInterface::class);
        $migrate = $this->createMock(MigrateInterface::class);
        $configuration = $this->migrationConfiguration();

        $migrationConfigurationProvider->expects(self::once())
            ->method('provide')
            ->willReturn(new MigrationConfigurationResult(true, $configuration));

        $migrate->expects(self::once())
            ->method('migrate')
            ->with($configuration)
            ->willReturn(new MigrationExecutionResult(true, [new MigrationVersion('20260221000000')]));

        $commandTester = new CommandTester(new MigrateCommand($migrationConfigurationProvider, $migrate));
        $exitCode = $commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Executed: Version20260221000000', $commandTester->getDisplay());
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
