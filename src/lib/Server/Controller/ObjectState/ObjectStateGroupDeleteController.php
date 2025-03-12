<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ObjectState;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\NoContent;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}',
    openapi: new Model\Operation(
        summary: 'Delete Object state group',
        description: 'Deletes the given Object state group including Object states.',
        tags: [
            'Object State Groups',
        ],
        parameters: [
            new Model\Parameter(
                name: 'objectStateGroupId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - Object state group deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete an Object state group.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The Object state group does not exist.',
            ],
        ],
    ),
)]
class ObjectStateGroupDeleteController extends RestController
{
    protected ObjectStateService $objectStateService;

    protected ContentService $contentService;

    public function __construct(ObjectStateService $objectStateService, ContentService $contentService)
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * The given object state group including the object states is deleted.
     */
    public function deleteObjectStateGroup(int $objectStateGroupId): NoContent
    {
        $this->objectStateService->deleteObjectStateGroup(
            $this->objectStateService->loadObjectStateGroup($objectStateGroupId)
        );

        return new NoContent();
    }
}
