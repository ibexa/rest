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

class UserBaseController extends RestController
{
    protected UserService $userService;

    protected RoleService $roleService;

    protected ContentService $contentService;

    protected ContentTypeService $contentTypeService;

    protected LocationService $locationService;

    protected SectionService $sectionService;

    /**
     * Repository.
     *
     * @var \Ibexa\Contracts\Core\Repository\Repository
     */
    protected $repository;

    protected PermissionResolver $permissionResolver;

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
     * Loads users.
     */
    public function loadUsers(Request $request): RestValue
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

    protected function buildRestUserObject(RepositoryUser $user): Values\RestUser
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
     * Extracts and returns an item id from a path, e.g. /1/2/58 => 58.
     */
    protected function extractLocationIdFromPath(string $path): int
    {
        $pathParts = explode('/', $path);

        return (int)array_pop($pathParts);
    }
}
