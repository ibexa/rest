<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\URLWildcard;

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
class URLWildcardCreateController extends RestController
{
    public function __construct(
        protected URLWildcardService $urlWildcardService
    ) {
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
}
