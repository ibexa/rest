<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\ObjectState;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Values\ContentObjectStates;
use Ibexa\Rest\Values\RestObjectState;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/content/objects/{contentId}/objectstates',
    name: 'Get Object states of content item',
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
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/objectstates/PATCH/ContentObjectStates.response.xml.example',
                    ],
                    'application/vnd.ibexa.api.ContentObjectStates+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ContentObjectStatesWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/content/objects/content_id/objectstates/GET/ContentObjectStates.json.example',
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
     *
     * @param $contentId
     *
     * @return \Ibexa\Rest\Values\ContentObjectStates
     */
    public function getObjectStatesForContent($contentId)
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
