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
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}/relations',
    name: 'Load Relations of content item version',
    openapi: new Model\Operation(
        summary: 'Loads the Relations of the given version.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Relation is returned in XML or JSON format.',
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
                    'application/vnd.ibexa.api.RelationList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RelationList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/GET/RelationList.xml.example',
                    ],
                    'application/vnd.ibexa.api.RelationList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RelationListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/GET/RelationList.json.example',
                    ],
                ],
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
class ContentVersionsRelationsListController extends RestController
{
    /**
     * Loads the relations of the given version.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @return \Ibexa\Rest\Server\Values\RelationList
     */
    public function loadVersionRelations($contentId, $versionNumber, Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : -1;

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $relationList = $this->repository->getContentService()->loadRelations(
            $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber)
        );

        $relationList = array_slice(
            $relationList,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : null
        );

        $relationListValue = new Values\RelationList(
            $relationList,
            $contentId,
            $versionNumber,
            $request->getPathInfo()
        );

        if ($contentInfo->mainLocationId === null) {
            return $relationListValue;
        }

        return new Values\CachedValue(
            $relationListValue,
            ['locationId' => $contentInfo->mainLocationId]
        );
    }
}
