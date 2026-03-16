<?php

declare(strict_types=1);

namespace Brnshkr\Config;

use LogicException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

use function array_keys;
use function iterator_to_array;

/**
 * @api
 *
 * @no-named-arguments
 */
final class PhpFileFinder
{
    private function __construct() {}

    /**
     * @throws DirectoryNotFoundException
     */
    public static function get(?Finder $finder = null): Finder
    {
        if (!$finder instanceof Finder) {
            $finder = new Finder()->in('.');
        }

        $finder = self::configure($finder);

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
     * @internal
     */
    public static function configure(Finder $finder): Finder
    {
        return $finder
            ->files()
            ->name('/\.php$/')
            ->ignoreDotFiles(false)
            ->sortByCaseInsensitiveName(true)
            ->notPath([
                'config/reference.php',
                'tests/coverage',
                '/^tests(?:\/.+)?\/Fixtures/',
            ])
            ->exclude([
                '.cache',
                '.local',
                'node_modules',
                'var',
                'vendor',
            ])
        ;
    }
}
