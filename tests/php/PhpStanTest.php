<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests;

use Brnshkr\Config\Json;
use JsonException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

/**
 * @internal
 */
#[CoversNothing]
final class PhpStanTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @throws DirectoryNotFoundException
     * @throws JsonException
     * @throws RuntimeException
     */
    public function testExpectedPhpstanConfig(): void
    {
        $config = include __DIR__ . '/../../conf/phpstan.dist.php';

        self::assertIsArray($config);
        self::assertArrayHasKey('parameters', $config);
        self::assertIsArray($config['parameters']);
        unset($config['parameters']['editorUrl']);

        $result = Json::encode($config);

        $this->assertMatchesJsonSnapshot($result);
    }
}
