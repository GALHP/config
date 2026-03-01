<?php

declare(strict_types=1);

namespace Brnshkr\Config;

use function array_filter;
use function array_last;
use function array_slice;
use function array_values;
use function count;
use function implode;
use function mb_strtolower;
use function preg_match;
use function sprintf;
use function str_starts_with;

use const PREG_UNMATCHED_AS_NULL;

/**
 * @internal
 */
final readonly class Str
{
    private function __construct() {}

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
