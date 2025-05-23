<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Values\Content\DraftList\ContentDraftListItemInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/users/{userId}/drafts',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Load user drafts',
        description: 'Loads user\'s drafts',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the version list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'userId',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - List the draft versions',
                'content' => [
                    'application/vnd.ibexa.api.VersionList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionList',
                        ],
                    ],
                    'application/vnd.ibexa.api.VersionList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/VersionListWrapper',
                        ],
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the current user is not authorized to list the drafts of the given user.',
            ],
        ],
    ),
)]
final class UserDraftListController extends UserBaseController
{
    /**
     * Loads drafts assigned to user.
     */
    public function loadUserDrafts(int $userId, Request $request): Values\VersionList
    {
        $contentDrafts = $this->contentService->loadContentDraftList(
            $this->userService->loadUser($userId)
        );

        return new Values\VersionList(
            array_filter(array_map(
                static fn (ContentDraftListItemInterface $draftListItem): ?VersionInfo => $draftListItem->getVersionInfo(),
                $contentDrafts->items,
            )),
            $request->getPathInfo()
        );
    }
}
