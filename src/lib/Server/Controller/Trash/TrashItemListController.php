<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Trash;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\TrashService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/trash',
    name: 'List Trash items',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
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
class TrashItemListController extends RestController
{
    public function __construct(
        protected TrashService $trashService,
        protected LocationService $locationService
    ) {
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
}
