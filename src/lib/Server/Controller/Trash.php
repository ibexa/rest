<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\NoContent;
use Ibexa\Rest\Server\Values\ResourceCreated;
use Ibexa\Rest\Server\Values\RestTrashItem;
use InvalidArgumentException;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trash controller.
 */
class Trash extends RestController
{
    /**
     * Trash service.
     *
     * @var TrashService
     */
    protected $trashService;

    /**
     * Location service.
     *
     * @var LocationService
     */
    protected $locationService;

    /**
     * Construct controller.
     *
     * @param TrashService $trashService
     * @param LocationService $locationService
     */
    public function __construct(
        TrashService $trashService,
        LocationService $locationService
    ) {
        $this->trashService = $trashService;
        $this->locationService = $locationService;
    }

    /**
     * Returns a list of all trash items.
     *
     * @return Values\Trash
     */
    public function loadTrashItems(Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : -1;

        $query = new Query();
        $query->offset = $offset >= 0 ? $offset : null;
        $query->limit = $limit >= 0 ? $limit : null;

        $trashItems = [];

        foreach ($this->trashService->findTrashItems($query)->items as $trashItem) {
            $trashItems[] = new RestTrashItem(
                $trashItem,
                $this->locationService->getLocationChildCount($trashItem)
            );
        }

        return new Values\Trash(
            $trashItems,
            $request->getPathInfo()
        );
    }

    /**
     * Returns the trash item given by id.
     *
     * @param $trashItemId
     *
     * @return RestTrashItem
     */
    public function loadTrashItem($trashItemId)
    {
        return new RestTrashItem(
            $trashItem = $this->trashService->loadTrashItem($trashItemId),
            $this->locationService->getLocationChildCount($trashItem)
        );
    }

    /**
     * Empties the trash.
     *
     * @return NoContent
     */
    public function emptyTrash()
    {
        $this->trashService->emptyTrash();

        return new NoContent();
    }

    /**
     * Deletes the given trash item.
     *
     * @param $trashItemId
     *
     * @return NoContent
     */
    public function deleteTrashItem($trashItemId)
    {
        $this->trashService->deleteTrashItem(
            $this->trashService->loadTrashItem($trashItemId)
        );

        return new NoContent();
    }

    /**
     * Restores a trashItem.
     *
     * @param $trashItemId
     *
     * @throws ForbiddenException
     *
     * @return ResourceCreated
     */
    public function restoreTrashItem(
        $trashItemId,
        Request $request
    ) {
        $requestDestination = null;
        try {
            $requestDestination = $request->headers->get('Destination');
        } catch (InvalidArgumentException $e) {
            // No Destination header
        }

        $parentLocation = null;
        if ($request->headers->has('Destination')) {
            $locationPathParts = explode(
                '/',
                $this->requestParser->parseHref($request->headers->get('Destination'), 'locationPath')
            );

            try {
                $parentLocation = $this->locationService->loadLocation(array_pop($locationPathParts));
            } catch (NotFoundException $e) {
                throw new ForbiddenException(/** @Ignore */ $e->getMessage());
            }
        }

        $trashItem = $this->trashService->loadTrashItem($trashItemId);

        if ($requestDestination === null) {
            // If we're recovering under the original location
            // check if it exists, to return "403 Forbidden" in case it does not
            try {
                $this->locationService->loadLocation($trashItem->parentLocationId);
            } catch (NotFoundException $e) {
                throw new ForbiddenException(/** @Ignore */ $e->getMessage());
            }
        }

        $location = $this->trashService->recover($trashItem, $parentLocation);

        return new ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($location->pathString, '/'),
                ]
            )
        );
    }
}

class_alias(Trash::class, 'EzSystems\EzPlatformRest\Server\Controller\Trash');
