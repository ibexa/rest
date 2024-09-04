<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\SectionCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\SectionUpdateStruct;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\NoContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/content/sections',
    name: 'Create new Section',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Section.',
        tags: [
            'Section',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new Section is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The Section input schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.SectionInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/SectionInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/POST/SectionInput.xml.example',
                ],
                'application/vnd.ibexa.api.SectionInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/SectionInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/POST/SectionInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.Section+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Section',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/section_id/PATCH/Section.xml.example',
                    ],
                    'application/vnd.ibexa.api.Section+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/SectionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/section_id/PATCH/Section.json.example',
                    ],
                ],
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/sections',
    name: 'Get Sections',
    openapi: new Model\Operation(
        summary: 'Returns a list of all Sections.',
        tags: [
            'Section',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Section list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'ETag',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.SectionList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/SectionList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/GET/SectionList.xml.example',
                    ],
                    'application/vnd.ibexa.api.SectionList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/SectionListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/GET/SectionList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read the Section.',
            ],
        ],
    ),
)]
#[Get(
    uriTemplate: '/content/sections/{sectionId}',
    name: 'Get Section',
    openapi: new Model\Operation(
        summary: 'Returns the Section by given Section ID.',
        tags: [
            'Section',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Section is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-None-match',
                in: 'header',
                required: true,
                description: 'ETag',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'sectionId',
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
                    'application/vnd.ibexa.api.Section+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Section',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/section_id/PATCH/Section.xml.example',
                    ],
                    'application/vnd.ibexa.api.Section+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/SectionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/section_id/PATCH/Section.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to read this Section.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The Section does not exist.',
            ],
        ],
    ),
)]
#[Patch(
    uriTemplate: '/content/sections/{sectionId}',
    name: 'Update a Section',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates a Section. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Section',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Section is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The Section input schema encoded in XML or JSON.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'ETag',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'sectionId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.SectionInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/SectionInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/POST/SectionInput.xml.example',
                ],
                'application/vnd.ibexa.api.SectionInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/SectionInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/POST/SectionInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Section updated.',
                'content' => [
                    'application/vnd.ibexa.api.Section+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Section',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/section_id/PATCH/Section.xml.example',
                    ],
                    'application/vnd.ibexa.api.Section+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/SectionWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/sections/section_id/PATCH/Section.json.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create this Section.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - a Section with the given identifier already exists.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the one provided in the If-Match header.',
            ],
        ],
    ),
)]
#[Delete(
    uriTemplate: '/content/sections/{sectionId}',
    name: 'Delete Section',
    openapi: new Model\Operation(
        summary: 'The given Section is deleted.',
        tags: [
            'Section',
        ],
        parameters: [
            new Model\Parameter(
                name: 'sectionId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - given Section is deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this Section.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Section does not exist.',
            ],
        ],
    ),
)]
/**
 * Section controller.
 */
class Section extends RestController
{
    /**
     * Section service.
     *
     * @var \Ibexa\Contracts\Core\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Construct controller.
     *
     * @param \Ibexa\Contracts\Core\Repository\SectionService $sectionService
     */
    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * List sections.
     *
     * @return \Ibexa\Rest\Server\Values\SectionList
     */
    public function listSections(Request $request)
    {
        if ($request->query->has('identifier')) {
            $sections = [
                $this->loadSectionByIdentifier($request),
            ];
        } else {
            $sections = $this->sectionService->loadSections();
        }

        return new Values\SectionList($sections, $request->getPathInfo());
    }

    /**
     * Loads section by identifier.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section
     */
    public function loadSectionByIdentifier(Request $request)
    {
        return $this->sectionService->loadSectionByIdentifier(
            // GET variable
            $request->query->get('identifier')
        );
    }

    /**
     * Create new section.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedSection
     */
    public function createSection(Request $request)
    {
        try {
            $createdSection = $this->sectionService->createSection(
                $this->inputDispatcher->parse(
                    new Message(
                        ['Content-Type' => $request->headers->get('Content-Type')],
                        $request->getContent()
                    )
                )
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new Values\CreatedSection(
            [
                'section' => $createdSection,
            ]
        );
    }

    /**
     * Loads a section.
     *
     * @param $sectionId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section
     */
    public function loadSection($sectionId)
    {
        return $this->sectionService->loadSection($sectionId);
    }

    /**
     * Updates a section.
     *
     * @param $sectionId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section
     */
    public function updateSection($sectionId, Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            return $this->sectionService->updateSection(
                $this->sectionService->loadSection($sectionId),
                $this->mapToUpdateStruct($createStruct)
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }

    /**
     * Delete a section by ID.
     *
     * @param $sectionId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteSection($sectionId)
    {
        $this->sectionService->deleteSection(
            $this->sectionService->loadSection($sectionId)
        );

        return new NoContent();
    }

    /**
     * Maps a SectionCreateStruct to a SectionUpdateStruct.
     *
     * Needed since both structs are encoded into the same media type on input.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\SectionCreateStruct $createStruct
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\SectionUpdateStruct
     */
    protected function mapToUpdateStruct(SectionCreateStruct $createStruct)
    {
        return new SectionUpdateStruct(
            [
                'name' => $createStruct->name,
                'identifier' => $createStruct->identifier,
            ]
        );
    }
}
