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
    uriTemplate: '/content/objects/{contentId}/reveal',
    name: 'Reveal content item',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Makes or keep the content item visible',
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
        requestBody: new Model\RequestBody(
            content: new \ArrayObject(),
        ),
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'OK - Object item is revealed.',
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
class ContentRevealController extends RestController
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function revealContent(int $contentId): Values\NoContent
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $this->repository->getContentService()->revealContent($contentInfo);

        return new Values\NoContent();
    }
}
