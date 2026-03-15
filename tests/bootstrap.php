<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (false === class_exists(\MongoDB\BSON\UTCDateTime::class)) {
    require_once __DIR__ . '/Stubs/MongoDB/BSON/UTCDateTime.php';
}

if (false === class_exists(\MongoDB\BSON\Int64::class)) {
    require_once __DIR__ . '/Stubs/MongoDB/BSON/Int64.php';
}

if (false === class_exists(\MongoDB\Driver\Server::class)) {
    require_once __DIR__ . '/Stubs/MongoDB/Driver/Server.php';
}

if (false === interface_exists(\MongoDB\Driver\CursorInterface::class)) {
    require_once __DIR__ . '/Stubs/MongoDB/Driver/CursorInterface.php';
}
