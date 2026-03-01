<?php

declare(strict_types=1);

namespace Brnshkr\Config\PhpStan\Rule\Trait;

use Brnshkr\Config\ComposerJson;
use Brnshkr\Config\Str;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use RuntimeException;

use function lcfirst;
use function preg_replace;
use function sprintf;

/**
 * @internal
 */
trait RuleTrait
{
    /**
     * @throws RuntimeException
     */
    private static function buildRuleError(string $message): IdentifierRuleError
    {
        $className = Str::afterLast(self::class, '\\');
        // @phpstan-ignore-next-line symplify.forbiddenFuncCall (Avoid using symfony/string here to keep package as lighweight as possible)
        $ruleName = lcfirst(preg_replace('/Rule$/', '', $className) ?: 'unknown');

        $identifier = sprintf(
            '%s.%s',
            ComposerJson::forThisLibrary()->getPackageOrganization(),
            $ruleName,
        );

        return RuleErrorBuilder::message($message)
            ->identifier($identifier)
            ->build()
        ;
    }
}
