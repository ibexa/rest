<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values\PolicyList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/user/policies',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'List Policies for User',
        description: 'Search all Policies which are applied to a given User.',
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
    public function listPoliciesForUser(Request $request): PolicyList
    {
        $user = $this->userService->loadUser((int)$request->query->get('userId'));
        $roleAssignments = $this->roleService->getRoleAssignmentsForUser($user, true);

        $policies = [];
        foreach ($roleAssignments as $roleAssignment) {
            $policiesIterable = $roleAssignment->getRole()->getPolicies();
            $policiesArray = [];
            foreach ($policiesIterable as $policy) {
                $policiesArray[] = $policy;
            }
            $policies[] = $policiesArray;
        }

        return new PolicyList(
            !empty($policies) ? array_merge(...$policies) : [],
            $request->getPathInfo()
        );
    }
}
