<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\CodeQuality\Rector\Class_\YamlToAttributeDoctrineMappingRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
    ->withoutParallel()
    ->withConfiguredRule(YamlToAttributeDoctrineMappingRector::class, [
        __DIR__ . '/config/routes',
    ])
;