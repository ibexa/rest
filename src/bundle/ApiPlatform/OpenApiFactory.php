<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Rest\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;

final class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated,
        private readonly SchemasCollectionFactory $schemaCollectionFactory,
    ) {
    }

    /**
     * @param array<mixed> $context
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $schemasCollection = $this->schemaCollectionFactory->create();
        $schemas = iterator_to_array($schemasCollection);

        $components = $openApi->getComponents();
        $components = $components->withSchemas(new \ArrayObject($schemas));

        $openApi = $openApi->withComponents($components);

        return $openApi;
    }
}
