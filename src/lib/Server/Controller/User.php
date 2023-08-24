<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Controller;

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
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

/**
 * User controller.
 */
class User extends RestController
{
    /**
     * User service.
     *
     * @var \Ibexa\Contracts\Core\Repository\UserService
     */
    protected $userService;

    /**
     * Role service.
     *
     * @var \Ibexa\Contracts\Core\Repository\RoleService
     */
    protected $roleService;

    /**
     * Content service.
     *
     * @var \Ibexa\Contracts\Core\Repository\ContentService
     */
    protected $contentService;

    /**
     * Content service.
     *
     * @var \Ibexa\Contracts\Core\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Location service.
     *
     * @var \Ibexa\Contracts\Core\Repository\LocationService
     */
    protected $locationService;

    /**
     * Section service.
     *
     * @var \Ibexa\Contracts\Core\Repository\SectionService
     */
    protected $sectionService;

    /**
     * Repository.
     *
     * @var \Ibexa\Contracts\Core\Repository\Repository
     */
    protected $repository;

    /**
     * @var \Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface
     *
     * @deprecated This property is deprecated since 6.5, and will be removed in 7.0.
     */
    private $csrfTokenStorage;

    /**
     * @var \Ibexa\Rest\Server\Controller\SessionController
     *
     * @deprecated This property is added for backward compatibility. It is deprecated, and will be removed in 7.0.
     */
    private $sessionController;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    public function __construct(
        UserService $userService,
        RoleService $roleService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService,
        SectionService $sectionService,
        Repository $repository,
        PermissionResolver $permissionResolver
    ) {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->sectionService = $sectionService;
        $this->repository = $repository;
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * Redirects to the root user group.
     *
     * @return \Ibexa\Rest\Server\Values\PermanentRedirect
     */
    public function loadRootUserGroup()
    {
        //@todo Replace hardcoded value with one loaded from settings
        return new Values\PermanentRedirect(
            $this->router->generate('ibexa.rest.load_user_group', ['groupPath' => '/1/5'])
        );
    }

    /**
     * Loads a user group for the given path.
     *
     * @param $groupPath
     *
     * @return \Ibexa\Rest\Server\Values\RestUserGroup
     */
    public function loadUserGroup($groupPath)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        if (trim($userGroupLocation->pathString, '/') != $groupPath) {
            throw new NotFoundException(
                "Could not find a Location with path string $groupPath"
            );
        }

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId,
            Language::ALL
        );
        $userGroupContentInfo = $userGroup->getVersionInfo()->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType($userGroupContentInfo->contentTypeId);

        return new Values\CachedValue(
            new Values\RestUserGroup(
                $userGroup,
                $contentType,
                $userGroupContentInfo,
                $userGroupLocation,
                $this->contentService->loadRelations($userGroup->getVersionInfo())
            ),
            ['locationId' => $userGroupLocation->id]
        );
    }

    /**
     * Loads a user for the given ID.
     *
     * @param $userId
     *
     * @return \Ibexa\Rest\Server\Values\RestUser
     */
    public function loadUser($userId)
    {
        $user = $this->userService->loadUser($userId, Language::ALL);

        $userContentInfo = $user->getVersionInfo()->getContentInfo();
        $contentType = $this->contentTypeService->loadContentType($userContentInfo->contentTypeId);

        try {
            $userMainLocation = $this->locationService->loadLocation($userContentInfo->mainLocationId);
            $relations = $this->contentService->loadRelations($user->getVersionInfo());
        } catch (UnauthorizedException $e) {
            // TODO: Hack for special case to allow current logged in user to load him/here self (but not relations)
            if ($user->id == $this->permissionResolver->getCurrentUserReference()->getUserId()) {
                $userMainLocation = $this->repository->sudo(
                    function () use ($userContentInfo) {
                        return $this->locationService->loadLocation($userContentInfo->mainLocationId);
                    }
                );
                // user may not have permissions to read related content, for security reasons do not use sudo().
                $relations = [];
            } else {
                throw $e;
            }
        }

        return new Values\CachedValue(
            new Values\RestUser(
                $user,
                $contentType,
                $userContentInfo,
                $userMainLocation,
                $relations
            ),
            ['locationId' => $userContentInfo->mainLocationId]
        );
    }

    /**
     * @see \Symfony\Component\Security\Http\Controller\UserValueResolver
     */
    public function redirectToCurrentUser(?UserInterface $user): Values\TemporaryRedirect
    {
        if ($user === null) {
            throw new UnauthorizedHttpException('', 'Not logged in.');
        }

        $userReference = $this->permissionResolver->getCurrentUserReference();

        return new Values\TemporaryRedirect(
            $this->router->generate('ibexa.rest.load_user', ['userId' => $userReference->getUserId()])
        );
    }

    /**
     * Create a new user group under the given parent
     * To create a top level group use /user/groups/1/5/subgroups.
     *
     * @param $groupPath
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedUserGroup
     */
    public function createUserGroup($groupPath, Request $request)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $createdUserGroup = $this->userService->createUserGroup(
            $this->inputDispatcher->parse(
                new Message(
                    ['Content-Type' => $request->headers->get('Content-Type')],
                    $request->getContent()
                )
            ),
            $this->userService->loadUserGroup(
                $userGroupLocation->contentId
            )
        );

        $createdContentInfo = $createdUserGroup->getVersionInfo()->getContentInfo();
        $createdLocation = $this->locationService->loadLocation($createdContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($createdContentInfo->contentTypeId);

        return new Values\CreatedUserGroup(
            [
                'userGroup' => new Values\RestUserGroup(
                    $createdUserGroup,
                    $contentType,
                    $createdContentInfo,
                    $createdLocation,
                    $this->contentService->loadRelations($createdUserGroup->getVersionInfo())
                ),
            ]
        );
    }

    /**
     * Create a new user group in the given group.
     *
     * @param $groupPath
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedUser
     */
    public function createUser($groupPath, Request $request)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );
        $userGroup = $this->userService->loadUserGroup($userGroupLocation->contentId);

        $userCreateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            $createdUser = $this->userService->createUser($userCreateStruct, [$userGroup]);
        } catch (ApiExceptions\InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $createdContentInfo = $createdUser->getVersionInfo()->getContentInfo();
        $createdLocation = $this->locationService->loadLocation($createdContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($createdContentInfo->contentTypeId);

        return new Values\CreatedUser(
            [
                'user' => new Values\RestUser(
                    $createdUser,
                    $contentType,
                    $createdContentInfo,
                    $createdLocation,
                    $this->contentService->loadRelations($createdUser->getVersionInfo())
                ),
            ]
        );
    }

    /**
     * Updates a user group.
     *
     * @param $groupPath
     *
     * @return \Ibexa\Rest\Server\Values\RestUserGroup
     */
    public function updateUserGroup($groupPath, Request $request)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $request->getPathInfo(),
                ],
                $request->getContent()
            )
        );

        if ($updateStruct->sectionId !== null) {
            $section = $this->sectionService->loadSection($updateStruct->sectionId);
            $this->sectionService->assignSection(
                $userGroup->getVersionInfo()->getContentInfo(),
                $section
            );
        }

        $updatedGroup = $this->userService->updateUserGroup($userGroup, $updateStruct->userGroupUpdateStruct);
        $contentType = $this->contentTypeService->loadContentType(
            $updatedGroup->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\RestUserGroup(
            $updatedGroup,
            $contentType,
            $updatedGroup->getVersionInfo()->getContentInfo(),
            $userGroupLocation,
            $this->contentService->loadRelations($updatedGroup->getVersionInfo())
        );
    }

    /**
     * Updates a user.
     *
     * @param $userId
     *
     * @return \Ibexa\Rest\Server\Values\RestUser
     */
    public function updateUser($userId, Request $request)
    {
        $user = $this->userService->loadUser($userId);

        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $request->getPathInfo(),
                ],
                $request->getContent()
            )
        );

        if ($updateStruct->sectionId !== null) {
            $section = $this->sectionService->loadSection($updateStruct->sectionId);
            $this->sectionService->assignSection(
                $user->getVersionInfo()->getContentInfo(),
                $section
            );
        }

        $updatedUser = $this->userService->updateUser($user, $updateStruct->userUpdateStruct);
        $updatedContentInfo = $updatedUser->getVersionInfo()->getContentInfo();
        $mainLocation = $this->locationService->loadLocation($updatedContentInfo->mainLocationId);
        $contentType = $this->contentTypeService->loadContentType($updatedContentInfo->contentTypeId);

        return new Values\RestUser(
            $updatedUser,
            $contentType,
            $updatedContentInfo,
            $mainLocation,
            $this->contentService->loadRelations($updatedUser->getVersionInfo())
        );
    }

    /**
     * Given user group is deleted.
     *
     * @param $groupPath
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteUserGroup($groupPath)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        // Load one user to see if user group is empty or not
        $users = $this->userService->loadUsersOfUserGroup($userGroup, 0, 1);
        if (!empty($users)) {
            throw new Exceptions\ForbiddenException('Cannot delete non-empty User Groups');
        }

        $this->userService->deleteUserGroup($userGroup);

        return new Values\NoContent();
    }

    /**
     * Given user is deleted.
     *
     * @param $userId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteUser($userId)
    {
        $user = $this->userService->loadUser($userId);

        if ($user->id == $this->permissionResolver->getCurrentUserReference()->getUserId()) {
            throw new Exceptions\ForbiddenException('Cannot delete the currently authenticated User');
        }

        $this->userService->deleteUser($user);

        return new Values\NoContent();
    }

    /**
     * Loads users.
     *
     * @return \Ibexa\Rest\Server\Values\UserList|\Ibexa\Rest\Server\Values\UserRefList
     */
    public function loadUsers(Request $request)
    {
        $restUsers = [];

        try {
            if ($request->query->has('roleId')) {
                $restUsers = $this->loadUsersAssignedToRole(
                    $this->requestParser->parseHref($request->query->get('roleId'), 'roleId')
                );
            } elseif ($request->query->has('remoteId')) {
                $restUsers = [
                    $this->buildRestUserObject(
                        $this->userService->loadUser(
                            $this->contentService->loadContentInfoByRemoteId($request->query->get('remoteId'))->id,
                            Language::ALL
                        )
                    ),
                ];
            } elseif ($request->query->has('login')) {
                $restUsers = [
                    $this->buildRestUserObject(
                        $this->userService->loadUserByLogin($request->query->get('login'), Language::ALL)
                    ),
                ];
            } elseif ($request->query->has('email')) {
                foreach ($this->userService->loadUsersByEmail($request->query->get('email'), Language::ALL) as $user) {
                    $restUsers[] = $this->buildRestUserObject($user);
                }
            }
        } catch (ApiExceptions\UnauthorizedException $e) {
            $restUsers = [];
        }

        if (empty($restUsers)) {
            throw new NotFoundException('Could not find Users with the given filter');
        }

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.userlist') {
            return new Values\UserList($restUsers, $request->getPathInfo());
        }

        return new Values\UserRefList($restUsers, $request->getPathInfo());
    }

    public function verifyUsers(Request $request)
    {
        // We let the NotFoundException loadUsers throws if there are no results pass.
        $this->loadUsers($request)->users;

        return new Values\OK();
    }

    /**
     * Loads a list of users assigned to role.
     *
     * @param mixed $roleId
     *
     * @return \Ibexa\Rest\Server\Values\RestUser[]
     */
    public function loadUsersAssignedToRole($roleId)
    {
        $role = $this->roleService->loadRole($roleId);
        $roleAssignments = $this->roleService->getRoleAssignments($role);

        $restUsers = [];

        foreach ($roleAssignments as $roleAssignment) {
            if ($roleAssignment instanceof UserRoleAssignment) {
                $restUsers[] = $this->buildRestUserObject($roleAssignment->getUser());
            }
        }

        return $restUsers;
    }

    /**
     * @return Values\RestUser
     */
    private function buildRestUserObject(RepositoryUser $user)
    {
        return new Values\RestUser(
            $user,
            $this->contentTypeService->loadContentType($user->contentInfo->contentTypeId),
            $user->contentInfo,
            $this->locationService->loadLocation($user->contentInfo->mainLocationId),
            $this->contentService->loadRelations($user->getVersionInfo())
        );
    }

    /**
     * Loads user groups.
     *
     * @return \Ibexa\Rest\Server\Values\UserGroupList|\Ibexa\Rest\Server\Values\UserGroupRefList
     */
    public function loadUserGroups(Request $request)
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
     *
     * @return \Ibexa\Rest\Server\Values\RestUserGroup
     */
    public function loadUserGroupByRemoteId(Request $request)
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
    public function loadUserGroupsAssignedToRole($roleId)
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

    /**
     * Loads drafts assigned to user.
     *
     * @param $userId
     *
     * @return \Ibexa\Rest\Server\Values\VersionList
     */
    public function loadUserDrafts($userId, Request $request)
    {
        $contentDrafts = $this->contentService->loadContentDrafts(
            $this->userService->loadUser($userId)
        );

        return new Values\VersionList($contentDrafts, $request->getPathInfo());
    }

    /**
     * Moves the user group to another parent.
     *
     * @param $groupPath
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\ResourceCreated
     */
    public function moveUserGroup($groupPath, Request $request)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $locationPath = $this->requestParser->parseHref(
            $request->headers->get('Destination'),
            'groupPath'
        );

        try {
            $destinationGroupLocation = $this->locationService->loadLocation(
                $this->extractLocationIdFromPath($locationPath)
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        try {
            $destinationGroup = $this->userService->loadUserGroup($destinationGroupLocation->contentId);
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        $this->userService->moveUserGroup($userGroup, $destinationGroup);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_user_group',
                [
                    'groupPath' => trim($destinationGroupLocation->pathString, '/') . '/' . $userGroupLocation->id,
                ]
            )
        );
    }

    /**
     * Returns a list of the sub groups.
     *
     * @param $groupPath
     *
     * @return \Ibexa\Rest\Server\Values\UserGroupList|\Ibexa\Rest\Server\Values\UserGroupRefList
     */
    public function loadSubUserGroups($groupPath, Request $request)
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
            $subGroupLocation = $this->locationService->loadLocation($subGroupContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($subGroupContentInfo->contentTypeId);

            $restUserGroups[] = new Values\RestUserGroup(
                $subGroup,
                $contentType,
                $subGroupContentInfo,
                $subGroupLocation,
                $this->contentService->loadRelations($subGroup->getVersionInfo())
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

    /**
     * Returns a list of user groups the user belongs to.
     * The returned list includes the resources for unassigning
     * a user group if the user is in multiple groups.
     *
     * @param $userId
     *
     * @return \Ibexa\Rest\Server\Values\UserGroupRefList
     */
    public function loadUserGroupsOfUser($userId, Request $request)
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

        return new Values\CachedValue(
            new Values\UserGroupRefList($restUserGroups, $request->getPathInfo(), $userId),
            ['locationId' => $user->contentInfo->mainLocationId]
        );
    }

    /**
     * Loads the users of the group with the given path.
     *
     * @param $groupPath
     *
     * @return \Ibexa\Rest\Server\Values\UserList|\Ibexa\Rest\Server\Values\UserRefList
     */
    public function loadUsersFromGroup($groupPath, Request $request)
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : 25;

        $users = $this->userService->loadUsersOfUserGroup(
            $userGroup,
            $offset >= 0 ? $offset : 0,
            $limit >= 0 ? $limit : 25,
            Language::ALL
        );

        $restUsers = [];
        foreach ($users as $user) {
            $userContentInfo = $user->getVersionInfo()->getContentInfo();
            $userLocation = $this->locationService->loadLocation($userContentInfo->mainLocationId);
            $contentType = $this->contentTypeService->loadContentType($userContentInfo->contentTypeId);

            $restUsers[] = new Values\RestUser(
                $user,
                $contentType,
                $userContentInfo,
                $userLocation,
                $this->contentService->loadRelations($user->getVersionInfo())
            );
        }

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.userlist') {
            return new Values\CachedValue(
                new Values\UserList($restUsers, $request->getPathInfo()),
                ['locationId' => $userGroupLocation->id]
            );
        }

        return new Values\CachedValue(
            new Values\UserRefList($restUsers, $request->getPathInfo()),
            ['locationId' => $userGroupLocation->id]
        );
    }

    /**
     * Unassigns the user from a user group.
     *
     * @param $userId
     * @param $groupPath
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\UserGroupRefList
     */
    public function unassignUserFromUserGroup($userId, $groupPath)
    {
        $user = $this->userService->loadUser($userId);
        $userGroupLocation = $this->locationService->loadLocation(trim($groupPath, '/'));

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

    /**
     * Assigns the user to a user group.
     *
     * @param $userId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\UserGroupRefList
     */
    public function assignUserToUserGroup($userId, Request $request)
    {
        $user = $this->userService->loadUser($userId);

        try {
            $userGroupLocation = $this->locationService->loadLocation(
                $this->extractLocationIdFromPath($request->query->get('group'))
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        try {
            $userGroup = $this->userService->loadUserGroup(
                $userGroupLocation->contentId
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new Exceptions\ForbiddenException($e->getMessage());
        }

        try {
            $this->userService->assignUserToUserGroup($user, $userGroup);
        } catch (ApiExceptions\NotFoundException $e) {
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

    /**
     * Creates a new session based on the credentials provided as POST parameters.
     *
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException If the login or password are incorrect or invalid CSRF
     *
     * @return Values\UserSession|Values\Conflict
     *
     * @deprecated Deprecated since 6.5. Use SessionController::refreshSessionAction().
     */
    public function createSession(Request $request)
    {
        @trigger_error(
            E_USER_DEPRECATED,
            'The session actions from the User controller are deprecated since 6.5. Use the SessionController instead.'
        );

        return $this->sessionController->createSessionAction($request);
    }

    /**
     * Refresh given session.
     *
     * @param string $sessionId
     *
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException if the CSRF token is missing or invalid
     *
     * @return \Ibexa\Rest\Server\Values\UserSession
     *
     * @deprecated Deprecated since 6.5. Use SessionController::refreshSessionAction().
     */
    public function refreshSession($sessionId, Request $request)
    {
        @trigger_error(
            E_USER_DEPRECATED,
            'The session actions from the User controller are deprecated since 6.5. Use the SessionController instead.'
        );

        return $this->sessionController->refreshSessionAction($sessionId, $request);
    }

    /**
     * Deletes given session.
     *
     * @param string $sessionId
     *
     * @return Values\DeletedUserSession|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException if the CSRF token is missing or invalid
     * @throws RestNotFoundException
     *
     * @deprecated Deprecated since 6.5. Use SessionController::refreshSessionAction().
     */
    public function deleteSession($sessionId, Request $request)
    {
        @trigger_error(
            E_USER_DEPRECATED,
            'The session actions from the User controller are deprecated since 6.5. Use the SessionController instead.'
        );

        return $this->sessionController->deleteSessionAction($sessionId, $request);
    }

    /**
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58.
     *
     * @param string $path
     *
     * @return mixed
     */
    private function extractLocationIdFromPath($path)
    {
        $pathParts = explode('/', $path);

        return array_pop($pathParts);
    }

    public function setTokenStorage(TokenStorageInterface $csrfTokenStorage)
    {
        @trigger_error(
            E_USER_DEPRECATED,
            'setTokenStorage() is deprecated since 6.5 and will be removed in 7.0.'
        );

        $this->csrfTokenStorage = $csrfTokenStorage;
    }

    public function setSessionController(SessionController $sessionController)
    {
        $this->sessionController = $sessionController;
    }
}

class_alias(User::class, 'EzSystems\EzPlatformRest\Server\Controller\User');
