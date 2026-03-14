<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Service;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\GenerateMigrationInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\GenerateMigrationResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationFileGeneratorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\Factory\MigrationVersionFactory;

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
