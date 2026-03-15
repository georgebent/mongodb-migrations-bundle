<?php

declare(strict_types=1);

namespace MongoDB\Driver;

use MongoDB\BSON\Int64;

interface CursorInterface extends \Iterator
{
    public function getId(): Int64;

    public function getServer(): Server;

    public function isDead(): bool;

    public function setTypeMap(array $typeMap): void;

    public function toArray(): array;
}
