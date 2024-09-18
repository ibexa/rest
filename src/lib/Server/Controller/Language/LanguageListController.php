<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\Language;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\LanguageList;
use Symfony\Component\HttpFoundation\Response;
use Traversable;

#[Get(
    uriTemplate: '/languages',
    name: 'Language list',
    openapi: new Model\Operation(
        summary: 'Lists languages',
        tags: [
            'Language',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.LanguageList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LanguageList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/languages/GET/LanguageList.xml.example',
                    ],
                    'application/vnd.ibexa.api.LanguageList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LanguageListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/languages/GET/LanguageList.json.example',
                    ],
                ],
            ],
        ],
    ),
)]
final class LanguageListController extends RestController
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
}
