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
    uriTemplate: '/user/users/{userId}/groups',
    name: 'Load Groups of User',
    openapi: new Model\Operation(
        summary: 'Returns a list of User Groups the User belongs to. The returned list includes the resources for unassigning a User Group if the User is in multiple groups.',
        tags: [
            'User',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the link list of User Groups is returned in XML or JSON format.',
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
                'content' => [
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
                'description' => 'Error - the user does not exist.',
            ],
        ],
    ),
)]
final class UserGroupsOfUserListController extends UserBaseController
{
    /**
     * Returns a list of user groups the user belongs to.
     * The returned list includes the resources for unassigning
     * a user group if the user is in multiple groups.
     */
    public function loadUserGroupsOfUser(int $userId, Request $request): RestValue
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $user = $this->userService->loadUser($userId);
        $userGroups = $this->userService->loadUserGroupsOfUser(
            $user,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25,
            Language::ALL
        );

        $restUserGroups = [];
        foreach ($userGroups as $userGroup) {
            $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();

            if ($userGroupContentInfo->mainLocationId === null) {
                throw new LogicException();
            }

            $userGroupLocation = $this->locationService->loadLocation($userGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                iterator_to_array($this->relationListFacade->getRelations($userGroup->getVersionInfo())),
            );
        }

        return new Values\CachedValue(
            new Values\UserGroupRefList($restUserGroups, $request->getPathInfo(), $userId),
            ['locationId' => $user->contentInfo->mainLocationId]
        );
    }
}
