<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle\Domain\Migration;

final readonly class MigrationVersion implements \Stringable
{
    public const string CLASS_NAME_PREFIX = 'Version';

    public const string FORMAT = 'YmdHis';

    public const string DISPLAY_FORMAT = 'Y-m-d H:i:s';

    public const string VALIDATION_PATTERN = '/^\d{14}$/';

    public const string INVALID_FORMAT_MESSAGE = 'Migration version must use the YmdHis format.';

    public const string TIMEZONE_UTC = 'UTC';

    public function __construct(private string $versionString)
    {
        if (1 !== preg_match(self::VALIDATION_PATTERN, $this->versionString)) {
            throw new \InvalidArgumentException(self::INVALID_FORMAT_MESSAGE);
        }
    }

    public function value(): string
    {
        return $this->versionString;
    }

    public function className(): string
    {
        return self::CLASS_NAME_PREFIX . $this->versionString;
    }

    public function formatted(): string
    {
        return $this->dateTime()->format(self::DISPLAY_FORMAT);
    }

    public function dateTime(): \DateTimeImmutable
    {
        $migrationDateTime = \DateTimeImmutable::createFromFormat(
            self::FORMAT,
            $this->versionString,
            new \DateTimeZone(self::TIMEZONE_UTC),
        );

        if (false === $migrationDateTime) {
            throw new \LogicException(self::INVALID_FORMAT_MESSAGE);
        }

        return $migrationDateTime;
    }

    public function isSame(MigrationVersion $otherVersion): bool
    {
        return $this->versionString === $otherVersion->value();
    }

    public function isGreaterThan(MigrationVersion $otherVersion): bool
    {
        return $this->versionString > $otherVersion->value();
    }

    public function isLessThan(MigrationVersion $otherVersion): bool
    {
        return $this->versionString < $otherVersion->value();
    }

    public function __toString(): string
    {
        return $this->versionString;
    }
}
