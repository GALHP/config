<?php

declare(strict_types=1);

namespace Brnshkr\Config;

use LogicException;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

use function array_keys;

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
    public static function getPaths(?Finder $finder = null): array
    {
        return array_keys([...self::get($finder)]);
    }
}
