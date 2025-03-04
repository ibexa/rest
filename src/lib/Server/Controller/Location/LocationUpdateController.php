<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Location;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Patch(
    uriTemplate: '/content/locations/{path}',
    name: 'Update Location',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates the Location. This method can also be used to hide/reveal a Location via the hidden field in the LocationUpdate. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Location',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Location is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The LocationUpdate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
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
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.LocationUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/LocationUpdateStruct',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/locations/location_id/PATCH/LocationUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.LocationUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/LocationUpdateStructWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/locations/location_id/PATCH/LocationUpdate.json.example',
                ],
            ]),
        ),
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
                'description' => 'Error - the user is not authorized to update this Location.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Location with the given ID does not exist.',
            ],
        ],
    ),
)]
class LocationUpdateController extends LocationBaseController
{
    /**
     * Updates a location.
     *
     * @param string $locationPath
     *
     * @return \Ibexa\Rest\Server\Values\RestLocation
     */
    public function updateLocation($locationPath, Request $request)
    {
        $locationUpdate = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $location = $this->locationService->loadLocation($this->extractLocationIdFromPath($locationPath));

        // First handle hiding/unhiding so that updating location afterwards
        // will return updated location with hidden/visible status correctly updated
        // Exact check for true/false is needed as null signals that no hiding/unhiding
        // is to be performed
        if ($locationUpdate->hidden === true) {
            $this->locationService->hideLocation($location);
        } elseif ($locationUpdate->hidden === false) {
            $this->locationService->unhideLocation($location);
        }

        return new Values\RestLocation(
            $location = $this->locationService->updateLocation($location, $locationUpdate->locationUpdateStruct),
            $this->locationService->getLocationChildCount($location)
        );
    }
}
