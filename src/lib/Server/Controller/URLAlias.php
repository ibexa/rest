<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

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
#[Post(
    uriTemplate: '/content/urlaliases',
    name: 'Create URL alias',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a URL alias.',
        tags: [
            'Url Alias',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the created URL alias is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The URL alias input schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.UrlAliasCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UrlAliasCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlaliases/POST/UrlAliasCreate.xml.example',
                ],
                'application/vnd.ibexa.api.UrlAliasCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UrlAliasCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlaliases/POST/UrlAliasCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'URL alias created.',
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to create a URL alias.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A URL alias with the same identifier already exists.',
            ],
        ],
    ),
)]
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
#[Delete(
    uriTemplate: '/content/urlaliases/{urlAliasId}',
    name: 'Delete URL alias',
    openapi: new Model\Operation(
        summary: 'Deletes the provided URL alias.',
        tags: [
            'Url Alias',
        ],
        parameters: [
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
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - URL alias deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete a URL alias.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The URL alias does not exist.',
            ],
        ],
    ),
)]
/**
 * URLAlias controller.
 */
class URLAlias extends RestController
{
    /**
     * URLAlias service.
     *
     * @var \Ibexa\Contracts\Core\Repository\URLAliasService
     */
    protected $urlAliasService;

    /**
     * Location service.
     *
     * @var \Ibexa\Contracts\Core\Repository\LocationService
     */
    protected $locationService;

    /**
     * Construct controller.
     *
     * @param \Ibexa\Contracts\Core\Repository\URLAliasService $urlAliasService
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(URLAliasService $urlAliasService, LocationService $locationService)
    {
        $this->urlAliasService = $urlAliasService;
        $this->locationService = $locationService;
    }

    /**
     * Returns the URL alias with the given ID.
     *
     * @param $urlAliasId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\URLAlias
     */
    public function loadURLAlias($urlAliasId)
    {
        return $this->urlAliasService->load($urlAliasId);
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

    /**
     * Returns the list of URL aliases for a location.
     *
     * @param $locationPath
     *
     * @return \Ibexa\Rest\Server\Values\URLAliasRefList
     */
    public function listLocationURLAliases($locationPath, Request $request)
    {
        $locationPathParts = explode('/', $locationPath);

        $location = $this->locationService->loadLocation(
            array_pop($locationPathParts)
        );

        $custom = !($request->query->has('custom') && $request->query->get('custom') === 'false');

        return new Values\CachedValue(
            new Values\URLAliasRefList(
                $this->urlAliasService->listLocationAliases($location, $custom),
                $request->getPathInfo()
            ),
            ['locationId' => $location->id]
        );
    }

    /**
     * Creates a new URL alias.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedURLAlias
     */
    public function createURLAlias(Request $request)
    {
        $urlAliasCreate = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        if ($urlAliasCreate['_type'] === 'LOCATION') {
            $locationPathParts = explode(
                '/',
                $this->requestParser->parseHref($urlAliasCreate['location']['_href'], 'locationPath')
            );

            $location = $this->locationService->loadLocation(
                array_pop($locationPathParts)
            );

            try {
                $createdURLAlias = $this->urlAliasService->createUrlAlias(
                    $location,
                    $urlAliasCreate['path'],
                    $urlAliasCreate['languageCode'],
                    $urlAliasCreate['forward'],
                    $urlAliasCreate['alwaysAvailable']
                );
            } catch (InvalidArgumentException $e) {
                throw new ForbiddenException(/** @Ignore */ $e->getMessage());
            }
        } else {
            try {
                $createdURLAlias = $this->urlAliasService->createGlobalUrlAlias(
                    $urlAliasCreate['resource'],
                    $urlAliasCreate['path'],
                    $urlAliasCreate['languageCode'],
                    $urlAliasCreate['forward'],
                    $urlAliasCreate['alwaysAvailable']
                );
            } catch (InvalidArgumentException $e) {
                throw new ForbiddenException(/** @Ignore */ $e->getMessage());
            }
        }

        return new Values\CreatedURLAlias(
            [
                'urlAlias' => $createdURLAlias,
            ]
        );
    }

    /**
     * The given URL alias is deleted.
     *
     * @param $urlAliasId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteURLAlias($urlAliasId)
    {
        $this->urlAliasService->removeAliases(
            [
                $this->urlAliasService->load($urlAliasId),
            ]
        );

        return new Values\NoContent();
    }
}
