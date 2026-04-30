<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests\Fixtures;

/**
 * @no-named-arguments
 */
final class ClassWithNoNamedArgumentsTag
{
    public function someMethod(string $argument): void {}
}

/**
 * @internal
 */
final class ClassWithInternalTag
{
    public function someMethod(string $argument): void {}
}

final class ClassWithoutTag
{
    public function methodWithoutTag(string $argument): void {}

    /**
     * @no-named-arguments
     */
    public function methodWithNoNamedArgumentsTag(): void {}

    /**
     * @internal
     */
    public function methodWithInternalTag(): void {}
}

final class ClassWithoutTagNoParameters
{
    public function methodWithoutParameters(): void {}
}

final class ClassWithoutTagOnlyPrivateParameters
{
    private function privateMethod(string $argument): void {}
}

final class ClassWithoutTagPrivateAndPublicParameters
{
    private function privateMethod(string $argument): void {}

    public function publicMethod(string $argument): void {}
}

final class ClassWithoutTagConstructorParameters
{
    public function __construct(private readonly string $name) {}
}

/**
 * @no-named-arguments
 */
function functionWithNoNamedArgumentsTag(string $argument): void {}

/**
 * @internal
 */
function functionWithInternalTag(string $argument): void {}

function functionWithoutTag(string $argument): void {}

function functionWithoutTagNoParameters(): void {}

$anonymousClass = new class {
    public function anonymousMethod(): void {}
};

$anonymousClassWithParameters = new class {
    public function anonymousMethod(string $argument): void {}
};

/**
 * @no-named-arguments
 */
interface InterfaceWithNoNamedArgumentsTag
{
    public function someMethod(string $argument): void;
}

/**
 * @internal
 */
interface InterfaceWithInternalTag
{
    public function someMethod(string $argument): void;
}

interface InterfaceWithoutTag
{
    public function someMethod(string $argument): void;
}

interface InterfaceWithoutTagNoParameters
{
    public function someMethod(): void;
}

/**
 * @no-named-arguments
 */
enum EnumWithNoNamedArgumentsTag
{
    case A;

    public function someMethod(string $argument): void {}
}

/**
 * @internal
 */
enum EnumWithInternalTag
{
    case A;

    public function someMethod(string $argument): void {}
}

enum EnumWithoutTag
{
    case A;

    public function someMethod(string $argument): void {}
}

enum EnumWithoutTagNoParameters
{
    case A;
}

/**
 * @no-named-arguments
 */
trait TraitWithNoNamedArgumentsTag
{
    public function someMethod(string $argument): void {}
}

/**
 * @internal
 */
trait TraitWithInternalTag
{
    public function someMethod(string $argument): void {}
}

trait TraitWithoutTag
{
    public function someMethod(string $argument): void {}
}

trait TraitWithoutTagNoParameters
{
    public function someMethod(): void {}
}
