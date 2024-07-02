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
    public function __construct(private readonly OpenApiFactoryInterface $decorated)
    {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $schemas = new \ArrayObject();
        $schemas['BaseObject'] = [
            'type' => 'object',
            'required' => ['_media-type', '_href'],
            'properties' => [
                '_media-type' => [
                    'type' => 'string',
                ],
                '_href' => [
                    'type' => 'string',
                ],
            ],
        ];
        $schemas['Language'] = [
            'allOf' => [
                [
                    '$ref' => '#/components/schemas/BaseObject',
                ],
                [
                    'type' => 'object',
                    'required' => ['id', 'languageCode', 'name', 'enabled'],
                    'properties' => [
                        'id' => [
                            'description' => 'The language ID (auto generated).',
                            'type' => 'integer',
                        ],
                        'languageCode' => [
                            'description' => 'The languageCode code.',
                            'type' => 'string',
                        ],
                        'name' => [
                            'description' => 'Human readable name of the language.',
                            'type' => 'string',
                        ],
                        'enabled' => [
                            'description' => 'Indicates if the language is enabled or not.',
                            'type' => 'boolean',
                        ],
                    ],
                ],
            ],
        ];
        $schemas['LanguageList'] = [
            'description' => ' List of languages.',
            'type' => 'array',
            'items' => [
                '$ref' => '#/components/schemas/Language',
            ],
        ];

        $components = $openApi->getComponents();
        $components = $components->withSchemas($schemas);

        $openApi = $openApi->withComponents($components);

        return $openApi;
    }
}
