<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Section;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Section;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/sections',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Get Sections',
        description: 'Returns a list of all Sections.',
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
            //Response::HTTP_NOT_FOUND => [],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user has no permission to read the Section.',
            ],
        ],
    ),
)]
class SectionListController extends RestController
{
    protected SectionService $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * List sections.
     */
    public function listSections(Request $request): Values\SectionList
    {
        if ($request->query->has('identifier')) {
            $sections = [
                $this->loadSectionByIdentifier($request),
            ];
        } else {
            $sectionsIterable = $this->sectionService->loadSections();
            $sections = [];
            foreach ($sectionsIterable as $section) {
                $sections[] = $section;
            }
        }

        return new Values\SectionList($sections, $request->getPathInfo());
    }

    /**
     * Loads section by identifier.
     */
    public function loadSectionByIdentifier(Request $request): Section
    {
        return $this->sectionService->loadSectionByIdentifier(
            // GET variable
            $request->query->getString('identifier')
        );
    }
}
