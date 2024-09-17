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

#[Patch(
    uriTemplate: '/content/objects/{contentId}/objectstates',
    name: 'Set Object states of content item',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates Object states of a content item. An Object state in the input overrides the state of the Object state group. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'Objects',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Object state is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The content item Object states input schema encoded in XML or JSON format.',
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
            ]),
        ),
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'OK - Object state updated.',
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - The input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - The user is not authorized to set an Object state.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - The input contains multiple Object states of the same Object state group.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - The current ETag does not match the one provided in the If-Match header.',
            ],
        ],
    ),
)]
class ObjectStatesForContentUpdateController extends RestController
{
    protected ObjectStateService $objectStateService;

    protected ContentService $contentService;

    public function __construct(ObjectStateService $objectStateService, ContentService $contentService)
    {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * Updates object states of content
     * An object state in the input overrides the state of the object state group.
     *
     * @param $contentId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Values\ContentObjectStates
     */
    public function setObjectStatesForContent($contentId, Request $request)
    {
        $newObjectStates = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $countByGroups = [];
        foreach ($newObjectStates as $newObjectState) {
            $groupId = (int)$newObjectState->groupId;
            if (array_key_exists($groupId, $countByGroups)) {
                ++$countByGroups[$groupId];
            } else {
                $countByGroups[$groupId] = 1;
            }
        }

        foreach ($countByGroups as $groupId => $count) {
            if ($count > 1) {
                throw new ForbiddenException(
                /** @Ignore */
                    "Multiple Object states provided for group with ID $groupId"
                );
            }
        }

        $contentInfo = $this->contentService->loadContentInfo($contentId);

        $contentObjectStates = [];
        foreach ($newObjectStates as $newObjectState) {
            $objectStateGroup = $this->objectStateService->loadObjectStateGroup($newObjectState->groupId);
            $this->objectStateService->setContentState($contentInfo, $objectStateGroup, $newObjectState->objectState);
            $contentObjectStates[(int)$objectStateGroup->id] = $newObjectState;
        }

        return new ContentObjectStates($contentObjectStates);
    }
}
