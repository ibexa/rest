<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\TemporaryRedirect;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/objects/{contentId}/relations',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Load Relations of content item',
        description: 'Redirects to the Relations of the current version.',
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
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'Temporary redirect.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to read this content item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item was not found.',
            ],
        ],
    ),
)]
class ContentCurrentVersionRelationsRedirectController extends RestController
{
    public function redirectCurrentVersionRelations(int $contentId): TemporaryRedirect
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        return new TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.load_version_relations',
                [
                    'contentId' => $contentId,
                    'versionNumber' => $contentInfo->currentVersionNo,
                ]
            )
        );
    }
}
