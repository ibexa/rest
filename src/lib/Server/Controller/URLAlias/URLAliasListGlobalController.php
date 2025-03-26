<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\URLAlias;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/urlaliases',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'List global URL aliases',
        description: 'Returns the list of global URL aliases.',
        tags: [
            'Url Alias',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the URL alias list contains only references and is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the list of URL aliases.',
                'content' => [
                    'application/vnd.ibexa.api.UrlAliasRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UrlAliasRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlaliases/GET/UrlAliasRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UrlAliasRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UrlAliasRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlaliases/GET/UrlAliasRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read URL aliases.',
            ],
        ],
    ),
)]
class URLAliasListGlobalController extends RestController
{
    public function __construct(
        protected URLAliasService $urlAliasService,
        protected LocationService $locationService
    ) {
    }

    /**
     * Returns the list of global URL aliases.
     */
    public function listGlobalURLAliases(): Values\URLAliasRefList
    {
        $globalAliasesArray = [];
        foreach ($this->urlAliasService->listGlobalAliases() as $alias) {
            $globalAliasesArray[] = $alias;
        }

        return new Values\URLAliasRefList(
            $globalAliasesArray,
            $this->router->generate('ibexa.rest.list_global_url_aliases')
        );
    }
}
