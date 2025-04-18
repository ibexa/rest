<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\URLWildcard;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/urlwildcards',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'List URL wildcards',
        description: 'Returns a list of URL wildcards.',
        tags: [
            'Url Wildcard',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the URL wildcard is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns a list of URL wildcards.',
                'content' => [
                    'application/vnd.ibexa.api.UrlWildcardList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UrlWildcardList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlwildcards/GET/UrlWildcardList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UrlWildcardList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UrlWildcardListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlwildcards/GET/UrlWildcardList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read URL wildcards.',
            ],
        ],
    ),
)]
class URLWildcardListController extends RestController
{
    public function __construct(
        protected URLWildcardService $urlWildcardService
    ) {
    }

    /**
     * Returns the list of URL wildcards.
     */
    public function listURLWildcards(): Values\URLWildcardList
    {
        $wildcards = $this->urlWildcardService->loadAll();
        $array = [];
        foreach ($wildcards as $wildcard) {
            $array[] = $wildcard;
        }

        return new Values\URLWildcardList(
            $array,
        );
    }
}
