<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (false === class_exists(\MongoDB\BSON\UTCDateTime::class)) {
    eval('namespace MongoDB\BSON; final class UTCDateTime {}');
}

if (false === interface_exists(\MongoDB\Driver\CursorInterface::class)) {
    eval('namespace MongoDB\Driver; interface CursorInterface extends \Traversable {}');
}
