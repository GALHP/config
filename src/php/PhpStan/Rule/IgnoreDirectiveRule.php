<?php

declare(strict_types=1);

namespace Brnshkr\Config\PhpStan\Rule;

use Brnshkr\Config\PhpStan\Rule\Trait\RuleTrait;
use Brnshkr\Config\Str;
use LogicException;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use RuntimeException;
use SplFileObject;

use function array_filter;
use function array_values;
use function is_file;
use function is_string;
use function preg_quote;
use function sprintf;

/**
 * @api
 *
 * @no-named-arguments
 *
 * @implements Rule<FileNode>
 */
final class IgnoreDirectiveRule implements Rule
{
    use RuleTrait;

    private const string ALLOWED_DIRECTIVE        = '@phpstan-ignore';
    private const string DIRECTIVE_SUFFIX_PATTERN = '(?:-[a-z-]+)?';
    private const string DESCRIPTION_PATTERN      = '\((?:[^()]+|\([^()]*\))+\)';
    private const string RULES_PATTERN            = '\s+[^\s(]+(?:\s*,\s*[^\s(]+)*\s+';

    public static string $allowedDirectivePregQuoted;

    #[Override]
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    /**
     * @return list<IdentifierRuleError>
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        return array_values(array_filter(
            self::processFile($scope->getTraitReflection()?->getFileName() ?? $scope->getFile()),
            static fn (?IdentifierRuleError $identifierRuleError): bool => $identifierRuleError instanceof IdentifierRuleError,
        ));
    }

    /**
     * @return list<?IdentifierRuleError>
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    private static function processFile(string $filePath): array
    {
        if (!is_file($filePath)) {
            return [];
        }

        $errors = [];

        foreach (new SplFileObject($filePath) as $lineIndex => $rawLine) {
            if (is_string($rawLine) && Str::doesContain($rawLine, self::ALLOWED_DIRECTIVE)) {
                $errors[] = self::processLine($rawLine, $lineIndex + 1);
            }
        }

        return $errors;
    }

    /**
     * @throws RuntimeException
     */
    private static function processLine(string $rawLine, int $lineNumber): ?IdentifierRuleError
    {
        self::$allowedDirectivePregQuoted ??= preg_quote(self::ALLOWED_DIRECTIVE);

        $directive = self::$allowedDirectivePregQuoted;

        if (Str::match($rawLine, '/(?:\/\/|\/\*|\*|#).*' . $directive . '/') === []) {
            return null;
        }

        $actual = Str::match($rawLine, '/' . $directive . self::DIRECTIVE_SUFFIX_PATTERN . '/')[0] ?? self::ALLOWED_DIRECTIVE;

        if ($actual !== self::ALLOWED_DIRECTIVE) {
            return self::buildError(
                $actual,
                'unsupported',
                sprintf('Only `%s <rule> [,<rules>...] (<reason>)` is permitted.', self::ALLOWED_DIRECTIVE),
                $lineNumber,
            );
        }

        if (!Str::doesStartWithAny(Str::trim($rawLine), ['//', '/*', '*', '#'])) {
            return self::buildError(
                $actual,
                'inline',
                'Place the comment on a separate line above the code.',
                $lineNumber,
            );
        }

        if (Str::match($rawLine, '/' . $directive . self::RULES_PATTERN . '/') === []
            || Str::match($rawLine, '/' . $directive . self::RULES_PATTERN . self::DESCRIPTION_PATTERN . '/') !== []) {
            return null;
        }

        return self::buildError(
            $actual,
            'undescribed',
            'Include a description explaining why the directive is necessary.',
            $lineNumber,
        );
    }

    /**
     * @throws RuntimeException
     */
    private static function buildError(
        string $directive,
        string $problem,
        string $action,
        int $line,
    ): IdentifierRuleError {
        return self::buildRuleError(sprintf(
            'Unexpected %s `%s` comment. %s',
            $problem,
            $directive,
            $action,
        ), $line, false);
    }
}
