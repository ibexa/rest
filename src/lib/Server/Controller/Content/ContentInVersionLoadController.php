<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ContentFieldValidationException as RESTContentFieldValidationException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\RestContentCreateStruct;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[Get(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}',
    name: 'Load version',
    openapi: new Model\Operation(
        summary: 'Loads a specific version of a content item. This method returns Fields and relations.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'Only return the version if the given ETag is the not current one, otherwise a 304 is returned.',
                schema: [
                    'type' => 'string',
                ],
            ),
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
            new Model\Parameter(
                name: 'versionNo',
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
            Response::HTTP_NOT_MODIFIED => [
                'description' => 'Error - the ETag does not match the current one.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to read this content item.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the ID or version is not found.',
            ],
        ],
    ),
)]
class ContentInVersionLoadController extends RestController
{
    /**
     * Loads a specific version of a given content object.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     *
     * @return \Ibexa\Rest\Server\Values\Version
     */
    public function loadContentInVersion($contentId, $versionNumber, Request $request)
    {
        $languages = Language::ALL;
        if ($request->query->has('languages')) {
            $languages = explode(',', $request->query->get('languages'));
        }

        $content = $this->repository->getContentService()->loadContent(
            $contentId,
            $languages,
            $versionNumber
        );
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );

        $versionValue = new Values\Version(
            $content,
            $contentType,
            $this->repository->getContentService()->loadRelations($content->getVersionInfo()),
            $request->getPathInfo()
        );

        if ($content->contentInfo->mainLocationId === null || $content->versionInfo->status === VersionInfo::STATUS_DRAFT) {
            return $versionValue;
        }

        return new Values\CachedValue(
            $versionValue,
            ['locationId' => $content->contentInfo->mainLocationId]
        );
    }
}
