<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Section;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\NoContent;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/content/sections/{sectionId}',
    openapi: new Model\Operation(
        summary: 'Delete Section',
        description: 'The given Section is deleted.',
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
     */
    public function deleteSection(int $sectionId): NoContent
    {
        $this->sectionService->deleteSection(
            $this->sectionService->loadSection($sectionId)
        );

        return new NoContent();
    }
}
