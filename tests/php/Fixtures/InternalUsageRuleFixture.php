<?php

declare(strict_types=1);

namespace External\Consumer;

use Brnshkr\Config\Tests\Fixtures\Internal\InternalClass;
use Brnshkr\Config\Tests\Fixtures\Internal\ScopedInternalClass;

function consumeInternalClass(): void
{
    $obj = new InternalClass();
    $obj->doSomething();
    $val   = $obj->value;
    $const = InternalClass::SOME_CONST;
    InternalClass::staticMethod();
}

function consumeScopedInternalClass(): void
{
    $obj = new ScopedInternalClass();
}
