<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests\PhpStan\Rule;

use Brnshkr\Config\PhpStan\Rule\ApiOrInternalTagRule;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

use function sprintf;

/**
 * @internal
 *
 * @extends RuleTestCase<ApiOrInternalTagRule>
 */
#[CoversNothing]
final class ApiOrInternalTagRuleTest extends RuleTestCase
{
    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/../../Fixtures/ApiOrInternalTagRuleFixture.php'], [
            [sprintf('Class `%s` must be annotated with either @internal or @api.', 'ClassWithoutTag'), 17],
            [sprintf('Function `%s` must be annotated with either @internal or @api.', 'functionWithoutTag'), 29],
            [sprintf('Constant `%s` must be annotated with either @internal or @api.', 'CONST_WITHOUT_TAG_A'), 41],
            [sprintf('Constant `%s` must be annotated with either @internal or @api.', 'CONST_WITHOUT_TAG_B'), 42],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new ApiOrInternalTagRule();
    }
}
