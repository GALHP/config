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

        $this->analyse([__DIR__ . '/../../Fixtures/Rule/BoolishPrefixRuleFixture.php'], [
            [sprintf('Constant name `%s` must have one of the following prefixes: %s', 'ENABLED', $prefixList), 16],
            [sprintf('Property name `%s` must have one of the following prefixes: %s', 'active', $prefixList), 20],
            [sprintf('Property name `%s` must have one of the following prefixes: %s', 'ready', $prefixList), 24],
            [sprintf('Parameter name `%s` must have one of the following prefixes: %s', 'enabled', $prefixList), 26],
            [sprintf('Method name `%s` must have one of the following prefixes: %s', 'validate', $prefixList), 34],
            [sprintf('Method name `%s` must have one of the following prefixes: %s', 'checkItems', $prefixList), 44],
            [sprintf('Parameter name `%s` must have one of the following prefixes: %s', 'forced', $prefixList), 84],
            [sprintf('Method name `%s` must have one of the following prefixes: %s', 'compute', $prefixList), 92],
            [sprintf('Property name `%s` must have one of the following prefixes: %s', 'value', $prefixList), 111],
            [sprintf('Method name `%s` must have one of the following prefixes: %s', 'getResult', $prefixList), 113],
            [sprintf('Parameter name `%s` must have one of the following prefixes: %s', 'forced', $prefixList), 113],
            [sprintf('Method name `%s` must have one of the following prefixes: %s', 'compute', $prefixList), 124],
            [sprintf('Property name `%s` must have one of the following prefixes: %s', 'value', $prefixList), 135],
            [sprintf('Method name `%s` must have one of the following prefixes: %s', 'getResult', $prefixList), 140],
            [sprintf('Parameter name `%s` must have one of the following prefixes: %s', 'forced', $prefixList), 141],
            [sprintf('Variable name `%s` must have one of the following prefixes: %s', 'result', $prefixList), 143],
            [sprintf('Method name `%s` must have one of the following prefixes: %s', 'evaluate', $prefixList), 156],
            [sprintf('Parameter name `%s` must have one of the following prefixes: %s', 'magic', $prefixList), 172],
            [sprintf('Function name `%s` must have one of the following prefixes: %s', 'checkEnabled', $prefixList), 208],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new BoolishPrefixRule();
    }
}
