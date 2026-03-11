<?php

declare(strict_types=1);

namespace Brnshkr\Config;

use LogicException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function array_filter;
use function array_keys;
use function array_map;
use function array_reduce;
use function array_unique;
use function array_values;
use function is_string;
use function iterator_to_array;
use function usort;

/**
 * @api
 *
 * @no-named-arguments
 */
final class PhpFileFinder
{
    /**
     * @throws DirectoryNotFoundException
     */
    public static function get(?Finder $finder = null): Finder
    {
        if (!$finder instanceof Finder) {
            $finder = (new Finder())->in('.');
        }

        $finder
            ->files()
            ->name('/\.php$/')
            ->ignoreDotFiles(false)
            ->sortByCaseInsensitiveName(true)
            ->notPath([
                'config/reference.php',
                '/^tests\/.*\/fixtures/',
                '/^tests\/coverage/',
            ])
            ->exclude([
                '.cache',
                '.local',
                'node_modules',
                'var',
                'vendor',
            ])
        ;

        try {
            $finder->getIterator();
        } catch (LogicException) {
            $finder->in('.');
        }

        return $finder;
    }

    /**
     * @return list<string>
     *
     * @throws DirectoryNotFoundException
     */
    public static function getFilePaths(?Finder $finder = null): array
    {
        return array_keys(iterator_to_array(self::get($finder)));
    }

    /**
     * @return list<string>
     *
     * @throws DirectoryNotFoundException
     */
    public static function getDirectoryPaths(?Finder $finder = null): array
    {
        $files = array_values(iterator_to_array(self::get($finder)));

        $paths = array_unique(array_map(
            static fn (SplFileInfo $file): string => $file->getPath(),
            $files,
        ));

        usort($paths, static fn (string $path1, string $path2): int => Str::length($path1) <=> Str::length($path2));

        $minimalPaths = array_reduce(
            $paths,
            static fn (array $minimalPaths, string $currentPath): array => array_reduce(
                $minimalPaths,
                static fn (bool $doSkip, mixed $parentPath): bool => $doSkip
                    || Str::doesStartWith($currentPath, Str::trimEnd(is_string($parentPath) ? $parentPath : '', '/') . '/'),
                false,
            )
            ? $minimalPaths
            : [...$minimalPaths, $currentPath],
            [],
        );

        return array_values(array_filter($minimalPaths, is_string(...)));
    }
}
