<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Tests\Application\Service;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\GenerateMigrationResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationFileGeneratorInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationPlanCalculatorInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationRunnerInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Factory\MigrationPlanFactory;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\ApplicationError;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\GenerateMigrationResult;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationExecutionResult;
use GeorgeBent\MongodbMigrationsBundle\Application\Model\MigrationPlanResult;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\ExecuteMigrationService;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\GenerateMigrationService;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\MigrateService;
use GeorgeBent\MongodbMigrationsBundle\Application\Service\RollbackMigrationService;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\ExecutionDirection;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\MigrationVersion;
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

        $generateMigrationResult = (new GenerateMigrationService(
            new MigrationVersionFactory(),
            $migrationFileGenerator,
        ))->generate($configuration);

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

        $migrationExecutionResult = (new ExecuteMigrationService(
            $migrationPlanCalculator,
            $migrationRunner,
        ))->execute($configuration, $migrationVersion, ExecutionDirection::Up);

        self::assertSame($expectedResult, $migrationExecutionResult);
    }

    public function testRollbackMigrationServiceRunsRollbackPlan(): void
    {
        $migrationPlanCalculator = $this->createMock(MigrationPlanCalculatorInterface::class);
        $migrationRunner = $this->createMock(MigrationRunnerInterface::class);
        $configuration = $this->migrationConfiguration();
        $migrationVersion = new MigrationVersion('20260221000000');
        $migrationPlan = (new MigrationPlanFactory())->create(ExecutionDirection::Down, []);
        $expectedResult = new MigrationExecutionResult(true, [$migrationVersion]);

        $migrationPlanCalculator->expects(self::once())
            ->method('forRollback')
            ->with($configuration)
            ->willReturn(new MigrationPlanResult(true, $migrationPlan));

        $migrationRunner->expects(self::once())
            ->method('run')
            ->with($configuration, $migrationPlan)
            ->willReturn($expectedResult);

        $migrationExecutionResult = (new RollbackMigrationService(
            $migrationPlanCalculator,
            $migrationRunner,
        ))->rollback($configuration);

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
