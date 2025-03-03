<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/content/objects/{contentId}/currentversion',
    name: 'Create a draft from current version',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'The system creates a new draft as a copy of the current version. COPY or POST with header X-HTTP-Method-Override COPY.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated version is returned in XML or JSON format.',
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
            Response::HTTP_CREATED => [
                'description' => 'Created',
                'content' => [
                    'application/vnd.ibexa.api.Version+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Version',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.xml.example',
                    ],
                    'application/vnd.ibexa.api.Version+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/GET/Version.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update this content item.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the current version is already a draft.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content item was not found.',
            ],
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject(),
        ),
    ),
)]
class ContentDraftCreateFromCurrentVersionController extends RestController
{
    public function __construct(
        private readonly ContentService\RelationListFacadeInterface $relationListFacade
    ) {
    }

    /**
     * The system creates a new draft version as a copy from the current version.
     *
     * @param mixed $contentId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if the current version is already a draft
     *
     * @return \Ibexa\Rest\Server\Values\CreatedVersion
     */
    public function createDraftFromCurrentVersion($contentId)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $contentInfo
        );

        if ($versionInfo->isDraft()) {
            throw new ForbiddenException('Current version already has DRAFT status');
        }

        $contentDraft = $this->repository->getContentService()->createContentDraft($contentInfo);

        return new Values\CreatedVersion(
            [
                'version' => new Values\Version(
                    $contentDraft,
                    $contentType,
                    iterator_to_array($this->relationListFacade->getRelations($contentDraft->getVersionInfo())),
                ),
            ]
        );
    }
}
