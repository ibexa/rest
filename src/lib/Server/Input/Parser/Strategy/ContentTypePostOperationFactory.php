<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Strategy;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Values\ContentTypePostOperationValue;

final readonly class ContentTypePostOperationFactory
{
    /**
     * @phpstan-param iterable<\Ibexa\Rest\Server\Input\Parser\Strategy\ContentTypePostOperationStrategyInterface> $operationStrategies
     */
    public function __construct(
        private iterable $operationStrategies
    ) {
    }

    public function getOperationStrategy(
        ContentTypePostOperationValue $contentTypePostOperationValue
    ): ContentTypePostOperationStrategyInterface {
        /** @var \Ibexa\Rest\Server\Input\Parser\Strategy\ContentTypePostOperationStrategyInterface $operationStrategy */
        foreach ($this->operationStrategies as $operationStrategy) {
            if ($operationStrategy->supports($contentTypePostOperationValue)) {
                return $operationStrategy;
            }
        }

        throw new Exceptions\Parser(
            sprintf('No valid strategy for %s.', self::class)
        );
    }
}
