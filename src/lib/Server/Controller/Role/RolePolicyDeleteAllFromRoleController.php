<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\LimitationValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException as APINotFoundException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\Values\User\RoleCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\User\RoleUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Core\Base\Exceptions\ForbiddenException;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Delete(
    uriTemplate: '/user/roles/{id}/policies',
    name: 'Delete Policies',
    openapi: new Model\Operation(
        summary: 'All Policies of the given Role are deleted.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_NO_CONTENT => [
                'description' => 'No Content - all Policies of the given Role are deleted.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to delete this content type.',
            ],
        ],
    ),
)]
class RolePolicyDeleteAllFromRoleController extends RoleBaseController
{
    /**
     * Deletes all policies from a role.
     *
     * @param $roleId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deletePolicies($roleId)
    {
        $loadedRole = $this->roleService->loadRole($roleId);
        $roleDraft = $this->roleService->createRoleDraft($loadedRole);
        /** @var \Ibexa\Contracts\Core\Repository\Values\User\PolicyDraft $policyDraft */
        foreach ($roleDraft->getPolicies() as $policyDraft) {
            $this->roleService->removePolicyByRoleDraft($roleDraft, $policyDraft);
        }
        $this->roleService->publishRoleDraft($roleDraft);

        return new Values\NoContent();
    }
}
