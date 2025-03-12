<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Exceptions;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/user/users/{userId}',
    openapi: new Model\Operation(
        summary: 'Delete User',
        description: 'Deletes the given User.',
        tags: [
            'User',
        ],
        parameters: [
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
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this User.',
            ],
            Response::HTTP_FORBIDDEN => [
                'description' => 'Error - the user is the same as the authenticated User.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the User does not exist.',
            ],
        ],
    ),
)]
final class UserDeleteController extends UserBaseController
{
    /**
     * Given user is deleted.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function deleteUser(int $userId): Values\NoContent
    {
        $user = $this->userService->loadUser($userId);

        if ($user->id == $this->permissionResolver->getCurrentUserReference()->getUserId()) {
            throw new Exceptions\ForbiddenException('Cannot delete the currently authenticated User');
        }

        $this->userService->deleteUser($user);

        return new Values\NoContent();
    }
}
