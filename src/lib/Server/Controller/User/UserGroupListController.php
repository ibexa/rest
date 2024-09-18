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
use Ibexa\Contracts\Core\Repository\Values\User\UserGroupRoleAssignment;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/groups',
    name: 'Load User Groups',
    openapi: new Model\Operation(
        summary: 'Loads User Groups for either an an ID or a remote ID or a Role.',
        tags: [
            'User Group',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'UserGroupList - If set, the User Group List is returned in XML or JSON format. UserGroupRefList - If set, the link list of User Group is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
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
        ],
    ),
)]
final class UserGroupListController extends UserBaseController
{
    /**
     * Loads user groups.
     */
    public function loadUserGroups(Request $request): RestValue
    {
        $restUserGroups = [];
        if ($request->query->has('id')) {
            $userGroup = $this->userService->loadUserGroup($request->query->get('id'), Language::ALL);
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
            $userGroupMainLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

            $restUserGroups = [
                new Values\RestUserGroup(
                    $userGroup,
                    $contentType,
                    $userGroupContentInfo,
                    $userGroupMainLocation,
                    $this->contentService->loadRelations($userGroup->getVersionInfo())
                ),
            ];
        } elseif ($request->query->has('roleId')) {
            $restUserGroups = $this->loadUserGroupsAssignedToRole($request->query->get('roleId'));
        } elseif ($request->query->has('remoteId')) {
            $restUserGroups = [
                $this->loadUserGroupByRemoteId($request),
            ];
        }

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.usergrouplist') {
            return new Values\UserGroupList($restUserGroups, $request->getPathInfo());
        }

        return new Values\UserGroupRefList($restUserGroups, $request->getPathInfo());
    }

    /**
     * Loads a user group by its remote ID.
     */
    public function loadUserGroupByRemoteId(Request $request): Values\RestUserGroup
    {
        $contentInfo = $this->contentService->loadContentInfoByRemoteId($request->query->get('remoteId'));
        $userGroup = $this->userService->loadUserGroup($contentInfo->id, Language::ALL);
        $userGroupLocation = $this->locationService->loadLocation($contentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);

        return new Values\RestUserGroup(
            $userGroup,
            $contentType,
            $contentInfo,
            $userGroupLocation,
            $this->contentService->loadRelations($userGroup->getVersionInfo())
        );
    }

    /**
     * Loads a list of user groups assigned to role.
     *
     * @param mixed $roleId
     *
     * @return \Ibexa\Rest\Server\Values\RestUserGroup[]
     */
    public function loadUserGroupsAssignedToRole($roleId): array
    {
        $role = $this->roleService->loadRole($roleId);
        $roleAssignments = $this->roleService->getRoleAssignments($role);

        $restUserGroups = [];

        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment instanceof UserGroupRoleAssignment) {
                $userGroup = $roleAssignment->getUserGroup();
                $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
                $userGroupLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
                $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

                $restUserGroups[] = new Values\RestUserGroup(
                    $userGroup,
                    $contentType,
                    $userGroupContentInfo,
                    $userGroupLocation,
                    $this->contentService->loadRelations($userGroup->getVersionInfo())
                );
            }
        }

        return $restUserGroups;
    }
}
