<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest;

use Ibexa\Contracts\Rest\FieldTypeProcessor;
use Ibexa\Rest\FieldTypeProcessorRegistry;
use Ibexa\Tests\Rest\Server\BaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

class FieldTypeProcessorRegistryTest extends BaseTest
{
    public function testRegisterProcessor(): void
    {
        $registry = new FieldTypeProcessorRegistry();

        $processor = $this->getAProcessorMock();

        $registry->registerProcessor('my-type', $processor);

        self::assertTrue($registry->hasProcessor('my-type'));
    }

    public function testRegisterMultipleProcessors(): void
    {
        $registry = new FieldTypeProcessorRegistry();

        $processorA = $this->getAProcessorMock();
        $processorB = $this->getAProcessorMock();

        $registry->registerProcessor('my-type', $processorA);
        $registry->registerProcessor('your-type', $processorB);

        self::assertTrue($registry->hasProcessor('my-type'));
        self::assertTrue($registry->hasProcessor('your-type'));
    }

    public function testHasProcessorFailure(): void
    {
        $registry = new FieldTypeProcessorRegistry();

        self::assertFalse($registry->hasProcessor('my-type'));
    }

    public function testGetProcessor(): void
    {
        $registry = new FieldTypeProcessorRegistry();

        $processor = $this->getAProcessorMock();

        $registry->registerProcessor('my-type', $processor);

        self::assertSame(
            $processor,
            $registry->getProcessor('my-type')
        );
    }

    public function testGetProcessorNotFoundException(): void
    {
        $this->expectException(RuntimeException::class);

        $registry = new FieldTypeProcessorRegistry();

        $registry->getProcessor('my-type');
    }

    public function testRegisterProcessorsOverwrite(): void
    {
        $registry = new FieldTypeProcessorRegistry();

        $processorA = $this->getAProcessorMock();
        $processorB = $this->getAProcessorMock();

        $registry->registerProcessor('my-type', $processorA);
        $registry->registerProcessor('my-type', $processorB);

        self::assertSame(
            $processorB,
            $registry->getProcessor('my-type')
        );
    }

    protected function getAProcessorMock(): FieldTypeProcessor & MockObject
    {
        return $this->createMock(FieldTypeProcessor::class);
    }
}
