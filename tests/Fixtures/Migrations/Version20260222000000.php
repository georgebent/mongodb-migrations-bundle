<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Tests\Fixtures\Migrations;

use GeorgeBent\MongodbMigrationsBundle\Migration\MigrationInterface;
use MongoDB\Database;

final class Version20260222000000 implements MigrationInterface
{
    public function up(Database $database): void {}

    public function down(Database $database): void {}
}
