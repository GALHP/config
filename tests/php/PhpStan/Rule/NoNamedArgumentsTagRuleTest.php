<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests\PhpStan\Rule;

use Brnshkr\Config\PhpStan\Rule\NoNamedArgumentsTagRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

use function sprintf;

/**
 * @internal
 *
 * @extends RuleTestCase<NoNamedArgumentsTagRule>
 */
#[CoversNothing]
final class NoNamedArgumentsTagRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../Fixtures/NoNamedArgumentsTagRuleFixture.php'], [
            [sprintf('Class `%s` must be annotated with @no-named-arguments.', 'ClassWithoutTag'), 23],
            [sprintf('Method `%s` must be annotated with @no-named-arguments.', 'ClassWithoutTag::methodWithoutTag'), 25],
            [sprintf('Function `%s` must be annotated with @no-named-arguments.', 'functionWithoutTag'), 48],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new NoNamedArgumentsTagRule();
    }
}
