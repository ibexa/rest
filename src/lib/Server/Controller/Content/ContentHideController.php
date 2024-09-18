<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/content/objects/{contentId}/hide',
    name: 'Hide content item',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapiContext: ['requestBody' => false],
    openapi: new Model\Operation(
        summary: 'Makes or keep the content item invisible',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'OK - Object item is hidden.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to change Object item visibility.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content item was not found.',
            ],
        ],
    ),
)]
class ContentHideController extends RestController
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function hideContent(int $contentId): Values\NoContent
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $this->repository->getContentService()->hideContent($contentInfo);

        return new Values\NoContent();
    }
}
