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

final class UserGroupMoveController extends UserBaseController
{
    /**
     * Moves the user group to another parent.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function moveUserGroup(string $groupPath, Request $request): Values\ResourceCreated
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
     * @throws \Ibexa\Contracts\Rest\Exceptions\ForbiddenException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function moveGroup(string $groupPath, Request $request): Values\ResourceCreated
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId,
        );

        try {
            /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $destinationLocation */
            $destinationLocation = $this->inputDispatcher->parse(
                new Message(
                    ['Content-Type' => $request->headers->get('Content-Type')],
                    $request->getContent(),
                ),
            );
        } catch (ApiExceptions\NotFoundException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage(), 1, $e);
        }

        $destinationGroup = $this->userService->loadUserGroup(
            $destinationLocation->getContent()->getId(),
        );

        $this->userService->moveUserGroup($userGroup, $destinationGroup);

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_user_group',
                [
                    'groupPath' => trim($destinationLocation->pathString, '/')
                        . '/'
                        . $userGroupLocation->getId(),
                ],
            )
        );
    }
}
