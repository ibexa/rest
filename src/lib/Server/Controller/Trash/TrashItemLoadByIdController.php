<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Trash;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/trash/{trashItemid}',
    openapi: new Model\Operation(
        summary: 'Get Trash item',
        description: 'Returns the item in Trash with the provided ID.',
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
class TrashItemLoadByIdController extends RestController
{
    public function __construct(
        protected TrashService $trashService,
        protected LocationService $locationService
    ) {
    }

    /**
     * Returns the trash item given by id.
     */
    public function loadTrashItem(int $trashItemId): Values\RestTrashItem
    {
        return new Values\RestTrashItem(
            $trashItem = $this->trashService->loadTrashItem($trashItemId),
            $this->locationService->getLocationChildCount($trashItem)
        );
    }
}
