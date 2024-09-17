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

#[Get(
    uriTemplate: '/user/policies',
    name: 'List Policies for User',
    openapi: new Model\Operation(
        summary: 'Search all Policies which are applied to a given User.',
        tags: [
            'User Policy',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Policy list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Policies which are applied to a given User.',
                'content' => [
                    'application/vnd.ibexa.api.PolicyList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/GET/PolicyList.xml.example',
                    ],
                    'application/vnd.ibexa.api.PolicyList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/GET/PolicyList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
        ],
    ),
)]
class RolePoliciesForUserListController extends RoleBaseController
{
    /**
     * Search all policies which are applied to a given user.
     *
     * @return \Ibexa\Rest\Server\Values\PolicyList
     */
    public function listPoliciesForUser(Request $request)
    {
        $user = $this->userService->loadUser((int)$request->query->get('userId'));
        $roleAssignments = $this->roleService->getRoleAssignmentsForUser($user, true);

        $policies = [];
        foreach ($roleAssignments as $roleAssignment) {
            $policies[] = $roleAssignment->getRole()->getPolicies();
        }

        return new Values\PolicyList(
            !empty($policies) ? array_merge(...$policies) : [],
            $request->getPathInfo()
        );
    }
}
