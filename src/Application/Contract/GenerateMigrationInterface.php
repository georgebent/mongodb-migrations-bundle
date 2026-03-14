<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Application\Contract;

interface GenerateMigrationInterface
{
    public function generate(MigrationConfiguration $configuration): GenerateMigrationResultInterface;
}
