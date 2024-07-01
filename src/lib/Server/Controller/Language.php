<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\Metadata\Post;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language as ApiLanguage;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\LanguageList;
use Symfony\Component\HttpFoundation\Response;
use Traversable;

//#[Post(uriTemplate: '/books/{id}/publication',
////    formats: [
////        'csv' =>    ['text/html'],
////    ],
////    input: TestInputDto::class,
////    output: TestAnotherDto::class,
//    name: 'name2B',
//)]
#[Post(
    uriTemplate: '/content/locations/\{locationPath}',
    openapi: new Model\Operation(
        tags:[
            'My-tag',
//            'Myyyyy-tttag'
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'My description',
                'content' => [
                    'application/vnd.ibexa.api.LocationCopyOutput' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'name' => ['type' => 'string'],
                                'description' => ['type' => 'string']
                            ]
                        ],
                        'example' => [
                            'name' => 'Article A',
                            'description' => 'Article A is an article'
                        ]
                    ],
                ],
            ],
            Response::HTTP_CREATED => [],
        ],
        summary: 'Operations on locations',
        description: 'Various operations on locations',
        parameters: [
            new Model\Parameter(
                name: 'locationPath',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.LocationCopyInput' => [
                    'schema' => [
                        '$ref' => "#/components/schemas/Language",
                    ],
//                    'schema' => [
//                        'type' => 'object',
//                        'properties' => [
//                            'namess' => ['type' => 'string'],
//                            'descriptionss' => ['type' => 'string']
//                        ]
//                    ],
//                    'example' => [
//                        'name' => 'Article A',
//                        'description' => 'Article A is an article'
//                    ]
                ],
            ])
        )
    ),
    name: 'locations_operations',
)]
final class Language extends RestController
{
    private LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function listLanguages(): LanguageList
    {
        $languages = $this->languageService->loadLanguages();

        if ($languages instanceof Traversable) {
            $languages = iterator_to_array($languages);
        }

        return new LanguageList($languages);
    }

    public function loadLanguage(string $languageCode): ApiLanguage
    {
        return $this->languageService->loadLanguage($languageCode);
    }
}
