<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ObjectState;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Values\ContentObjectStates;
use Ibexa\Rest\Values\RestObjectState;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/objects/{contentId}/objectstates',
    name: 'Get Object states of content item',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Returns the Object states of a content item',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Object states are returned in XML or JSON format.',
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
            new Model\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - returns the Object state.',
                'content' => [
                    'application/vnd.ibexa.api.ContentObjectStates+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentObjectStates',
                        ],
                    ],
                    'application/vnd.ibexa.api.ContentObjectStates+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentObjectStates',
                        ],
                    ],
                ],
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - The content item does not exist.',
            ],
        ],
    ),
)]
class ObjectStatesForContentListController extends RestController
{
    protected ObjectStateService $objectStateService;

    protected ContentService $contentService;

    public function __construct(ObjectStateService $objectStateService, ContentService $contentService)
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * Returns the object states of content.
     */
    public function getObjectStatesForContent(int $contentId): \Ibexa\Rest\Values\ContentObjectStates
    {
        $groups = $this->objectStateService->loadObjectStateGroups();
        $contentInfo = $this->contentService->loadContentInfo($contentId);

        $contentObjectStates = [];

        foreach ($groups as $group) {
            try {
                $state = $this->objectStateService->getContentState($contentInfo, $group);
                $contentObjectStates[] = new RestObjectState($state, $group->id);
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        return new ContentObjectStates($contentObjectStates);
    }
}
