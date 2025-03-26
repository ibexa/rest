<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/groups/{path}/subgroups',
    openapi: new Model\Operation(
        summary: 'Load subgroups',
        description: 'Returns a list of the subgroups.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'UserGroupList - If set, the User Group list is returned in XML or JSON format. UserGroupRefList - If set, the link list of User Groups is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'path',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - list of the subgroups.',
                'content' => [
                    'application/vnd.ibexa.api.UserGroupList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/GET/UserGroupList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/groups/GET/UserGroupList.json.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupRefList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/POST/UserGroupRefList.xml.example',
                    ],
                    'application/vnd.ibexa.api.UserGroupRefList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserGroupRefListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/groups/group_id/UserGroupRefList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read User Groups.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User Group does not exist.',
            ],
        ],
    ),
)]
final class UserSubGroupListController extends UserBaseController
{
    /**
     * Returns a list of the sub groups.
     */
    public function loadSubUserGroups(string $groupPath, Request $request): RestValue
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $subGroups = $this->userService->loadSubUserGroups(
            $userGroup,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25,
            Language::ALL
        );

        $restUserGroups = [];
        foreach ($subGroups as $subGroup) {
            $subGroupContentInfo = $subGroup->getVersionInfo()->getContentInfo();

            if ($subGroupContentInfo->mainLocationId === null) {
                throw new LogicException();
            }

            $subGroupLocation = $this->locationService->loadLocation($subGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($subGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $subGroup,
                $contentType,
                $subGroupContentInfo,
                $subGroupLocation,
                iterator_to_array($this->relationListFacade->getRelations($userGroup->getVersionInfo())),
            );
        }

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.usergrouplist') {
            return new Values\CachedValue(
                new Values\UserGroupList($restUserGroups, $request->getPathInfo()),
                ['locationId' => $userGroupLocation->id]
            );
        }

        return new Values\CachedValue(
            new Values\UserGroupRefList($restUserGroups, $request->getPathInfo()),
            ['locationId' => $userGroupLocation->id]
        );
    }
}
