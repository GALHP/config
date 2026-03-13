<?php

declare(strict_types=1);

namespace Brnshkr\Config;

use function array_filter;
use function array_last;
use function array_slice;
use function array_values;
use function count;
use function implode;
use function mb_rtrim;
use function mb_strlen;
use function mb_strrpos;
use function mb_strtolower;
use function mb_substr;
use function preg_match;
use function preg_quote;
use function sprintf;
use function str_contains;
use function str_replace;
use function str_starts_with;

use const PREG_UNMATCHED_AS_NULL;

/**
 * @internal
 */
final readonly class Str
{
    private function __construct() {}

    public static function length(string $string): int
    {
        // @phpstan-ignore-next-line symplify.forbiddenFuncCall (Avoid using symfony/string here to keep package as lighweight as possible)
        return mb_strlen($string);
    }

    public static function toLowerCase(string $string): string
    {
        // @phpstan-ignore-next-line symplify.forbiddenFuncCall (Avoid using symfony/string here to keep package as lighweight as possible)
        return mb_strtolower($string);
    }

    public static function doesStartWith(string $haystack, string $needle): bool
    {
        // @phpstan-ignore-next-line symplify.forbiddenFuncCall (Avoid using symfony/string here to keep package as lighweight as possible)
        return str_starts_with($haystack, $needle);
    }

    public static function trimEnd(string $string, ?string $characters = null): string
    {
        // @phpstan-ignore-next-line symplify.forbiddenFuncCall (Avoid using symfony/string here to keep package as lighweight as possible)
        return mb_rtrim($string, $characters);
    }

    /**
     * @return list<string>
     */
    public static function match(string $string, string $pattern, int $flags = 0): array
    {
        $matches = [];

        // @phpstan-ignore-next-line symplify.forbiddenFuncCall (Avoid using symfony/string here to keep package as lighweight as possible)
        $result = preg_match($pattern . 'u', $string, $matches, $flags | PREG_UNMATCHED_AS_NULL);

        return $result === false
            ? []
            : array_values(array_filter($matches, is_string(...)));
    }

    public static function doesContain(string $haystack, string $needle): bool
    {
        // @phpstan-ignore-next-line symplify.forbiddenFuncCall (Avoid using symfony/string here to keep package as lighweight as possible)
        return str_contains($haystack, $needle);
    }

    public static function replace(string $haystack, string $needle, string $replacement): string
    {
        // @phpstan-ignore-next-line symplify.forbiddenFuncCall (Avoid using symfony/string here to keep package as lighweight as possible)
        return str_replace($needle, $replacement, $haystack);
    }

    public static function fnmatchToRegex(string $pattern): string
    {
        return '/' . self::replace('\*', preg_quote($pattern, '/'), '.*') . '/';
    }

    public static function afterLast(string $haystack, string $needle): string
    {
        // @phpstan-ignore-next-line symplify.forbiddenFuncCall (Avoid using symfony/string here to keep package as lighweight as possible)
        return mb_substr($haystack, (mb_strrpos($haystack, $needle) ?: -1) + 1);
    }

    /**
     * @phpstan-param list<string> $strings
     * @phpstan-param 'conjunction'|'disjunction' $type
     */
    public static function joinAsQuotedList(
        array $strings,
        string $type = 'conjunction',
    ): string {
        return count($strings) > 1
            ? sprintf(
                '"%s" %s "%s"',
                implode('", "', array_slice($strings, 0, -1)),
                $type === 'conjunction' ? 'and' : 'or',
                array_last($strings),
            )
            : [
                0 => '',
                1 => isset($strings[0]) ? sprintf('"%s"', $strings[0]) : '',
            ][count($strings)];
    }
}
