<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Patch(
    uriTemplate: '/content/objects/{contentId}',
    name: 'Update content',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'This method updates the content metadata which is independent from a version. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, all information for the content item (excluding the current version) is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-match',
                in: 'header',
                required: true,
                description: 'Causes to patch only if the specified ETag is the current one. Otherwise a 412 is returned.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The ContentUpdate schema encoded in XML or JSON format.',
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
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentInfo',
                    ],
                ],
                'application/vnd.ibexa.api.ContentUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentInfoWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/PATCH/ContentInfo.xml.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.ContentInfo+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentInfo',
                        ],
                    ],
                    'application/vnd.ibexa.api.ContentInfo+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentInfoWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/PATCH/ContentInfo.xml.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update this object.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the content ID does not exist.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the one provided in the If-Match header.',
            ],
            Response::HTTP_UNSUPPORTED_MEDIA_TYPE => [
                'description' => 'Error - the media-type is not one of those specified in headers.',
            ],
        ],
    ),
)]
class ContentMetadataUpdateController extends RestController
{
    /**
     * Updates a content's metadata.
     */
    public function updateContentMetadata(int $contentId, Request $request): Values\RestContent
    {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        // update section
        if ($updateStruct->sectionId !== null) {
            $section = $this->repository->getSectionService()->loadSection($updateStruct->sectionId);
            $this->repository->getSectionService()->assignSection($contentInfo, $section);
            $updateStruct->sectionId = null;
        }

        // @todo Consider refactoring! ContentService::updateContentMetadata throws the same exception
        // in case the updateStruct is empty and if remoteId already exists. Since REST version of update struct
        // includes section ID in addition to other fields, we cannot throw exception if only sectionId property
        // is set, so we must skip updating content in that case instead of allowing propagation of the exception.
        foreach ($updateStruct as $propertyName => $propertyValue) {
            if ($propertyName !== 'sectionId' && $propertyValue !== null) {
                // update content
                $this->repository->getContentService()->updateContentMetadata($contentInfo, $updateStruct);
                $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
                break;
            }
        }

        if ($contentInfo->mainLocationId === null) {
            throw new LogicException();
        }

        try {
            $locationInfo = $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);
        } catch (NotFoundException $e) {
            $locationInfo = null;
        }

        return new Values\RestContent(
            $contentInfo,
            $locationInfo
        );
    }
}
