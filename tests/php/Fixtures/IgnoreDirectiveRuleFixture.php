<?php

// lint-file

declare(strict_types=1);

namespace Brnshkr\Config\Tests\Fixtures;

// @phpstan-ignore someRule
$a = 1;

$b = 2; // @phpstan-ignore someRule (reason)

// @phpstan-ignore-line someRule (reason)
$c = 3;

// @phpstan-ignore-next-line someRule (reason)
$d = 4;
