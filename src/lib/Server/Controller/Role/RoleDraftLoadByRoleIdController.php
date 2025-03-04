<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/roles/{id}/draft',
    name: 'Load Role draft',
    openapi: new Model\Operation(
        summary: 'Loads a Role draft by original Role ID.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the User list returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-None-Match',
                in: 'header',
                required: true,
                description: 'ETag',
                schema: [
                    'type' => 'string',
                ],
            ),
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
            Response::HTTP_OK => [
                'description' => 'OK - Role draft by original Role ID.',
                'content' => [
                    'application/vnd.ibexa.api.Role+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Role',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.xml.example',
                    ],
                    'application/vnd.ibexa.api.Role+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - there is no draft or Role with the given ID.',
            ],
        ],
    ),
)]
class RoleDraftLoadByRoleIdController extends RoleBaseController
{
    /**
     * Loads a role draft.
     *
     * @param mixed $roleId Original role ID, or ID of the role draft itself
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\RoleDraft
     */
    public function loadRoleDraft($roleId)
    {
        try {
            // First try to load the draft for given role.
            return $this->roleService->loadRoleDraftByRoleId($roleId);
        } catch (NotFoundException $e) {
            // We might want a newly created role, so try to load it by its ID.
            // loadRoleDraft() might throw a NotFoundException (wrong $roleId). If so, let it bubble up.
            return $this->roleService->loadRoleDraft($roleId);
        }
    }
}
