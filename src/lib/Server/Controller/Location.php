<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

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
use Ibexa\Rest\Server\Values\CreatedLocation;
use Ibexa\Rest\Server\Values\LocationList;
use Ibexa\Rest\Server\Values\NoContent;
use Ibexa\Rest\Server\Values\ResourceCreated;
use Ibexa\Rest\Server\Values\RestLocation;
use Ibexa\Rest\Server\Values\TemporaryRedirect;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;

/**
 * Location controller.
 */
class Location extends RestController
{
    /**
     * Location service.
     *
     * @var LocationService
     */
    protected $locationService;

    /**
     * Content service.
     *
     * @var ContentService
     */
    protected $contentService;

    /**
     * Trash service.
     *
     * @var TrashService
     */
    protected $trashService;

    /**
     * URLAlias Service.
     *
     * @var URLAliasService
     */
    protected $urlAliasService;

    /**
     * Construct controller.
     *
     * @param LocationService $locationService
     * @param ContentService $contentService
     * @param TrashService $trashService
     * @param URLAliasService $urlAliasService
     */
    public function __construct(
        LocationService $locationService,
        ContentService $contentService,
        TrashService $trashService,
        URLAliasService $urlAliasService
    ) {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->trashService = $trashService;
        $this->urlAliasService = $urlAliasService;
    }

    /**
     * Loads the location for a given ID (x)or remote ID.
     *
     * @throws BadRequestException
     *
     * @return TemporaryRedirect
     */
    public function redirectLocation(Request $request)
    {
        if ($request->query->has('id')) {
            $location = $this->locationService->loadLocation($request->query->getInt('id'));
        } elseif ($request->query->has('remoteId')) {
            $location = $this->locationService->loadLocationByRemoteId($request->query->get('remoteId'));
        } elseif ($request->query->has('urlAlias')) {
            $urlAlias = $this->urlAliasService->lookup($request->query->get('urlAlias'));
            $location = $this->locationService->loadLocation((int)$urlAlias->destination);
        } else {
            throw new BadRequestException("At least one of 'id', 'remoteId' or 'urlAlias' parameters is required.");
        }

        return new TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($location->pathString, '/'),
                ]
            )
        );
    }

    /**
     * Creates a new location for object with id $contentId.
     *
     * @param mixed $contentId
     *
     * @throws ForbiddenException
     *
     * @return CreatedLocation
     */
    public function createLocation(
        $contentId,
        Request $request
    ) {
        $locationCreateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $contentInfo = $this->contentService->loadContentInfo($contentId);

        try {
            $createdLocation = $this->locationService->createLocation($contentInfo, $locationCreateStruct);
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new CreatedLocation(['restLocation' => new RestLocation($createdLocation, 0)]);
    }

    /**
     * Loads a location.
     *
     * @param string $locationPath
     *
     * @return RestLocation
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
            new RestLocation(
                $location,
                $this->locationService->getLocationChildCount($location)
            ),
            ['locationId' => $location->id]
        );
    }

    /**
     * Deletes a location.
     *
     * @param string $locationPath
     *
     * @return NoContent
     */
    public function deleteSubtree($locationPath)
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );
        $this->locationService->deleteLocation($location);

        return new NoContent();
    }

    /**
     * Copies a subtree to a new destination.
     *
     * @param string $locationPath
     *
     * @return ResourceCreated
     */
    public function copySubtree(
        $locationPath,
        Request $request
    ) {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        $destinationLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath(
                $this->requestParser->parseHref(
                    $request->headers->get('Destination'),
                    'locationPath'
                )
            )
        );

        $newLocation = $this->locationService->copySubtree($location, $destinationLocation);

        return new ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($newLocation->pathString, '/'),
                ]
            )
        );
    }

    /**
     * Moves a subtree to a new location.
     *
     * @param string $locationPath
     *
     * @throws BadRequestException if the Destination header cannot be parsed as location or trash
     *
     * @return ResourceCreated|NoContent
     */
    public function moveSubtree(
        $locationPath,
        Request $request
    ) {
        $locationToMove = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        $destinationLocationId = null;
        $destinationHref = $request->headers->get('Destination');
        try {
            // First check to see if the destination is for moving within another subtree
            $destinationLocationId = $this->extractLocationIdFromPath(
                $this->requestParser->parseHref($destinationHref, 'locationPath')
            );

            // We're moving the subtree
            $destinationLocation = $this->locationService->loadLocation($destinationLocationId);
            $this->locationService->moveSubtree($locationToMove, $destinationLocation);

            // Reload the location to get the new position is subtree
            $locationToMove = $this->locationService->loadLocation($locationToMove->id);

            return new ResourceCreated(
                $this->router->generate(
                    'ibexa.rest.load_location',
                    [
                        'locationPath' => trim($locationToMove->pathString, '/'),
                    ]
                )
            );
        } catch (Exceptions\InvalidArgumentException $e) {
            // If parsing of destination fails, let's try to see if destination is trash
            try {
                $route = $this->requestParser->parse($destinationHref);
                if (!isset($route['_route']) || $route['_route'] !== 'ibexa.rest.load_trash_items') {
                    throw new Exceptions\InvalidArgumentException('');
                }
                // Trash the subtree
                $trashItem = $this->trashService->trash($locationToMove);

                if (isset($trashItem)) {
                    return new ResourceCreated(
                        $this->router->generate(
                            'ibexa.rest.load_trash_item',
                            ['trashItemId' => $trashItem->id]
                        )
                    );
                } else {
                    // Only a location has been trashed and not the object
                    return new NoContent();
                }
            } catch (Exceptions\InvalidArgumentException $e) {
                // If that fails, the Destination header is not formatted right
                // so just throw the BadRequestException
                throw new BadRequestException("{$destinationHref} is not an acceptable destination");
            }
        }
    }

    /**
     * Swaps a location with another one.
     *
     * @param string $locationPath
     *
     * @return NoContent
     */
    public function swapLocation(
        $locationPath,
        Request $request
    ) {
        $locationId = $this->extractLocationIdFromPath($locationPath);
        $location = $this->locationService->loadLocation($locationId);

        $destinationLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath(
                $this->requestParser->parseHref(
                    $request->headers->get('Destination'),
                    'locationPath'
                )
            )
        );

        $this->locationService->swapLocation($location, $destinationLocation);

        return new NoContent();
    }

    /**
     * Loads a location by remote ID.
     *
     * @todo remove, or use in loadLocation with filter
     *
     * @return LocationList
     */
    public function loadLocationByRemoteId(Request $request)
    {
        return new LocationList(
            [
                new RestLocation(
                    $location = $this->locationService->loadLocationByRemoteId(
                        $request->query->get('remoteId')
                    ),
                    $this->locationService->getLocationChildCount($location)
                ),
            ],
            $request->getPathInfo()
        );
    }

    /**
     * Loads all locations for content object.
     *
     * @param mixed $contentId
     *
     * @return LocationList
     */
    public function loadLocationsForContent(
        $contentId,
        Request $request
    ) {
        $restLocations = [];
        $contentInfo = $this->contentService->loadContentInfo($contentId);
        foreach ($this->locationService->loadLocations($contentInfo) as $location) {
            $restLocations[] = new RestLocation(
                $location,
                // @todo Remove, and make optional in VO. Not needed for a location list.
                $this->locationService->getLocationChildCount($location)
            );
        }

        return new Values\CachedValue(
            new LocationList($restLocations, $request->getPathInfo()),
            ['locationId' => $contentInfo->mainLocationId]
        );
    }

    /**
     * Loads child locations of a location.
     *
     * @param string $locationPath
     *
     * @return LocationList
     */
    public function loadLocationChildren(
        $locationPath,
        Request $request
    ) {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 10;

        $restLocations = [];
        $locationId = $this->extractLocationIdFromPath($locationPath);
        $children = $this->locationService->loadLocationChildren(
            $this->locationService->loadLocation($locationId),
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25
        )->locations;
        foreach ($children as $location) {
            $restLocations[] = new RestLocation(
                $location,
                $this->locationService->getLocationChildCount($location)
            );
        }

        return new Values\CachedValue(
            new LocationList($restLocations, $request->getPathInfo()),
            ['locationId' => $locationId]
        );
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58.
     *
     * @param string $path
     *
     * @return mixed
     */
    private function extractLocationIdFromPath($path)
    {
        $pathParts = explode('/', $path);

        return array_pop($pathParts);
    }

    /**
     * Updates a location.
     *
     * @param string $locationPath
     *
     * @return RestLocation
     */
    public function updateLocation(
        $locationPath,
        Request $request
    ) {
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

        return new RestLocation(
            $location = $this->locationService->updateLocation($location, $locationUpdate->locationUpdateStruct),
            $this->locationService->getLocationChildCount($location)
        );
    }
}

class_alias(Location::class, 'EzSystems\EzPlatformRest\Server\Controller\Location');
