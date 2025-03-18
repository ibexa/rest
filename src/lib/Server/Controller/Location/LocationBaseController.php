<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Location;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\LocationList;
use Symfony\Component\HttpFoundation\Request;

class LocationBaseController extends RestController
{
    protected LocationService $locationService;

    protected ContentService $contentService;

    protected TrashService $trashService;

    protected URLAliasService $urlAliasService;

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
     * Loads a location by remote ID.
     *
     * @todo remove, or use in loadLocation with filter
     */
    public function loadLocationByRemoteId(Request $request): LocationList
    {
        return new LocationList(
            [
                new Values\RestLocation(
                    $location = $this->locationService->loadLocationByRemoteId(
                        $request->query->getString('remoteId')
                    ),
                    $this->locationService->getLocationChildCount($location)
                ),
            ],
            $request->getPathInfo()
        );
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58.
     */
    protected function extractLocationIdFromPath(string $path): int
    {
        $pathParts = explode('/', $path);

        return (int) array_pop($pathParts);
    }
}
