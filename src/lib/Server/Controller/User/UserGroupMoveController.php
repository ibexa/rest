<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use Ibexa\Contracts\Core\Repository\Exceptions as ApiExceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Exceptions;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;

final class UserGroupMoveController extends UserBaseController
{
    /**
     * Moves the user group to another parent.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function moveUserGroup(string $groupPath, Request $request): Values\ResourceCreated
    {
        $userGroupLocation = $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($groupPath)
        );

        $userGroup = $this->userService->loadUserGroup(
            $userGroupLocation->contentId
        );

        $locationPath = $this->uriParser->getAttributeFromUri(
            (string)$request->headers->get('Destination'),
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
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
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
