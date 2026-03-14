<?php

declare(strict_types=1);

namespace GeorgeBent\MongoDBMigrationsBundle;

use GeorgeBent\MongoDBMigrationsBundle\DependencyInjection\MongoDBMigrationsExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MongoDBMigrationsBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (!isset($this->extension)) {
            $this->extension = new MongoDBMigrationsExtension();
        }

        return false === $this->extension ? null : $this->extension;
    }
}
