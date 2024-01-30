<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPHPStanConfigs([
        __DIR__ . '/phpstan.dist.neon',
    ])
    ->withPaths([
        __DIR__ . '/assets',
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRules([
        InlineConstructorDefaultToPropertyRector::class,
    ])
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::EARLY_RETURN,
        SetList::STRICT_BOOLEANS,
        SetList::NAMING,
        LevelSetList::UP_TO_PHP_83,
        SymfonySetList::SYMFONY_62,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    ->withSkip([
        __DIR__.'/assets/**/*.php',
        RenameVariableToMatchMethodCallReturnTypeRector::class,
        RenamePropertyToMatchTypeRector::class,
        RenameParamToMatchTypeRector::class,
        LocallyCalledStaticMethodToNonStaticRector::class,
    ])
    ;
