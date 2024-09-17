<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions as ApiExceptions;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\User\User as RepositoryUser;
use Ibexa\Contracts\Core\Repository\Values\User\UserGroupRoleAssignment;
use Ibexa\Contracts\Core\Repository\Values\User\UserRoleAssignment;
use Ibexa\Contracts\Rest\Exceptions\NotFoundException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Value as RestValue;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

#[Delete(
    uriTemplate: '/user/users/{userId}/groups/{groupId}',
    name: 'Unassign User Group',
    openapi: new Model\Operation(
        summary: 'Unassigns the User from a User Group.',
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
            new Model\Parameter(
                name: 'groupId',
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
                'description' => 'Error - the user is not authorized to unassign User Groups.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the User is not in the given group.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User does not exist.',
            ],
        ],
    ),
)]
final class UserUnassignFromUserGroupController extends UserBaseController
{
    /**
     * Unassigns the user from a user group.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function unassignUserFromUserGroup(int $userId, string $groupPath): Values\UserGroupRefList
    {
        $user = $this->userService->loadUser($userId);
        $userGroupLocation = $this->locationService->loadLocation((int)trim($groupPath, '/'));

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        try {
            $this->userService->unAssignUserFromUserGroup($user, $userGroup);
        } catch (ApiExceptions\InvalidArgumentException $e) {
            // User is not in the group
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        $userGroups = $this->userService->loadUserGroupsOfUser($user);
        $restUserGroups = [];
        foreach ($userGroups as $userGroup) {
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

        return new Values\UserGroupRefList(
            $restUserGroups,
            $this->router->generate(
                'ibexa.rest.load_user_groups_of_user',
                ['userId' => $userId]
            ),
            $userId
        );
    }
}
