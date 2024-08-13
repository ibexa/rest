<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language as ApiLanguage;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\LanguageList;
use Symfony\Component\HttpFoundation\Response;
use Traversable;

#[Get(
    uriTemplate: '/languages',
    openapi: new Model\Operation(
        extensionProperties: [
            'x-visib' => '555'
        ],
        tags: [
            'Language attr on Controller',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                description: 'If set, the list is returned in XML or JSON format.',
                required: true,
                in: 'header',
                schema: [
                    'description' => 'If set, the list is returned in XML or JSON format.',
                    'example' => 'application/vnd.ibexa.api.LanguageList+xml
application/vnd.ibexa.api.LanguageList+json
',
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
                        'example' => <<<XML
                        <?xml version="1.0" encoding="UTF-8"?>
                        <LanguageList media-type="application/vnd.ibexa.api.LanguageList+xml" href="/api/ibexa/v2/languages">
                            <Language media-type="application/vnd.ibexa.api.Language+xml" href="/api/ibexa/v2/languages/eng-GB">
                                <languageId>2</languageId>
                                <languageCode>eng-GB</languageCode>
                                <name>English (United Kingdom)</name>
                            </Language>
                            <Language href="/api/ibexa/v2/languages/pol-PL" media-type="application/vnd.ibexa.api.Language+xml">
                                <languageId>4</languageId>
                                <languageCode>pol-PL</languageCode>
                                <name>Polish (polski)</name>
                            </Language>
                        </LanguageList>
                        XML
                    ],
                    'application/vnd.ibexa.api.LanguageList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LanguageListWrapper',
                        ],
                        'example' => [
                            'LanguageList' => [
                                '_media-type' => 'application/vnd.ibexa.api.LanguageList+json',
                                '_href' => '/api/ibexa/v2/languages',
                                'Language' => [
                                    [
                                        '_media-type' => 'application/vnd.ibexa.api.Language+json',
                                        '_href' => '/api/ibexa/v2/languages/eng-GB',
                                        'languageId' => 2,
                                        'languageCode' => 'eng-GB',
                                        'name' => 'English (United Kingdom)',
                                    ],
                                    [
                                        '_media-type' => 'application/vnd.ibexa.api.Language+json',
                                        '_href' => '/api/ibexa/v2/languages/pol-PL',
                                        'languageId' => 4,
                                        'languageCode' => 'pol-PL',
                                        'name' => 'Polish (polski)',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        summary: 'Language list',
        description: 'Lists languages',
    ),
    name: 'languages',
)]
#[Get(
    uriTemplate: '/languages/{code}',
    openapi: new Model\Operation(
        tags: [
            'Language attr on Controller',
        ],
        parameters: [
            new Model\Parameter(
                name: 'code',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Accept',
                description: 'If set, the language is returned in XML or JSON format.',
                required: true,
                in: 'header',
                schema: [
                    'description' => 'If set, the language is returned in XML or JSON format.',
                    'example' => 'application/vnd.ibexa.api.Language+xml
application/vnd.ibexa.api.Language+json
',
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.Language+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Language',
                        ],
                        'example' => <<<XML
                          <?xml version="1.0" encoding="UTF-8"?>
                          <Language media-type="application/vnd.ibexa.api.Language+xml" href="/api/ibexa/v2/languages/eng-GB">
                           <languageId>2</languageId>
                           <languageCode>eng-GB</languageCode>
                           <name>English (United Kingdom)</name>
                          </Language>
                        XML
                    ],
                    'application/vnd.ibexa.api.Language+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LanguageWrapper',
                        ],
                        'example' => [
                            'Language' => [
                                '_media-type' => 'application/vnd.ibexa.api.Language+json',
                                '_href' => '/api/ibexa/v2/languages/eng-GB',
                                'languageId' => '2',
                                'languageCode' => 'eng-GB',
                                'name' => 'English (United Kingdom)',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        summary: 'Get language',
    ),
    name: 'languages_code',
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
