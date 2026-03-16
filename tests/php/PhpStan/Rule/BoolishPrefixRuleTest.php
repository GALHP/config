<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests\PhpStan\Rule;

use Brnshkr\Config\PhpStan\Rule\BoolishPrefixRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

use function implode;
use function sprintf;

/**
 * @internal
 *
 * @extends RuleTestCase<BoolishPrefixRule>
 */
#[CoversNothing]
final class BoolishPrefixRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $prefixList = implode(', ', BoolishPrefixRule::BOOLISH_PREFIXES);

        $this->analyse([__DIR__ . '/../../Fixtures/BoolishPrefixRuleFixture.php'], [
            [sprintf('Constant name `%s` must have one of the following prefixes: %s', 'ENABLED', $prefixList), 13],
            [sprintf('Property name `%s` must have one of the following prefixes: %s', 'active', $prefixList), 17],
            [sprintf('Property name `%s` must have one of the following prefixes: %s', 'ready', $prefixList), 21],
            [sprintf('Parameter name `%s` must have one of the following prefixes: %s', 'enabled', $prefixList), 23],
            [sprintf('Method name `%s` must have one of the following prefixes: %s', 'validate', $prefixList), 31],
            [sprintf('Method name `%s` must have one of the following prefixes: %s', 'checkItems', $prefixList), 41],
            [sprintf('Parameter name `%s` must have one of the following prefixes: %s', 'forced', $prefixList), 81],
            [sprintf('Function name `%s` must have one of the following prefixes: %s', 'checkEnabled', $prefixList), 95],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new BoolishPrefixRule();
    }
}
