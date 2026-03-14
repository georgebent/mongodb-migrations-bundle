<?php

declare(strict_types=1);

namespace GeorgeBent\MongodbMigrationsBundle\Domain\Migration;

enum ExecutionDirection: string
{
    case Up = 'up';
    case Down = 'down';
}
