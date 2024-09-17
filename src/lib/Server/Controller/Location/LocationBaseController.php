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
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58.
     *
     * @param string $path
     *
     * @return mixed
     */
    protected function extractLocationIdFromPath($path)
    {
        $pathParts = explode('/', $path);

        return array_pop($pathParts);
    }
}
