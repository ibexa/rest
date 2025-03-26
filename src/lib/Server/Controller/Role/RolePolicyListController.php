<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/roles/{id}/policies',
    openapi: new Model\Operation(
        summary: 'Load Policies',
        description: 'Loads Policies for the given Role.',
        tags: [
            'User Role',
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
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role does not exist.',
            ],
        ],
    ),
)]
class RolePolicyListController extends RoleBaseController
{
    /**
     * Loads the policies for the role.
     */
    public function loadPolicies(int $roleId, Request $request): \Ibexa\Rest\Server\Values\PolicyList
    {
        $loadedRole = $this->roleService->loadRole($roleId);
        $policiesIterable = $loadedRole->getPolicies();
        $policies = [];
        foreach ($policiesIterable as $policy) {
            $policies[] = $policy;
        }

        return new Values\PolicyList($policies, $request->getPathInfo());
    }
}
