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

#[Post(
    uriTemplate: '/content/objects/{contentId}/versions/{versionNo}/relations',
    name: 'Create new Relation',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Relation of type COMMON for the given draft.',
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
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The RelationCreate schema encoded in XML or JSON format.',
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
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.RelationCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RelationCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/POST/RelationCreate.xml.example',
                ],
                'application/vnd.ibexa.api.RelationCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RelationCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/POST/RelationCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.Relation+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Relation',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/relation_id/GET/Relation.xml.example',
                    ],
                    'application/vnd.ibexa.api.Relation+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RelationWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/versions/version_no/relations/POST/Relation.json.example',
                    ],
                ],
            ],
        ],
    ),
)]
class ContentRelationCreateController extends RestController
{
    /**
     * Creates a new relation of type COMMON for the given draft.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if version $versionNumber isn't a draft
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if a relation to the same content already exists
     *
     * @return \Ibexa\Rest\Server\Values\CreatedRelation
     */
    public function createRelation($contentId, $versionNumber, Request $request)
    {
        $destinationContentId = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $versionInfo = $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber);
        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Relation of type COMMON can only be added to drafts');
        }

        try {
            $destinationContentInfo = $this->repository->getContentService()->loadContentInfo($destinationContentId);
        } catch (NotFoundException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $existingRelations = $this->repository->getContentService()->loadRelations($versionInfo);
        foreach ($existingRelations as $existingRelation) {
            if ($existingRelation->getDestinationContentInfo()->id == $destinationContentId) {
                throw new ForbiddenException('Relation of type COMMON to the selected destination content ID already exists');
            }
        }

        $relation = $this->repository->getContentService()->addRelation($versionInfo, $destinationContentInfo);

        return new Values\CreatedRelation(
            [
                'relation' => new Values\RestRelation($relation, $contentId, $versionNumber),
            ]
        );
    }
}
