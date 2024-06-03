<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser\Strategy;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Rest\Server\Input\Parser\Strategy\ContentTypePostCopyOperation;
use Ibexa\Rest\Server\Input\Parser\Strategy\ContentTypePostOperationFactory;
use Ibexa\Rest\Server\Input\Parser\Strategy\ContentTypePostOperationStrategyInterface;
use Ibexa\Rest\Server\Values\ContentTypePostOperationValue;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ContentTypePostOperationFactoryTest extends KernelTestCase
{
    public function testGetOperationStrategyForCopy(): void
    {
        $factory = new ContentTypePostOperationFactory($this->getStrategies());

        $operationValue = new ContentTypePostOperationValue('copy');

        $strategy = $factory->getOperationStrategy($operationValue);

        self::assertInstanceOf(ContentTypePostCopyOperation::class, $strategy);
    }

    /**
     * @phpstan-return iterable<\Ibexa\Rest\Server\Input\Parser\Strategy\ContentTypePostOperationStrategyInterface>
     */
    public function getStrategies(): iterable
    {
        yield $this->createMock(
            ContentTypePostOperationStrategyInterface::class,
        );
        yield new ContentTypePostCopyOperation(
            $this->createMock(ContentTypeService::class),
        );
    }
}
