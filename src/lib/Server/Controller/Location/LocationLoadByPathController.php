<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Location;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/locations/{path}',
    name: 'Load Location',
    openapi: new Model\Operation(
        summary: 'Loads the Location for the given path e.g. \'/content/locations/1/2/61\'.',
        tags: [
            'Location',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new Location is returned in XML or JSON format.',
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
                'content' => [
                    'application/vnd.ibexa.api.Location+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Location',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/POST/Location.xml.example',
                    ],
                    'application/vnd.ibexa.api.Location+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LocationWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/POST/Location.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to read this Location.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Location with the given path does not exist.',
            ],
        ],
    ),
)]
class LocationLoadByPathController extends LocationBaseController
{
    /**
     * Loads a location.
     *
     * @param string $locationPath
     *
     * @return \Ibexa\Rest\Server\Values\RestLocation
     */
    public function loadLocation($locationPath)
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        if (trim($location->pathString, '/') != $locationPath) {
            throw new Exceptions\NotFoundException(
                "Could not find a Location with path string $locationPath"
            );
        }

        return new Values\CachedValue(
            new Values\RestLocation(
                $location,
                $this->locationService->getLocationChildCount($location)
            ),
            ['locationId' => $location->id]
        );
    }
}
