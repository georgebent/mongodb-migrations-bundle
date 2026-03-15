<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Application\Service;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\GenerateMigrationResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationFileGeneratorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationPlanCalculatorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationRunnerInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Factory\MigrationPlanFactory;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\ApplicationError;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\GenerateMigrationResult;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationExecutionResult;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\MigrationPlanResult;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\ExecuteMigrationService;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\GenerateMigrationService;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\MigrateService;
use GeorgeBent\MongoDBMigrationsBundle\Application\Service\RollbackMigrationService;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;
use PHPUnit\Framework\TestCase;

final class OrchestrationServicesTest extends TestCase
{
    public function testGenerateMigrationServiceDelegatesToFileGenerator(): void
    {
        $migrationFileGenerator = $this->createMock(MigrationFileGeneratorInterface::class);
        $configuration = $this->migrationConfiguration();

        $migrationFileGenerator->expects(self::once())
            ->method('generate')
            ->with(
                $configuration,
                self::callback(static fn (MigrationVersion $migrationVersion): bool => 14 === strlen($migrationVersion->value())),
            )
            ->willReturn(new GenerateMigrationResult(true, new MigrationVersion('20260221000000')));

        $generateMigrationResult = new GenerateMigrationService(
            new MigrationVersionFactory(),
            $migrationFileGenerator,
        )->generate($configuration);

        self::assertTrue($generateMigrationResult->isSuccess());
        self::assertSame('20260221000000', $generateMigrationResult->generatedMigrationVersion()?->value());
    }

    public function testMigrateServiceReturnsPlannerErrorWithoutRunningMigrations(): void
    {
        $migrationPlanCalculator = $this->createMock(MigrationPlanCalculatorInterface::class);
        $migrationRunner = $this->createMock(MigrationRunnerInterface::class);
        $configuration = $this->migrationConfiguration();
        $error = new ApplicationError('planner_error', 'Planner error');

        $migrationPlanCalculator->expects(self::once())
            ->method('forLatest')
            ->with($configuration)
            ->willReturn(new MigrationPlanResult(false, error: $error));

        $migrationRunner->expects(self::never())->method('run');

        $migrationExecutionResult = (new MigrateService($migrationPlanCalculator, $migrationRunner))->migrate($configuration);

        self::assertFalse($migrationExecutionResult->isSuccess());
        self::assertSame('planner_error', $migrationExecutionResult->error()?->identifier());
    }

    public function testExecuteMigrationServiceRunsCalculatedPlan(): void
    {
        $migrationPlanCalculator = $this->createMock(MigrationPlanCalculatorInterface::class);
        $migrationRunner = $this->createMock(MigrationRunnerInterface::class);
        $configuration = $this->migrationConfiguration();
        $migrationVersion = new MigrationVersion('20260221000000');
        $migrationPlan = (new MigrationPlanFactory())->create(ExecutionDirection::Up, []);
        $expectedResult = new MigrationExecutionResult(true, [$migrationVersion]);

        $migrationPlanCalculator->expects(self::once())
            ->method('forVersion')
            ->with($configuration, $migrationVersion, ExecutionDirection::Up)
            ->willReturn(new MigrationPlanResult(true, $migrationPlan));

        $migrationRunner->expects(self::once())
            ->method('run')
            ->with($configuration, $migrationPlan)
            ->willReturn($expectedResult);

        $migrationExecutionResult = new ExecuteMigrationService(
            $migrationPlanCalculator,
            $migrationRunner,
        )->execute($configuration, $migrationVersion, ExecutionDirection::Up);

        self::assertSame($expectedResult, $migrationExecutionResult);
    }

    public function testRollbackMigrationServiceRunsRollbackPlan(): void
    {
        $migrationPlanCalculator = $this->createMock(MigrationPlanCalculatorInterface::class);
        $migrationRunner = $this->createMock(MigrationRunnerInterface::class);
        $configuration = $this->migrationConfiguration();
        $migrationVersion = new MigrationVersion('20260221000000');
        $migrationPlan = new MigrationPlanFactory()->create(ExecutionDirection::Down, []);
        $expectedResult = new MigrationExecutionResult(true, [$migrationVersion]);

        $migrationPlanCalculator->expects(self::once())
            ->method('forRollback')
            ->with($configuration)
            ->willReturn(new MigrationPlanResult(true, $migrationPlan));

        $migrationRunner->expects(self::once())
            ->method('run')
            ->with($configuration, $migrationPlan)
            ->willReturn($expectedResult);

        $migrationExecutionResult = new RollbackMigrationService(
            $migrationPlanCalculator,
            $migrationRunner,
        )->rollback($configuration);

        self::assertSame($expectedResult, $migrationExecutionResult);
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
