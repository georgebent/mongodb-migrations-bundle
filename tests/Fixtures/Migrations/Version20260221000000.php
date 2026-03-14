<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Tests\Fixtures\Migrations;

use GeorgeBent\MongoDBMigrationsBundle\Migration\MigrationInterface;
use MongoDB\Database;

final class Version20260221000000 implements MigrationInterface
{
    public function up(Database $database): void {}

    public function down(Database $database): void {}
}
