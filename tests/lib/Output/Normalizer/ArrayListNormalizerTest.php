<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Output\Normalizer;

use Ibexa\Rest\Output\Generator\Data\ArrayList;
use Ibexa\Rest\Output\Generator\Data\DataObjectInterface;
use Ibexa\Rest\Output\Normalizer\ArrayListNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ArrayListNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizer = new ArrayListNormalizer();

        $mockedInnerNormalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->setNormalizer($mockedInnerNormalizer);
        $mockedInnerNormalizer->method('normalize')->willReturnArgument(0);

        $list = new ArrayList('foo', $this->createMock(DataObjectInterface::class));
        $list->append('bar');

        $result = $normalizer->normalize($list);

        self::assertSame(['bar'], $result);
    }

    public function testNormalizeWithArrayAppend(): void
    {
        $normalizer = new ArrayListNormalizer();

        $mockedInnerNormalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->setNormalizer($mockedInnerNormalizer);
        $mockedInnerNormalizer->method('normalize')->willReturnArgument(0);

        $list = new ArrayList('test', $this->createMock(DataObjectInterface::class));
        $list->append(['foo' => 'bar']);
        $list->append(['foo' => 'zzz']);

        $result = $normalizer->normalize($list);

        self::assertSame(['foo' => ['bar', 'zzz']], $result);
    }
}
