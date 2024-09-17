<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Section;

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
class SectionLoadByIdController extends RestController
{
    protected SectionService $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
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
}
