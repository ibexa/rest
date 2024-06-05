<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest;

use Ibexa\Contracts\Rest\FieldTypeProcessor;
use Ibexa\Rest\FieldTypeProcessorRegistry;
use Ibexa\Tests\Rest\Server\BaseTest;
use RuntimeException;

class FieldTypeProcessorRegistryTest extends BaseTest
{
    public function testRegisterProcessor()
    {
        $registry = new FieldTypeProcessorRegistry();

        $processor = $this->getAProcessorMock();

        $registry->registerProcessor('my-type', $processor);

        self::assertTrue($registry->hasProcessor('my-type'));
    }

    public function testRegisterMultipleProcessors()
    {
        $registry = new FieldTypeProcessorRegistry();

        $processorA = $this->getAProcessorMock();
        $processorB = $this->getAProcessorMock();

        $registry->registerProcessor('my-type', $processorA);
        $registry->registerProcessor('your-type', $processorB);

        self::assertTrue($registry->hasProcessor('my-type'));
        self::assertTrue($registry->hasProcessor('your-type'));
    }

    public function testHasProcessorFailure()
    {
        $registry = new FieldTypeProcessorRegistry();

        self::assertFalse($registry->hasProcessor('my-type'));
    }

    public function testGetProcessor()
    {
        $registry = new FieldTypeProcessorRegistry();

        $processor = $this->getAProcessorMock();

        $registry->registerProcessor('my-type', $processor);

        self::assertSame(
            $processor,
            $registry->getProcessor('my-type')
        );
    }

    public function testGetProcessorNotFoundException()
    {
        $this->expectException(RuntimeException::class);

        $registry = new FieldTypeProcessorRegistry();

        $registry->getProcessor('my-type');
    }

    public function testRegisterProcessorsOverwrite()
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

    /**
     * Get FieldTypeProcessor mock object.
     *
     * @return \Ibexa\Contracts\Rest\FieldTypeProcessor
     */
    protected function getAProcessorMock()
    {
        return $this->createMock(FieldTypeProcessor::class);
    }
}
