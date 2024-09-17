<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Trash;

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
class TrashItemDeleteController extends RestController
{
    public function __construct(
        protected TrashService $trashService,
        protected LocationService $locationService
    ) {
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
}
