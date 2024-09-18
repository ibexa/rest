<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Location;

use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;

class LocationSwapController extends LocationBaseController
{
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
                $this->requestParser->parseHref(
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
}
