<?php

declare(strict_types=1);

namespace Brnshkr\Config\PhpStan\Rule;

use Brnshkr\Config\PhpStan\Rule\Trait\RuleTrait;
use Brnshkr\Config\Str;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use RuntimeException;

use function array_filter;
use function array_values;
use function sprintf;

/**
 * @api
 *
 * @no-named-arguments
 *
 * @implements Rule<NodeAbstract>
 */
final readonly class NoNamedArgumentsTagRule implements Rule
{
    use RuleTrait;

    private const string KIND_CLASS    = 'Class';
    private const string KIND_FUNCTION = 'Function';
    private const string KIND_METHOD   = 'Method';

    #[Override]
    public function getNodeType(): string
    {
        return NodeAbstract::class;
    }

    /**
     * @return list<IdentifierRuleError>
     *
     * @throws RuntimeException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        return array_values(array_filter(
            match (true) {
                $node instanceof Class_    => self::processClass($node),
                $node instanceof Function_ => [self::processFunction($node)],
                default                    => [],
            },
            static fn (?IdentifierRuleError $identifierRuleError): bool => $identifierRuleError instanceof IdentifierRuleError,
        ));
    }

    /**
     * @return list<?IdentifierRuleError>
     *
     * @throws RuntimeException
     */
    private static function processClass(Class_ $class): array
    {
        if ($class->isAnonymous()) {
            return [];
        }

        $errors = [];

        $hasInternalTag         = self::hasInternalTag($class);
        $hasNoNamedArgumentsTag = self::hasNoNamedArgumentsTag($class);

        if (!$hasInternalTag && !$hasNoNamedArgumentsTag) {
            $errors[] = self::buildError(
                self::KIND_CLASS,
                self::getClassName($class),
                $class->getStartLine(),
            );
        }

        foreach ($class->getMethods() as $classMethod) {
            if ($hasInternalTag) {
                continue;
            }

            if ($hasNoNamedArgumentsTag) {
                continue;
            }

            if (self::hasInternalTag($classMethod)) {
                continue;
            }

            if (self::hasNoNamedArgumentsTag($classMethod)) {
                continue;
            }

            $errors[] = self::buildError(
                self::KIND_METHOD,
                self::getClassName($class) . '::' . $classMethod->name->toString(),
                $classMethod->getStartLine(),
            );
        }

        return $errors;
    }

    /**
     * @throws RuntimeException
     */
    private static function processFunction(Function_ $function): ?IdentifierRuleError
    {
        return self::hasInternalTag($function) || self::hasNoNamedArgumentsTag($function)
            ? null
            : self::buildError(
                self::KIND_FUNCTION,
                $function->name->toString(),
                $function->getStartLine(),
            );
    }

    private static function hasInternalTag(NodeAbstract $nodeAbstract): bool
    {
        return Str::match($nodeAbstract->getDocComment()?->getText() ?? '', '/\*\s+@internal\b/') !== [];
    }

    private static function hasNoNamedArgumentsTag(NodeAbstract $nodeAbstract): bool
    {
        return Str::match($nodeAbstract->getDocComment()?->getText() ?? '', '/\*\s+@no-named-arguments\b/') !== [];
    }

    /**
     * @phpstan-param self::KIND_* $kind
     *
     * @throws RuntimeException
     */
    private static function buildError(string $kind, string $name, int $line): IdentifierRuleError
    {
        return self::buildRuleError(sprintf(
            '%s `%s` must be annotated with @no-named-arguments.',
            $kind,
            $name,
        ), $line);
    }
}
