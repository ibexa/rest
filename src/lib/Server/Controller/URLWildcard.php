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
use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/urlwildcards',
    name: 'List URL wildcards',
    openapi: new Model\Operation(
        summary: 'Returns a list of URL wildcards.',
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
#[Post(
    uriTemplate: '/content/urlwildcards',
    name: 'Create URL wildcard',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new URL wildcard.',
        tags: [
            'Url Wildcard',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new URL wildcard is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The URL Wildcard input schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.UrlWildcardCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UrlWildcardCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlwildcards/POST/UrlWildcardCreate.xml.example',
                ],
                'application/vnd.ibexa.api.UrlWildcardCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/UrlWildcardCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlwildcards/POST/UrlWildcardCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'description' => 'URL wildcard created.',
                'content' => [
                    'application/vnd.ibexa.api.UrlWildcard+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UrlWildcard',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlwildcards/wildcard_id/GET/UrlWildcard.xml.example',
                    ],
                    'application/vnd.ibexa.api.UrlWildcard+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UrlWildcardWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlwildcards/wildcard_id/GET/UrlWildcard.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to create a URL wildcard.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - A URL wildcard with the same identifier already exists.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/urlwildcards/{wildcardId}',
    name: 'Get URL wildcard',
    openapi: new Model\Operation(
        summary: 'Returns the URL wildcard with the given ID.',
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
            new Model\Parameter(
                name: 'wildcardId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the URL wildcard.',
                'content' => [
                    'application/vnd.ibexa.api.UrlWildcard+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UrlWildcard',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlwildcards/wildcard_id/GET/UrlWildcard.xml.example',
                    ],
                    'application/vnd.ibexa.api.UrlWildcard+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UrlWildcardWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/urlwildcards/wildcard_id/GET/UrlWildcard.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read URL wildcards.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The URL wildcard does not exist.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/urlwildcards/{wildcardId}',
    name: 'Delete URL wildcard',
    openapi: new Model\Operation(
        summary: 'Deletes the given URL wildcard.',
        tags: [
            'Url Wildcard',
        ],
        parameters: [
            new Model\Parameter(
                name: 'wildcardId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - URL wildcard deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete a URL wildcard.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The URL wildcard does not exist.',
            ],
        ],
    ),
)]
/**
 * URLWildcard controller.
 */
class URLWildcard extends RestController
{
    /**
     * URLWildcard service.
     *
     * @var \Ibexa\Contracts\Core\Repository\URLWildcardService
     */
    protected $urlWildcardService;

    /**
     * Construct controller.
     *
     * @param \Ibexa\Contracts\Core\Repository\URLWildcardService $urlWildcardService
     */
    public function __construct(URLWildcardService $urlWildcardService)
    {
        $this->urlWildcardService = $urlWildcardService;
    }

    /**
     * Returns the URL wildcard with the given id.
     *
     * @param $urlWildcardId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard
     */
    public function loadURLWildcard($urlWildcardId)
    {
        return $this->urlWildcardService->load($urlWildcardId);
    }

    /**
     * Returns the list of URL wildcards.
     *
     * @return \Ibexa\Rest\Server\Values\URLWildcardList
     */
    public function listURLWildcards()
    {
        return new Values\URLWildcardList(
            $this->urlWildcardService->loadAll()
        );
    }

    /**
     * Creates a new URL wildcard.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedURLWildcard
     */
    public function createURLWildcard(Request $request)
    {
        $urlWildcardCreate = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            $createdURLWildcard = $this->urlWildcardService->create(
                $urlWildcardCreate['sourceUrl'],
                $urlWildcardCreate['destinationUrl'],
                $urlWildcardCreate['forward']
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new Values\CreatedURLWildcard(
            [
                'urlWildcard' => $createdURLWildcard,
            ]
        );
    }

    /**
     * The given URL wildcard is deleted.
     *
     * @param $urlWildcardId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteURLWildcard($urlWildcardId)
    {
        $this->urlWildcardService->remove(
            $this->urlWildcardService->load($urlWildcardId)
        );

        return new Values\NoContent();
    }
}
