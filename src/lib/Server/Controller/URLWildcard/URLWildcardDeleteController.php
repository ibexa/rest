<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\URLWildcard;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\URLWildcardService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/urlwildcards/{wildcardId}',
    name: 'Delete URL wildcard',
    openapi: new Model\Operation(
        summary: 'Deletes the given URL wildcard.',
        tags: [
            'Url Wildcard',
        ],
        parameters: [
            new Model\Parameter(
                name: 'wildcardId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - URL wildcard deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to delete a URL wildcard.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The URL wildcard does not exist.',
            ],
        ],
    ),
)]
class URLWildcardDeleteController extends RestController
{
    public function __construct(
        protected URLWildcardService $urlWildcardService
    ) {
    }

    /**
     * The given URL wildcard is deleted.
     */
    public function deleteURLWildcard(int $urlWildcardId): Values\NoContent
    {
        $this->urlWildcardService->remove(
            $this->urlWildcardService->load($urlWildcardId)
        );

        return new Values\NoContent();
    }
}
