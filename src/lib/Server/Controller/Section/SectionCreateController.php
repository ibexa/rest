<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Section;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/content/sections',
    name: 'Create new Section',
    openapiContext: [
        'summary' => 'Creates a new Section.',
        'tags' => [
            'Section',
        ],
        'parameters' => [
            [
                'name' => 'Accept',
                'in' => 'header',
                'required' => true,
                'description' => 'If set, the new Section is returned in XML or JSON format.',
                'schema' => [
                    'type' => 'string',
                ],
            ],
            [
                'name' => 'Content-Type',
                'in' => 'header',
                'required' => true,
                'description' => 'The Section input schema encoded in XML or JSON format.',
                'schema' => [
                    'type' => 'string',
                ],
            ],
        ],
        'requestBody' => [
            'content' => [
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
            ],
        ],
        'responses' => [
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
    ],
)]
class SectionCreateController extends RestController
{
    protected SectionService $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
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
}
