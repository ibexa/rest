<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\URLAlias;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Contracts\Core\Repository\Values\Content\URLAlias as ApiUrlAlias;
use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/urlaliases/{urlAliasId}',
    name: 'Get URL alias',
    openapi: new Model\Operation(
        summary: 'Returns the URL alias with the given ID.',
        tags: [
            'Url Alias',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the URL alias is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'urlAliasId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the URL alias.',
                'content' => [
                    'application/vnd.ibexa.api.UrlAlias+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UrlAlias',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlaliases/url_alias_id/GET/UrlAlias.xml.example',
                    ],
                    'application/vnd.ibexa.api.UrlAlias+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UrlAliasWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlaliases/url_alias_id/GET/UrlAlias.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read URL aliases.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The URL alias does not exist.',
            ],
        ],
    ),
)]
class URLAliasLoadByIdController extends RestController
{
    public function __construct(
        protected URLAliasService $urlAliasService,
        protected LocationService $locationService
    ) {
    }

    /**
     * Returns the URL alias with the given ID.
     */
    public function loadURLAlias(mixed $urlAliasId): ApiUrlAlias
    {
        return $this->urlAliasService->load((string)$urlAliasId);
    }
}
