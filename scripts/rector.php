<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPhpVersion(\Rector\ValueObject\PhpVersion::PHP_82)
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->withRules([
        \Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class,
    ])
    ->withSets([
        \Rector\Set\ValueObject\SetList::CODE_QUALITY,
        \Rector\Set\ValueObject\SetList::TYPE_DECLARATION,
        \Rector\Set\ValueObject\SetList::EARLY_RETURN,
    ]);
