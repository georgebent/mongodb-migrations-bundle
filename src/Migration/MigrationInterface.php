<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Migration;

use MongoDB\Database;

interface MigrationInterface
{
    public function up(Database $database): void;

    public function down(Database $database): void;
}
