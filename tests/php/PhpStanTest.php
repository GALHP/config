<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests;

use Brnshkr\Config\Json;
use JsonException;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\DependencyInjection\LoaderFactory;
use PHPStan\File\FileHelper;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

use function getcwd;
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
        $currentWorkingDirectory = getcwd() ?: '.';
        $containerFactory        = new ContainerFactory($currentWorkingDirectory);

        // @phpstan-ignore-next-line phpstanApi.constructor, phpstanApi.method (Not covered by BC)
        $loader = new LoaderFactory(
            // @phpstan-ignore-next-line phpstanApi.constructor (Not covered by BC)
            fileHelper: new FileHelper($currentWorkingDirectory),
            rootDir: $containerFactory->getRootDirectory(),
            currentWorkingDirectory: $containerFactory->getCurrentWorkingDirectory(),
            generateBaselineFile: null,
            expandRelativePaths: [],
        )->createLoader();

        $config = $loader->load($currentWorkingDirectory . '/conf/phpstan.php', null);

        if (is_array($config['parameters'] ?? null)) {
            unset($config['parameters']['editorUrl']);
        }

        $result = Json::encode($config);

        $this->assertMatchesJsonSnapshot($result);
    }
}
