<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Tests\Infrastructure\Console\Command;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\GenerateMigrationInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfigurationProviderInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\GenerateMigrationResult;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationConfigurationResult;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;
use GeorgeBent\MongodbMigrationsBundle\Infrastructure\Console\Command\GenerateMigrationCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class GenerateMigrationCommandTest extends TestCase
{
    public function testItPrintsSuccessMessage(): void
    {
        $migrationConfigurationProvider = $this->createMock(MigrationConfigurationProviderInterface::class);
        $generateMigration = $this->createMock(GenerateMigrationInterface::class);
        $configuration = $this->migrationConfiguration();

        $migrationConfigurationProvider->expects(self::once())
            ->method('provide')
            ->willReturn(new MigrationConfigurationResult(true, $configuration));

        $generateMigration->expects(self::once())
            ->method('generate')
            ->with($configuration)
            ->willReturn(new GenerateMigrationResult(true, new MigrationVersion('20260221000000')));

        $commandTester = new CommandTester(new GenerateMigrationCommand($migrationConfigurationProvider, $generateMigration));
        $exitCode = $commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Generated Version20260221000000', $commandTester->getDisplay());
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
