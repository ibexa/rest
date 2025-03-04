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
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/locations/{path}/urlaliases',
    name: 'List URL aliases for Location',
    openapi: new Model\Operation(
        summary: 'Returns the list of URL aliases for a Location.',
        tags: [
            'Location',
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
            new Model\Parameter(
                name: 'path',
                in: 'path',
                required: true,
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
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The user has no permission to read URL aliases.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The Location was not found.',
            ],
        ],
    ),
)]
class URLAliasListLocationController extends RestController
{
    public function __construct(
        protected URLAliasService $urlAliasService,
        protected LocationService $locationService
    ) {
    }

    /**
     * Returns the list of URL aliases for a location.
     */
    public function listLocationURLAliases(string $locationPath, Request $request): Values\CachedValue
    {
        $locationPathParts = explode('/', $locationPath);

        $location = $this->locationService->loadLocation(
            (int)array_pop($locationPathParts)
        );

        $custom = !($request->query->has('custom') && $request->query->get('custom') === 'false');

        $locationAliasesArray = [];
        foreach ($this->urlAliasService->listLocationAliases($location, $custom) as $locationAlias) {
            $locationAliasesArray[] = $locationAlias;
        }

        return new Values\CachedValue(
            new Values\URLAliasRefList(
                $locationAliasesArray,
                $request->getPathInfo()
            ),
            ['locationId' => $location->id]
        );
    }
}
