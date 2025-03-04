<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\URLAlias;

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
class URLAliasCreateController extends RestController
{
    public function __construct(
        protected URLAliasService $urlAliasService,
        protected LocationService $locationService
    ) {
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
                $this->uriParser->getAttributeFromUri($urlAliasCreate['location']['_href'], 'locationPath')
            );

            $location = $this->locationService->loadLocation(
                (int)array_pop($locationPathParts)
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
}
