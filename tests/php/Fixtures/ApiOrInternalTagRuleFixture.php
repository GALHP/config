<?php

declare(strict_types=1);

namespace Brnshkr\Config\Tests\Fixtures;

/**
 * @api
 */
final class ClassWithApiTag {}

/**
 * @internal
 */
final class ClassWithInternalTag {}

final class ClassWithoutTag {}

/**
 * @api
 */
function functionWithApiTag(): void {}

/**
 * @internal
 */
function functionWithInternalTag(): void {}

function functionWithoutTag(): void {}

/**
 * @api
 */
const CONST_WITH_API_TAG = 'foo';

/**
 * @internal
 */
const CONST_WITH_INTERNAL_TAG = 'bar';

const CONST_WITHOUT_TAG_A = 'baz';
const CONST_WITHOUT_TAG_B = 'qux';

$anonymousClass = new class {};
