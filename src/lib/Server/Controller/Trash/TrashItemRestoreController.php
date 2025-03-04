<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Trash;

use Ibexa\Contracts\Core\Repository\Exceptions as ApiExceptions;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use InvalidArgumentException;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;

class TrashItemRestoreController extends RestController
{
    public function __construct(
        protected TrashService $trashService,
        protected LocationService $locationService
    ) {
    }

    /**
     * Restores a trashItem.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \InvalidArgumentException
     */
    public function restoreTrashItem(int $trashItemId, Request $request): Values\ResourceCreated
    {
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
                $this->uriParser->getAttributeFromUri((string)$request->headers->get('Destination'), 'locationPath')
            );

            try {
                $parentLocation = $this->locationService->loadLocation((int)array_pop($locationPathParts));
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

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($location->pathString, '/'),
                ]
            )
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException
     */
    public function restoreItem(int $trashItemId, Request $request): Values\ResourceCreated
    {
        try {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $locationDestination */
            $locationDestination = $this->inputDispatcher->parse(
                new Message(
                    ['Content-Type' => $request->headers->get('Content-Type')],
                    $request->getContent(),
                ),
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage(), 1, $e);
        }

        $trashItem = $this->trashService->loadTrashItem($trashItemId);

        if ($locationDestination === null) {
            try {
                $locationDestination = $this->locationService->loadLocation($trashItem->parentLocationId);
            } catch (NotFoundException $e) {
                throw new ForbiddenException(/** @Ignore */ $e->getMessage(), 1, $e);
            }
        }

        $location = $this->trashService->recover($trashItem, $locationDestination);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_location',
                [
                    'locationPath' => trim($location->getPathString(), '/'),
                ],
            )
        );
    }
}
