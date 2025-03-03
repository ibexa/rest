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
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/objectstategroups/{objectStateGroupId}/objectstates/{objectStateId}',
    name: 'Delete Object state',
    openapi: new Model\Operation(
        summary: 'Deletes provided Object state.',
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
            new Model\Parameter(
                name: 'objectStateId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - Object state deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete an Object state.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The Object state does not exist.',
            ],
        ],
    ),
)]
class ObjectStateDeleteController extends RestController
{
    protected ObjectStateService $objectStateService;

    protected ContentService $contentService;

    public function __construct(ObjectStateService $objectStateService, ContentService $contentService)
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * The given object state is deleted.
     */
    public function deleteObjectState(int $objectStateId): \Ibexa\Rest\Server\Values\NoContent
    {
        $this->objectStateService->deleteObjectState(
            $this->objectStateService->loadObjectState($objectStateId)
        );

        return new Values\NoContent();
    }
}
