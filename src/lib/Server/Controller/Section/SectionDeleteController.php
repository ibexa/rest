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
class SectionDeleteController extends RestController
{
    protected SectionService $sectionService;

    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
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
}
