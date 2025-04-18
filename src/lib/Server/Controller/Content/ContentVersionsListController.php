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
use Ibexa\Rest\Server\Values\VersionList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/objects/{contentId}/versions',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'List versions',
        description: 'Returns a list of all versions of the content item. This method does not include fields and relations in the version elements of the response.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the version list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
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
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.VersionList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/GET/VersionList.xml.example',
                    ],
                    'application/vnd.ibexa.api.VersionList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/GET/VersionList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read the versions.',
            ],
        ],
    ),
)]
class ContentVersionsListController extends RestController
{
    /**
     * Returns a list of all versions of the content. This method does not
     * include fields and relations in the Version elements of the response.
     */
    public function loadContentVersions(int $contentId, Request $request): VersionList
    {
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo($contentId);

        $versionsIterable = $contentService->loadVersions($contentInfo);
        $versions = [];
        foreach ($versionsIterable as $version) {
            $versions[] = $version;
        }

        return new VersionList(
            $versions,
            $request->getPathInfo(),
        );
    }
}
