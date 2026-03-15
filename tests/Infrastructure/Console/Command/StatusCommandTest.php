<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Infrastructure\Console\Command;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationStatusProviderInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationConfigurationResult;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationStatusResult;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationStatusFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationStatusNumbersFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Console\Command\StatusCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class StatusCommandTest extends TestCase
{
    public function testItRendersStatusTable(): void
    {
        $migrationConfigurationProvider = $this->createMock(MigrationConfigurationProviderInterface::class);
        $migrationStatusProvider = $this->createMock(MigrationStatusProviderInterface::class);
        $configuration = $this->migrationConfiguration();

        $migrationConfigurationProvider->expects(self::once())
            ->method('provide')
            ->willReturn(new MigrationConfigurationResult(true, $configuration));

        $migrationStatusProvider->expects(self::once())
            ->method('provide')
            ->with($configuration)
            ->willReturn(new MigrationStatusResult(
                true,
                (new MigrationStatusFactory())->create(
                    'test_database',
                    null,
                    'migrations',
                    'App\\Migrations',
                    '/tmp/migrations',
                    new MigrationVersion('20260221000000'),
                    new MigrationVersion('20260222000000'),
                    new MigrationStatusNumbersFactory()->create(1, 0, 2, 1),
                ),
            ));

        $commandTester = new CommandTester(new StatusCommand($migrationConfigurationProvider, $migrationStatusProvider));
        $exitCode = $commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Database Driver', $commandTester->getDisplay());
        self::assertStringContainsString('Latest Version', $commandTester->getDisplay());
        self::assertStringContainsString('2026-02-22 00:00:00 (20260222000000)', $commandTester->getDisplay());
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
