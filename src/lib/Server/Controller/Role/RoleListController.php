<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException as APINotFoundException;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/roles',
    name: 'Load Roles',
    openapi: new Model\Operation(
        summary: 'Returns a list of all Roles.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the user list returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - list of all Roles.',
                'content' => [
                    'application/vnd.ibexa.api.RoleList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/GET/RoleList.xml.example',
                    ],
                    'application/vnd.ibexa.api.RoleList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/GET/RoleList.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
        ],
    ),
)]
class RoleListController extends RoleBaseController
{
    /**
     * Loads list of roles.
     */
    public function listRoles(Request $request): \Ibexa\Rest\Server\Values\RoleList
    {
        $roles = [];
        if ($request->query->has('identifier')) {
            try {
                $role = $this->roleService->loadRoleByIdentifier((string)$request->query->get('identifier'));
                $roles[] = $role;
            } catch (APINotFoundException $e) {
                // Do nothing
            }
        } else {
            $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
            $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : -1;

            $rolesArray = [];
            foreach ($this->roleService->loadRoles() as $role) {
                $rolesArray[] = $role;
            }

            $roles = array_slice(
                $rolesArray,
                $offset >= 0 ? $offset : 0,
                $limit >= 0 ? $limit : null
            );
        }

        return new Values\RoleList($roles, $request->getPathInfo());
    }
}
