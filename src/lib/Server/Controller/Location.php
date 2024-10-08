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
     * @var \Ibexa\Contracts\Core\Repository\LocationService
     */
    protected $locationService;

    /**
     * Content service.
     *
     * @var \Ibexa\Contracts\Core\Repository\ContentService
     */
    protected $contentService;

    /**
     * Trash service.
     *
     * @var \Ibexa\Contracts\Core\Repository\TrashService
     */
    protected $trashService;

    /**
     * URLAlias Service.
     *
     * @var \Ibexa\Contracts\Core\Repository\URLAliasService
     */
    protected $urlAliasService;

    /**
     * Construct controller.
     *
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\TrashService $trashService
     * @param \Ibexa\Contracts\Core\Repository\URLAliasService $urlAliasService
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
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\TemporaryRedirect
     */
    public function redirectLocation(Request $request)
    {
        if ($request->query->has('id')) {
            $location = $this->locationService->loadLocation((int)$request->query->get('id'));
        } elseif ($request->query->has('remoteId')) {
            $location = $this->locationService->loadLocationByRemoteId((string)$request->query->get('remoteId'));
        } elseif ($request->query->has('urlAlias')) {
            $urlAlias = $this->urlAliasService->lookup((string)$request->query->get('urlAlias'));
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

    /**
     * Creates a new location for object with id $contentId.
     *
     * @param mixed $contentId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedLocation
     */
    public function createLocation($contentId, Request $request)
    {
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

        return new Values\CreatedLocation(['restLocation' => new Values\RestLocation($createdLocation, 0)]);
    }

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

    /**
     * Deletes a location.
     *
     * @param string $locationPath
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteSubtree($locationPath)
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );
        $this->locationService->deleteLocation($location);

        return new Values\NoContent();
    }

    /**
     * Copies a subtree to a new destination.
     *
     * @param string $locationPath
     *
     * @return \Ibexa\Rest\Server\Values\ResourceCreated
     */
    public function copySubtree($locationPath, Request $request)
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        $destinationLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath(
                $this->uriParser->getAttributeFromUri(
                    $request->headers->get('Destination'),
                    'locationPath'
                )
            )
        );

        $newLocation = $this->locationService->copySubtree($location, $destinationLocation);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($newLocation->pathString, '/'),
                ]
            )
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function copy(string $locationPath, Request $request): Values\ResourceCreated
    {
        $locationId = $this->extractLocationIdFromPath($locationPath);
        $location = $this->locationService->loadLocation($locationId);

        $destinationLocation = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent(),
            ),
        );

        $newLocation = $this->locationService->copySubtree($location, $destinationLocation);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($newLocation->pathString, '/'),
                ],
            )
        );
    }

    /**
     * Moves a subtree to a new location.
     *
     * @param string $locationPath
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException if the Destination header cannot be parsed as location or trash
     *
     * @return \Ibexa\Rest\Server\Values\ResourceCreated|\Ibexa\Rest\Server\Values\NoContent
     */
    public function moveSubtree($locationPath, Request $request)
    {
        $locationToMove = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        $destinationLocationId = null;
        $destinationHref = $request->headers->get('Destination');
        try {
            // First check to see if the destination is for moving within another subtree
            $destinationLocationId = $this->extractLocationIdFromPath(
                $this->uriParser->getAttributeFromUri($destinationHref, 'locationPath')
            );

            // We're moving the subtree
            $destinationLocation = $this->locationService->loadLocation($destinationLocationId);
            $this->locationService->moveSubtree($locationToMove, $destinationLocation);

            // Reload the location to get the new position is subtree
            $locationToMove = $this->locationService->loadLocation($locationToMove->id);

            return new Values\ResourceCreated(
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
                $route = $this->uriParser->matchUri($destinationHref);
                if (!isset($route['_route']) || $route['_route'] !== 'ibexa.rest.load_trash_items') {
                    throw new Exceptions\InvalidArgumentException('');
                }
                // Trash the subtree
                $trashItem = $this->trashService->trash($locationToMove);

                if (isset($trashItem)) {
                    return new Values\ResourceCreated(
                        $this->router->generate(
                            'ibexa.rest.load_trash_item',
                            ['trashItemId' => $trashItem->id]
                        )
                    );
                } else {
                    // Only a location has been trashed and not the object
                    return new Values\NoContent();
                }
            } catch (Exceptions\InvalidArgumentException $e) {
                // If that fails, the Destination header is not formatted right
                // so just throw the BadRequestException
                throw new BadRequestException("{$destinationHref} is not an acceptable destination");
            }
        }
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function moveLocation(Request $request, string $locationPath): Values\ResourceCreated
    {
        $destinationLocation = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent(),
            ),
        );

        $locationToMove = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath),
        );

        $this->locationService->moveSubtree($locationToMove, $destinationLocation);

        // Reload the location to get a new subtree position
        $locationToMove = $this->locationService->loadLocation($locationToMove->id);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($locationToMove->getPathString(), '/'),
                ],
            ),
        );
    }

    /**
     * Swaps a location with another one.
     *
     * @param string $locationPath
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function swapLocation($locationPath, Request $request)
    {
        $locationId = $this->extractLocationIdFromPath($locationPath);
        $location = $this->locationService->loadLocation($locationId);

        $destinationLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath(
                $this->uriParser->getAttributeFromUri(
                    $request->headers->get('Destination'),
                    'locationPath'
                )
            )
        );

        $this->locationService->swapLocation($location, $destinationLocation);

        return new Values\NoContent();
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function swap(Request $request, string $locationPath): Values\NoContent
    {
        $locationId = $this->extractLocationIdFromPath($locationPath);
        $location = $this->locationService->loadLocation($locationId);

        $destinationLocation = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent(),
            ),
        );

        $this->locationService->swapLocation($location, $destinationLocation);

        return new Values\NoContent();
    }

    /**
     * Loads a location by remote ID.
     *
     * @todo remove, or use in loadLocation with filter
     *
     * @return \Ibexa\Rest\Server\Values\LocationList
     */
    public function loadLocationByRemoteId(Request $request)
    {
        return new Values\LocationList(
            [
                new Values\RestLocation(
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

    /**
     * Loads child locations of a location.
     *
     * @param string $locationPath
     *
     * @return \Ibexa\Rest\Server\Values\LocationList
     */
    public function loadLocationChildren($locationPath, Request $request)
    {
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
            $restLocations[] = new Values\RestLocation(
                $location,
                $this->locationService->getLocationChildCount($location)
            );
        }

        return new Values\CachedValue(
            new Values\LocationList($restLocations, $request->getPathInfo()),
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
