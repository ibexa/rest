<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output\Normalizer;

use Ibexa\Rest\Output\Normalizer\JsonSerializableNormalizer;
use Ibexa\Tests\Rest\Output\MoneyObject;
use PHPUnit\Framework\TestCase;

class ArrayNormalizerTest extends TestCase
{
    public function testNormalizeArray(): void
    {
        $normalizer = new JsonSerializableNormalizer();
        $money = new MoneyObject(100, 'EUR');

        $result = $normalizer->normalize($money);

        self::assertIsArray($result);

        self::assertSame('100', $result['amount']);
        self::assertSame('EUR', $result['currency']);
    }
}
