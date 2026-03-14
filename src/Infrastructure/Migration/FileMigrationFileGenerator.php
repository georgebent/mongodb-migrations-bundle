<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Infrastructure\Migration;

use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\GenerateMigrationResultInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationConfiguration;
use GeorgeBent\MongoDBMigrationsBundle\Application\Contract\MigrationFileGeneratorInterface;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\ApplicationError;
use GeorgeBent\MongoDBMigrationsBundle\Application\Model\GenerateMigrationResult;
use GeorgeBent\MongoDBMigrationsBundle\Domain\Migration\MigrationVersion;

final readonly class FileMigrationFileGenerator implements MigrationFileGeneratorInterface
{
    private const string ERROR_MIGRATION_ALREADY_EXISTS = 'migration_already_exists';

    private const string ERROR_MIGRATION_DIRECTORY_CREATION_FAILED = 'migration_directory_creation_failed';

    private const string ERROR_MIGRATION_FILE_WRITE_FAILED = 'migration_file_write_failed';

    private const string MESSAGE_MIGRATION_ALREADY_EXISTS = 'Migration file already exists.';

    private const string MESSAGE_MIGRATION_DIRECTORY_CREATION_FAILED = 'Migration directory could not be created.';

    private const string MESSAGE_MIGRATION_FILE_WRITE_FAILED = 'Migration file could not be written.';

    private const string TEMPLATE_DECLARE = "declare(strict_types=1);\n";

    private const string TEMPLATE_USE_MIGRATION_INTERFACE = 'use GeorgeBent\MongoDBMigrationsBundle\Migration\MigrationInterface;';

    private const string TEMPLATE_USE_DATABASE = 'use MongoDB\Database;';

    public function generate(
        MigrationConfiguration $configuration,
        MigrationVersion $migrationVersion,
    ): GenerateMigrationResultInterface {
        $migrationDirectory = $configuration->migrationsDirectory();

        if (false === is_dir($migrationDirectory) && false === mkdir($migrationDirectory, 0777, true)) {
            return new GenerateMigrationResult(
                false,
                error: new ApplicationError(
                    self::ERROR_MIGRATION_DIRECTORY_CREATION_FAILED,
                    self::MESSAGE_MIGRATION_DIRECTORY_CREATION_FAILED,
                ),
            );
        }

        $migrationFilePath = $migrationDirectory . DIRECTORY_SEPARATOR . $migrationVersion->className() . '.php';

        if (true === file_exists($migrationFilePath)) {
            return new GenerateMigrationResult(
                false,
                error: new ApplicationError(
                    self::ERROR_MIGRATION_ALREADY_EXISTS,
                    self::MESSAGE_MIGRATION_ALREADY_EXISTS,
                ),
            );
        }

        if (false === file_put_contents($migrationFilePath, $this->template($configuration, $migrationVersion))) {
            return new GenerateMigrationResult(
                false,
                error: new ApplicationError(
                    self::ERROR_MIGRATION_FILE_WRITE_FAILED,
                    self::MESSAGE_MIGRATION_FILE_WRITE_FAILED,
                ),
            );
        }

        return new GenerateMigrationResult(true, $migrationVersion);
    }

    private function template(MigrationConfiguration $configuration, MigrationVersion $migrationVersion): string
    {
        return "<?php\n\n"
            . self::TEMPLATE_DECLARE
            . "\n"
            . 'namespace ' . $configuration->migrationsNamespace() . ";\n\n"
            . self::TEMPLATE_USE_MIGRATION_INTERFACE . "\n"
            . self::TEMPLATE_USE_DATABASE . "\n\n"
            . 'final class ' . $migrationVersion->className() . " implements MigrationInterface\n"
            . "{\n"
            . "    public function up(Database \$database): void\n"
            . "    {\n"
            . "    }\n\n"
            . "    public function down(Database \$database): void\n"
            . "    {\n"
            . "    }\n"
            . "}\n";
    }
}
