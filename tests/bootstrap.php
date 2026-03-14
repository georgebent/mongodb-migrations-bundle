<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (false === class_exists(\MongoDB\BSON\UTCDateTime::class)) {
    eval('namespace MongoDB\BSON; final class UTCDateTime {}');
}

if (false === class_exists(\MongoDB\BSON\Int64::class)) {
    eval('namespace MongoDB\BSON; final class Int64 { public function __construct(private readonly string $value = "0") {} public function __toString(): string { return $this->value; } }');
}

if (false === class_exists(\MongoDB\Driver\Server::class)) {
    eval('namespace MongoDB\Driver; final class Server {}');
}

if (false === interface_exists(\MongoDB\Driver\CursorInterface::class)) {
    eval('namespace MongoDB\Driver; interface CursorInterface extends \Iterator { public function getId(): \MongoDB\BSON\Int64; public function getServer(): \MongoDB\Driver\Server; public function isDead(): bool; public function setTypeMap(array $typeMap): void; public function toArray(): array; }');
}
