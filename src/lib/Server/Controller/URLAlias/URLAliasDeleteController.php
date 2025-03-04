<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\URLAlias;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/urlaliases/{urlAliasId}',
    name: 'Delete URL alias',
    openapi: new Model\Operation(
        summary: 'Deletes the provided URL alias.',
        tags: [
            'Url Alias',
        ],
        parameters: [
            new Model\Parameter(
                name: 'urlAliasId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - URL alias deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete a URL alias.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The URL alias does not exist.',
            ],
        ],
    ),
)]
class URLAliasDeleteController extends RestController
{
    public function __construct(
        protected URLAliasService $urlAliasService,
        protected LocationService $locationService
    ) {
    }

    /**
     * The given URL alias is deleted.
     */
    public function deleteURLAlias(string $urlAliasId): Values\NoContent
    {
        $this->urlAliasService->removeAliases(
            [
                $this->urlAliasService->load($urlAliasId),
            ]
        );

        return new Values\NoContent();
    }
}
