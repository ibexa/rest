<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Location;

use ApiPlatform\Metadata\Get;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values\NoContent;
use Ibexa\Rest\Server\Values\ResourceCreated;
use Symfony\Component\HttpFoundation\Request;

class LocationSubtreeMoveController extends LocationBaseController
{
    /**
     * Moves a subtree to a new location.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException if the Destination header cannot be parsed as location or trash
     */
    public function moveSubtree(string $locationPath, Request $request): ResourceCreated|NoContent
    {
        $locationToMove = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        $destinationHref = (string)$request->headers->get('Destination');
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
                $route = $this->uriParser->matchUri($destinationHref);
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
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function moveLocation(Request $request, string $locationPath): ResourceCreated
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

        return new ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($locationToMove->getPathString(), '/'),
                ],
            ),
        );
    }
}
