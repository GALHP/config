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
     * @internal
     */
    public const array EXCLUDED_DIRECTORIES = [
        '.cache',
        '.local',
        'node_modules',
        'var',
        'vendor',
    ];

    /**
     * @internal
     */
    public const array EXCLUDED_PATHS = [
        '*/fixtures',
        'config/reference.php',
        'tests/coverage',
    ];

    private function __construct() {}

    /**
     * @throws DirectoryNotFoundException
     */
    public static function get(?Finder $finder = null): Finder
    {
        if (!$finder instanceof Finder) {
            $finder = new Finder()->in('.');
        }

        $finder
            ->files()
            ->name('/\.php$/')
            ->ignoreDotFiles(false)
            ->sortByCaseInsensitiveName(true)
            ->exclude(self::EXCLUDED_DIRECTORIES)
            ->notPath(array_map(
                static fn (string $path): string => Str::doesContain($path, '*')
                    ? Str::fnmatchToRegex($path)
                    : $path,
                self::EXCLUDED_PATHS,
            ))
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
        return self::get($finder)
            |> iterator_to_array(...)
            |> array_keys(...);
    }

    /**
     * @return list<string>
     *
     * @throws DirectoryNotFoundException
     */
    public static function getDirectoryPaths(?Finder $finder = null): array
    {
        return self::get($finder)
            |> iterator_to_array(...)
            |> array_values(...)
            |> (static fn (array $files): array => array_map(
                static fn (SplFileInfo $file): string => $file->getPath(),
                $files,
            ))
            |> array_unique(...)
            |> (
                static function (array $paths): array {
                    usort($paths, static fn (string $path1, string $path2): int => Str::length($path1) <=> Str::length($path2));

                    return $paths;
                }
            )
            |> (static fn (array $sortedPaths): array => array_reduce(
                $sortedPaths,
                static fn (array $minimalPaths, string $currentPath): array => array_reduce(
                    $minimalPaths,
                    static fn (bool $doSkip, mixed $parentPath): bool => $doSkip
                        || Str::doesStartWith($currentPath, Str::trim(is_string($parentPath) ? $parentPath : '', '/', 'end') . '/'),
                    false,
                )
                ? $minimalPaths
                : [...$minimalPaths, $currentPath],
                [],
            ))
            |> (static fn (array $minimalPaths): array => array_filter($minimalPaths, is_string(...)))
            |> array_values(...);
    }
}
