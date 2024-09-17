<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\URLAlias;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\URLAliasService;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
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
     *
     * @param $urlAliasId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteURLAlias($urlAliasId)
    {
        $this->urlAliasService->removeAliases(
            [
                $this->urlAliasService->load($urlAliasId),
            ]
        );

        return new Values\NoContent();
    }
}
