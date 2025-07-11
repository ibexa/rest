<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Trash;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\NoContent;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/trash',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Empty Trash',
        description: 'Empties the Trash.',
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
class TrashEmptyController extends RestController
{
    public function __construct(
        protected TrashService $trashService,
        protected LocationService $locationService
    ) {
    }

    public function emptyTrash(): NoContent
    {
        $this->trashService->emptyTrash();

        return new NoContent();
    }
}
