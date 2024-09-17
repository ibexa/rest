<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Location;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/objects/{contentId}/locations',
    name: 'Get Locations for content item',
    openapi: new Model\Operation(
        summary: 'Loads all Locations for the given content item.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Location list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'ETag',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.LocationList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LocationList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/GET/LocationList.xml.example',
                    ],
                    'application/vnd.ibexa.api.LocationList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LocationListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/GET/LocationList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to read this content item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item with the given ID does not exist.',
            ],
        ],
    ),
)]
class LocationForContentListController extends LocationBaseController
{
    /**
     * Loads all locations for content object.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\LocationList
     */
    public function loadLocationsForContent($contentId, Request $request)
    {
        $restLocations = [];
        $contentInfo = $this->contentService->loadContentInfo($contentId);
        foreach ($this->locationService->loadLocations($contentInfo) as $location) {
            $restLocations[] = new Values\RestLocation(
                $location,
                // @todo Remove, and make optional in VO. Not needed for a location list.
                $this->locationService->getLocationChildCount($location)
            );
        }

        return new Values\CachedValue(
            new Values\LocationList($restLocations, $request->getPathInfo()),
            ['locationId' => $contentInfo->mainLocationId]
        );
    }
}
