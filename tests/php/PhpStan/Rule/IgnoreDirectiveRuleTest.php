<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests\PhpStan\Rule;

use Brnshkr\Config\PhpStan\Rule\IgnoreDirectiveRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

use function sprintf;

/**
 * @internal
 *
 * @extends RuleTestCase<IgnoreDirectiveRule>
 */
#[CoversNothing]
final class IgnoreDirectiveRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../Fixtures/IgnoreDirectiveRuleFixture.php'], [
            [sprintf('Unexpected undescribed `%s` comment. Include a description explaining why the directive is necessary.', '@phpstan-ignore'), 9],
            ['No error with identifier someRule is reported on line 10.', 10],
            ['No error with identifier someRule is reported on line 12.', 12],
            [sprintf('Unexpected inline `%s` comment. Place the comment on a separate line above the code.', '@phpstan-ignore'), 12],
            ['No error to ignore is reported on line 14.', 14],
            [sprintf('Unexpected unsupported `%s` comment. Only `@phpstan-ignore <rule> [,<rules>...] (<reason>)` is permitted.', '@phpstan-ignore-line'), 14],
            [sprintf('Unexpected unsupported `%s` comment. Only `@phpstan-ignore <rule> [,<rules>...] (<reason>)` is permitted.', '@phpstan-ignore-next-line'), 17],
            ['No error to ignore is reported on line 18.', 18],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new IgnoreDirectiveRule();
    }
}
