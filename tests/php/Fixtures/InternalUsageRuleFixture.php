<?php

declare(strict_types=1);

namespace External\Consumer;

use Brnshkr\Config\Tests\Fixtures\Internal\InternalClass;
use Brnshkr\Config\Tests\Fixtures\Internal\ScopedInternalClass;

function consumeInternalClass(): void
{
    $object = new InternalClass();

    $object->doSomething();

    $value    = $object->value;
    $constant = InternalClass::SOME_CONSTANT;

    InternalClass::staticMethod();
}

function consumeScopedInternalClass(): void
{
    $object = new ScopedInternalClass();
}
