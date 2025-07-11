<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Location;

use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Values\ResourceCreated;
use Symfony\Component\HttpFoundation\Request;

class LocationSubtreeCopyController extends LocationBaseController
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function copy(string $locationPath, Request $request): ResourceCreated
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

        return new ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($newLocation->pathString, '/'),
                ],
            )
        );
    }

    /**
     * Copies a subtree to a new destination.
     */
    public function copySubtree(string $locationPath, Request $request): ResourceCreated
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath)
        );

        $destinationLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath(
                $this->uriParser->getAttributeFromUri(
                    (string)$request->headers->get('Destination'),
                    'locationPath',
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
}
