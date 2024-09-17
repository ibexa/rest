<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\URLAlias;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/urlaliases',
    name: 'List global URL aliases',
    openapi: new Model\Operation(
        summary: 'Returns the list of global URL aliases.',
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
     *
     * @return \Ibexa\Rest\Server\Values\URLAliasRefList
     */
    public function listGlobalURLAliases()
    {
        return new Values\URLAliasRefList(
            $this->urlAliasService->listGlobalAliases(),
            $this->router->generate('ibexa.rest.list_global_url_aliases')
        );
    }
}
