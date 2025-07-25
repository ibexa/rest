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
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values\TemporaryRedirect;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/objects',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Load content by remote ID',
        description: 'Loads content item for a given remote ID.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'remoteId',
                description: 'Remote ID of the content item.',
                in: 'query',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_TEMPORARY_REDIRECT => [
                'description' => 'Temporary redirect to `GET /content/objects/{contentId}` equivalent.',
                'headers' => [
                    'Location' => [
                        'description' => 'Contains the prefixed `/content/objects/{contentId}` absolute path of the content item.',
                        'schema' => ['type' => 'string'],
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the required `remoteId` query parameter is missing.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content with the given remote ID does not exist.',
            ],
        ],
    ),
)]
class ContentRedirectController extends RestController
{
    /**
     * Loads a content info by a remote ID.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     */
    public function redirectContent(Request $request): TemporaryRedirect
    {
        if (!$request->query->has('remoteId')) {
            throw new BadRequestException("'remoteId' parameter is required.");
        }

        $contentInfo = $this->repository->getContentService()->loadContentInfoByRemoteId(
            $request->query->getString('remoteId')
        );

        return new TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.load_content',
                [
                    'contentId' => $contentInfo->id,
                ]
            )
        );
    }
}
