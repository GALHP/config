<?php

declare(strict_types=1);

namespace Brnshkr\Config\PhpStan\Rule;

use Brnshkr\Config\PhpStan\Rule\Trait\RuleTrait;
use Brnshkr\Config\Str;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use RuntimeException;

use function array_any;
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

    private const string KIND_CLASS     = 'Class';
    private const string KIND_ENUM      = 'Enum';
    private const string KIND_FUNCTION  = 'Function';
    private const string KIND_INTERFACE = 'Interface';
    private const string KIND_TRAIT     = 'Trait';

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
        return array_filter(
            match (true) {
                $node instanceof ClassLike  => [self::processClassLike($node)],
                $node instanceof Function_ => [self::processFunction($node)],
                default                    => [],
            },
            static fn (?IdentifierRuleError $identifierRuleError): bool => $identifierRuleError instanceof IdentifierRuleError,
        );
    }

    /**
     * @throws RuntimeException
     */
    private static function processClassLike(ClassLike $classLike): ?IdentifierRuleError
    {
        if ($classLike instanceof Class_ && $classLike->isAnonymous()) {
            return null;
        }

        if (!self::hasMethodsWithParameters($classLike)) {
            return null;
        }

        if (self::hasInternalTag($classLike)) {
            return null;
        }

        if (self::hasNoNamedArgumentsTag($classLike)) {
            return null;
        }

        return self::buildError(
            self::getKindForClassLike($classLike),
            self::getClassLikeName($classLike),
            $classLike->getStartLine(),
        );
    }

    /**
     * @return self::KIND_*
     */
    private static function getKindForClassLike(ClassLike $classLike): string
    {
        return match (true) {
            $classLike instanceof Enum_      => self::KIND_ENUM,
            $classLike instanceof Interface_ => self::KIND_INTERFACE,
            $classLike instanceof Trait_     => self::KIND_TRAIT,
            default                          => self::KIND_CLASS,
        };
    }

    private static function hasMethodsWithParameters(ClassLike $classLike): bool
    {
        return array_any(
            $classLike->getMethods(),
            static fn (ClassMethod $classMethod): bool => !$classMethod->isPrivate() && $classMethod->getParams() !== [],
        );
    }

    /**
     * @throws RuntimeException
     */
    private static function processFunction(Function_ $function): ?IdentifierRuleError
    {
        if ($function->getParams() === []) {
            return null;
        }

        if (self::hasInternalTag($function)) {
            return null;
        }

        if (self::hasNoNamedArgumentsTag($function)) {
            return null;
        }

        return self::buildError(
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
     * @param self::KIND_* $kind
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
