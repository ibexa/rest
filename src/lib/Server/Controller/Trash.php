<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions as ApiExceptions;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use InvalidArgumentException;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

#[Get(
    uriTemplate: '/content/trash',
    name: 'List Trash items',
    openapi: new Model\Operation(
        summary: 'Returns a list of all items in the Trash.',
        tags: [
            'Trash',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Trash item list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the list of items in the Trash.',
                'content' => [
                    'application/vnd.ibexa.api.Trash+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Trash',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/trash/GET/Trash.xml.example',
                    ],
                    'application/vnd.ibexa.api.Trash+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/TrashWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/trash/GET/Trash.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read the Trash.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/trash',
    name: 'Empty Trash',
    openapi: new Model\Operation(
        summary: 'Empties the Trash.',
        tags: [
            'Trash',
        ],
        parameters: [
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - Trash emptied.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to empty all items from Trash.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/trash/{trashItemid}',
    name: 'Get Trash item',
    openapi: new Model\Operation(
        summary: 'Returns the item in Trash with the provided ID.',
        tags: [
            'Trash',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the item in Trash is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'trashItemid',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.TrashItem+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/TrashItem',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/trash/trash_itemid/GET/TrashItem.xml.example',
                    ],
                    'application/vnd.ibexa.api.TrashItem+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/TrashItemWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/trash/trash_itemid/GET/TrashItem.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read the item in Trash.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - An item in Trash with the provided ID does not exist.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/trash/{trashItemid}',
    name: 'Delete Trash item',
    openapi: new Model\Operation(
        summary: 'Deletes the provided item from Trash.',
        tags: [
            'Trash',
        ],
        parameters: [
            new Model\Parameter(
                name: 'trashItemid',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - item deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete the provided item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The provided item does not exist in Trash.',
            ],
        ],
    ),
)]
/**
 * Trash controller.
 */
class Trash extends RestController
{
    /**
     * Trash service.
     *
     * @var \Ibexa\Contracts\Core\Repository\TrashService
     */
    protected $trashService;

    /**
     * Location service.
     *
     * @var \Ibexa\Contracts\Core\Repository\LocationService
     */
    protected $locationService;

    /**
     * Construct controller.
     *
     * @param \Ibexa\Contracts\Core\Repository\TrashService $trashService
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(TrashService $trashService, LocationService $locationService)
    {
        $this->trashService = $trashService;
        $this->locationService = $locationService;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function trashLocation(string $locationPath): RestValue
    {
        $location = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($locationPath),
        );

        $trashItem = $this->trashService->trash($location);

        if ($trashItem === null) {
            return new Values\NoContent();
        }

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_trash_item',
                ['trashItemId' => $trashItem->getId()],
            ),
        );
    }

    /**
     * Returns a list of all trash items.
     *
     * @return \Ibexa\Rest\Server\Values\Trash
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
            $trashItems[] = new Values\RestTrashItem(
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
     * @return \Ibexa\Rest\Server\Values\RestTrashItem
     */
    public function loadTrashItem($trashItemId)
    {
        return new Values\RestTrashItem(
            $trashItem = $this->trashService->loadTrashItem($trashItemId),
            $this->locationService->getLocationChildCount($trashItem)
        );
    }

    /**
     * Empties the trash.
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function emptyTrash()
    {
        $this->trashService->emptyTrash();

        return new Values\NoContent();
    }

    /**
     * Deletes the given trash item.
     *
     * @param $trashItemId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteTrashItem($trashItemId)
    {
        $this->trashService->deleteTrashItem(
            $this->trashService->loadTrashItem($trashItemId)
        );

        return new Values\NoContent();
    }

    /**
     * Restores a trashItem.
     *
     * @param $trashItemId
     *
     * @return \Ibexa\Rest\Server\Values\ResourceCreated
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function restoreTrashItem($trashItemId, Request $request)
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

    private function extractLocationIdFromPath(string $path): int
    {
        $pathParts = explode('/', $path);
        $lastPart = array_pop($pathParts);

        Assert::integerish($lastPart);

        return (int)$lastPart;
    }
}
