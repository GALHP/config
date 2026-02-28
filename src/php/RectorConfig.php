<?php

declare(strict_types=1);

namespace Brnshkr\Config;

use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector;
use Rector\Config\RectorConfig as BaseConfig;
use Rector\Configuration\RectorConfigBuilder;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use RuntimeException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

Module::assertInstalled(Module::MODULE_RECTOR);

/**
 * @api
 *
 * @no-named-arguments
 */
final readonly class RectorConfig
{
    private function __construct() {}

    /**
     * @throws DirectoryNotFoundException
     * @throws RuntimeException
     */
    public static function get(?Finder $finder = null): RectorConfigBuilder
    {
        /** @disregard P1009 because some skipped classes come bundled with rector and are not picked up by intelephense */
        return BaseConfig::configure()
            ->withCache('.cache/rector.cache')
            ->withRootFiles()
            ->withPaths(PhpFileFinder::getPaths($finder))
            ->withPreparedSets(
                deadCode: true,
                codeQuality: true,
                codingStyle: true,
                typeDeclarations: true,
                privatization: true,
                naming: true,
                instanceOf: true,
                earlyReturn: true,
                rectorPreset: true,
                phpunitCodeQuality: true,
                doctrineCodeQuality: true,
                symfonyCodeQuality: true,
                symfonyConfigs: true,
            )
            ->withSkip([
                LocallyCalledStaticMethodToNonStaticRector::class,
                NewlineBeforeNewAssignSetRector::class,
                NewlineBetweenClassLikeStmtsRector::class,
                NullToStrictStringFuncCallArgRector::class,
                PreferPHPUnitThisCallRector::class,
                SimplifyQuoteEscapeRector::class,
            ])
            ->withPhpSets()
            ->withAttributesSets()
            ->withImportNames(
                importNames: false,
                importDocBlockNames: false,
                importShortClasses: false,
                removeUnusedImports: true,
            )
        ;
    }
}

return RectorConfig::get();
