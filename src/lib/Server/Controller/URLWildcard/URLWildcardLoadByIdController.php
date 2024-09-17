<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\URLWildcard;

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
class URLWildcardLoadByIdController extends RestController
{
    public function __construct(
        protected URLWildcardService $urlWildcardService
    ) {
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
}
