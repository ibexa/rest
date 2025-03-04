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
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ContentFieldValidationException as RESTContentFieldValidationException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\RestContentCreateStruct;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/content/objects',
    name: 'Create content item',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a draft assigned to the authenticated user. If a different user ID is given in the input, the draft is assigned to the given user but this action requires special permissions for the authenticated user (this is useful for content staging where the transfer process does not have to authenticate with the user who created the content item in the source server). The user needs to publish the content item if it should be visible.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'Content - If set, all information for the content item including the embedded current version is returned in XML or JSON format. ContentInfo - If set, all information for the content item (excluding the current version) is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The ContentCreate schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ContentCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/POST/ContentCreate.xml.example',
                ],
                'application/vnd.ibexa.api.ContentCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ContentCreateWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/POST/ContentCreate.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.Content+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Content',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/GET/Content.xml.example',
                    ],
                    'application/vnd.ibexa.api.Content+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/GET/Content.json.example',
                    ],
                    'application/vnd.ibexa.api.ContentInfo+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentInfoWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/PATCH/ContentInfo.xml.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition or the validation on a field fails.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create this Object in this Location.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the parent Location specified in the request body does not exist.',
            ],
        ],
    ),
)]
class ContentCreateController extends RestController
{
    public function __construct(
        private readonly ContentService\RelationListFacadeInterface $relationListFacade
    ) {
    }

    /**
     * Creates a new content draft assigned to the authenticated user.
     * If a different userId is given in the input it is assigned to the
     * given user but this required special rights for the authenticated
     * user (this is useful for content staging where the transfer process
     * does not have to authenticate with the user which created the content
     * object in the source server). The user has to publish the content if
     * it should be visible.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Ibexa\Rest\Server\Values\CreatedContent
     */
    public function createContent(Request $request)
    {
        $contentCreate = $this->parseContentRequest($request);

        return $this->doCreateContent($request, $contentCreate);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return mixed
     */
    protected function parseContentRequest(Request $request)
    {
        return $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type'), 'Url' => $request->getPathInfo()],
                $request->getContent()
            )
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Rest\Server\Values\RestContentCreateStruct $contentCreate
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedContent
     */
    protected function doCreateContent(Request $request, RestContentCreateStruct $contentCreate)
    {
        try {
            $contentCreateStruct = $contentCreate->contentCreateStruct;
            $contentCreate->locationCreateStruct->sortField = $contentCreateStruct->contentType->defaultSortField;
            $contentCreate->locationCreateStruct->sortOrder = $contentCreateStruct->contentType->defaultSortOrder;

            $content = $this->repository->getContentService()->createContent(
                $contentCreateStruct,
                [$contentCreate->locationCreateStruct]
            );
        } catch (ContentValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentFieldValidationException $e) {
            throw new RESTContentFieldValidationException($e);
        }

        $contentValue = null;
        $contentType = null;
        $relations = null;
        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.content') {
            $contentValue = $content;
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $content->getVersionInfo()->getContentInfo()->contentTypeId
            );
            $relations = iterator_to_array($this->relationListFacade->getRelations($contentValue->getVersionInfo()));
        }

        return new Values\CreatedContent(
            [
                'content' => new Values\RestContent(
                    $content->contentInfo,
                    null,
                    $contentValue,
                    $contentType,
                    $relations
                ),
            ]
        );
    }
}
