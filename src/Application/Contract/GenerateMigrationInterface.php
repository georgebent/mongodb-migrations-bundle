<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Application\Contract;

interface GenerateMigrationInterface
{
    public function generate(MigrationConfiguration $configuration): GenerateMigrationResultInterface;
}
