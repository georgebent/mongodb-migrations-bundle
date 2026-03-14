<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Migration;

use MongoDB\Database;

interface MigrationInterface
{
    public function up(Database $database): void;

    public function down(Database $database): void;
}
