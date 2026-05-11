<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests\Fixtures\Rule;

use ArrayAccess;
use Override;

/**
 * @internal
 */
final class BoolishPrefixFixtureClass
{
    public const bool IS_VALID = true;
    public const bool ENABLED  = false;

    public bool $isActive = true;

    public bool $active = false;

    public function __construct(
        public bool $isReady = true,
        public bool $ready = false,
        bool $isEnabled = true,
        bool $enabled = false,
    ) {}

    public function isValid(): bool
    {
        return true;
    }

    public function validate(): bool
    {
        return true;
    }

    public function hasItems(): bool
    {
        return true;
    }

    public function checkItems(): bool
    {
        return false;
    }

    public function canAccess(): bool
    {
        return true;
    }

    public function doesExist(): bool
    {
        return true;
    }

    public function wasDeleted(): bool
    {
        return true;
    }

    public function didSucceed(): bool
    {
        return true;
    }

    public function doProcess(): bool
    {
        return true;
    }

    public function asBoolean(): bool
    {
        return true;
    }

    public function getStatus(): string
    {
        return 'ok';
    }

    public function process(bool $isForced, bool $forced): void {}
}

/**
 * @internal
 */
interface BoolishPrefixLockSourceInterface
{
    public function compute(): bool;
}

/**
 * @internal
 */
trait BoolishPrefixLockSourceTrait
{
    public function evaluate(): bool
    {
        return false;
    }
}

/**
 * @internal
 */
class BoolishPrefixLockSourceParent
{
    public function __construct(public bool $value = true) {}

    public function getResult(bool $forced): bool
    {
        return $forced;
    }
}

/**
 * @internal
 */
final class BoolishPrefixInterfaceImplFixture implements BoolishPrefixLockSourceInterface
{
    public function compute(): bool
    {
        return true;
    }
}

/**
 * @internal
 */
final class BoolishPrefixOverrideFixture extends BoolishPrefixLockSourceParent
{
    public function __construct(public bool $value = false)
    {
        parent::__construct($value);
    }

    #[Override]
    public function getResult(bool $forced): bool
    {
        $result = true;

        return $result && $forced;
    }
}

/**
 * @internal
 */
final class BoolishPrefixTraitUserFixture
{
    use BoolishPrefixLockSourceTrait;

    public function evaluate(): bool
    {
        return true;
    }
}

/**
 * @internal
 */
final class BoolishPrefixMagicFixture
{
    public function __isset(string $name): bool
    {
        return false;
    }

    public function __construct(bool $magic) {}
}

/**
 * @internal
 *
 * @implements ArrayAccess<int, mixed>
 */
final class BoolishPrefixExternalImplFixture implements ArrayAccess
{
    public function offsetExists(mixed $offset): bool
    {
        return false;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void {}

    public function offsetUnset(mixed $offset): void {}
}

/**
 * @internal
 */
function isEnabled(): bool
{
    return true;
}

/**
 * @internal
 */
function checkEnabled(): bool
{
    return true;
}
