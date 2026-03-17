<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests\Fixtures;

/**
 * @no-named-arguments
 */
final class ClassWithNoNamedArgs
{
    public function someMethod(): void {}
}

/**
 * @internal
 */
final class ClassWithInternal
{
    public function someMethod(): void {}
}

final class ClassWithoutTag
{
    public function methodWithoutTag(): void {}

    /**
     * @no-named-arguments
     */
    public function methodWithNoNamedArgs(): void {}

    /**
     * @internal
     */
    public function methodWithInternal(): void {}
}

/**
 * @no-named-arguments
 */
function functionWithNoNamedArgs(): void {}

/**
 * @internal
 */
function functionWithInternal(): void {}

function functionWithoutTag(): void {}

$anonymousClass = new class {
    public function anonymousMethod(): void {}
};
