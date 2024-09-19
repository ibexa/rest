<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Rest\Exceptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/roles/{roleId}/policies/{policyId}',
    name: 'Load Policy',
    openapi: new Model\Operation(
        summary: 'Loads a Policy for the given module and function.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the Policy is returned in XML or JSON format.',
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
                name: 'policyId',
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
                    'application/vnd.ibexa.api.Policy+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Policy',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/id/PATCH/Policy.xml.example',
                    ],
                    'application/vnd.ibexa.api.Policy+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/PolicyWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/id/GET/Policy.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Roles.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role or Policy does not exist.',
            ],
        ],
    ),
)]
class RolePolicyLoadByIdController extends RoleBaseController
{
    /**
     * Loads a policy.
     *
     * @param $roleId
     * @param $policyId
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Policy
     */
    public function loadPolicy($roleId, $policyId, Request $request)
    {
        $loadedRole = $this->roleService->loadRole($roleId);
        foreach ($loadedRole->getPolicies() as $policy) {
            if ($policy->id == $policyId) {
                return $policy;
            }
        }

        throw new Exceptions\NotFoundException("Policy not found: '{$request->getPathInfo()}'.");
    }
}
