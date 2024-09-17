<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Location;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
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
    uriTemplate: '/content/locations',
    name: 'Load Locations by id/remoteId/urlAlias',
    openapi: new Model\Operation(
        summary: 'Loads the Location for a given ID (x), remote ID or URL alias.',
        tags: [
            'Location',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
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
                            '$ref' => '#/components/schemas/Location',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/POST/Location.xml.example',
                    ],
                    'application/vnd.ibexa.api.LocationList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/LocationWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/locations/POST/Location.json.example',
                    ],
                ],
            ],
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'Temporary redirect to the main resource URL.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Location with the given ID (remote ID or URL  Alias) does not exist.',
            ],
        ],
    ),
)]
class LocationRedirectController extends LocationBaseController
{
    /**
     * Loads the location for a given ID (x)or remote ID.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\TemporaryRedirect
     */
    public function redirectLocation(Request $request)
    {
        if ($request->query->has('id')) {
            $location = $this->locationService->loadLocation($request->query->get('id'));
        } elseif ($request->query->has('remoteId')) {
            $location = $this->locationService->loadLocationByRemoteId($request->query->get('remoteId'));
        } elseif ($request->query->has('urlAlias')) {
            $urlAlias = $this->urlAliasService->lookup($request->query->get('urlAlias'));
            $location = $this->locationService->loadLocation($urlAlias->destination);
        } else {
            throw new BadRequestException("At least one of 'id', 'remoteId' or 'urlAlias' parameters is required.");
        }

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($location->pathString, '/'),
                ]
            )
        );
    }
}
