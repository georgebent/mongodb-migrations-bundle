<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Service;

use GeorgeBent\MongodbMigrationsBundle\Application\Contract\GenerateMigrationInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\GenerateMigrationResultInterface;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongodbMigrationsBundle\Application\Contract\MigrationFileGeneratorInterface;
use GeorgeBent\MongodbMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;

final readonly class GenerateMigrationService implements GenerateMigrationInterface
{
    private const string TIMEZONE_UTC = 'UTC';

    public function __construct(
        private MigrationVersionFactory $migrationVersionFactory,
        private MigrationFileGeneratorInterface $migrationFileGenerator,
    ) {}

    public function generate(MigrationConfiguration $configuration): GenerateMigrationResultInterface
    {
        $migrationVersion = $this->migrationVersionFactory->fromDateTime(
            new \DateTimeImmutable('now', new \DateTimeZone(self::TIMEZONE_UTC)),
        );

        return $this->migrationFileGenerator->generate($configuration, $migrationVersion);
    }
}
