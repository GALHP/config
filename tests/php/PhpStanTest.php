<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests;

use Brnshkr\Config\Json;
use Brnshkr\Config\PhpStanConfig;
use JsonException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

use function is_array;

/**
 * @internal
 */
#[CoversNothing]
final class PhpStanTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @throws JsonException
     */
    public function testExpectedPhpstanConfig(): void
    {
        $config = PhpStanConfig::get();

        if (is_array($config['parameters'] ?? null)) {
            unset($config['parameters']['editorUrl']);
        }

        $result = Json::encode($config);

        $this->assertMatchesJsonSnapshot($result);
    }
}
