<?php

declare(strict_types=1);

namespace Brnshkr\Config\PhpStan\Rule;

use Brnshkr\Config\PhpStan\Rule\Trait\RuleTrait;
use Brnshkr\Config\Str;
use Override;
use PhpParser\Node;
use PhpParser\Node\Const_ as ConstNode;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Const_ as ConstStmt;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use RuntimeException;

use function array_filter;
use function array_map;
use function array_values;
use function sprintf;

/**
 * @api
 *
 * @implements Rule<NodeAbstract>
 */
final readonly class ApiOrInternalAnnotationRule implements Rule
{
    use RuleTrait;

    private const string KIND_CLASS    = 'Class';
    private const string KIND_FUNCTION = 'Function';
    private const string KIND_CONSTANT = 'Constant';

    #[Override]
    public function getNodeType(): string
    {
        return NodeAbstract::class;
    }

    /**
     * @throws RuntimeException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        return array_values(array_filter(
            match (true) {
                $node instanceof Class_    => [self::processClass($node)],
                $node instanceof Function_ => [self::processFunction($node)],
                $node instanceof ConstStmt => self::processGlobalConst($node),
                default                    => [],
            },
            static fn (?IdentifierRuleError $identifierRuleError): bool => $identifierRuleError instanceof IdentifierRuleError,
        ));
    }

    /**
     * @throws RuntimeException
     */
    private static function processClass(Class_ $class): ?IdentifierRuleError
    {
        return ($class->isAnonymous() || self::hasRequiredAnnotation($class))
            ? null
            : self::buildError(self::KIND_CLASS, (string) $class->name);
    }

    /**
     * @throws RuntimeException
     */
    private static function processFunction(Function_ $function): ?IdentifierRuleError
    {
        return self::hasRequiredAnnotation($function)
            ? null
            : self::buildError(self::KIND_FUNCTION, $function->name->toString());
    }

    /**
     * @return list<IdentifierRuleError>
     *
     * @throws RuntimeException
     */
    private static function processGlobalConst(ConstStmt $constStmt): array
    {
        if (self::hasRequiredAnnotation($constStmt)) {
            return [];
        }

        return array_values(array_map(
            static fn (ConstNode $constNode): IdentifierRuleError => self::buildError(
                self::KIND_CONSTANT,
                $constNode->name->toString(),
            ),
            $constStmt->consts,
        ));
    }

    private static function hasRequiredAnnotation(NodeAbstract $nodeAbstract): bool
    {
        return Str::match($nodeAbstract->getDocComment()?->getText() ?? '', '/\*\s+@(internal|api)\b/') !== [];
    }

    /**
     * @phpstan-param self::KIND_* $kind
     *
     * @throws RuntimeException
     */
    private static function buildError(string $kind, string $name): IdentifierRuleError
    {
        return self::buildRuleError(sprintf(
            '%s `%s` must be annotated with either @internal or @api.',
            $kind,
            $name,
        ));
    }
}
