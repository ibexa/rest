<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\LimitationValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\User\Policy;
use Ibexa\Contracts\Core\Repository\Values\User\PolicyDraft;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Patch(
    uriTemplate: '/user/roles/{roleId}/policies/{policyId}',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Update Policy',
        description: 'Updates a Policy. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Policy is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'If set, the updated Policy is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'Causes to patch only if the specified ETag is the current one. Otherwise a 412 is returned.',
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
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.PolicyUpdate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/PolicyUpdate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/id/PATCH/PolicyUpdate.xml.example',
                ],
                'application/vnd.ibexa.api.PolicyUpdate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/PolicyUpdateWrapper',
                    ],
                ],
            ]),
        ),
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition or validation of limitation in PolicyUpdate fails.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update the Policy.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role does not exist.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the one provided in the If-Match header.',
            ],
        ],
    ),
)]
class RolePolicyUpdateController extends RoleBaseController
{
    /**
     * Updates a policy.
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     */
    public function updatePolicy(int $roleId, int $policyId, Request $request): Policy
    {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );
        try {
            // First try to treat $roleId as a role draft ID.
            $roleDraft = $this->roleService->loadRoleDraft($roleId);
            foreach ($roleDraft->getPolicies() as $policy) {
                assert($policy instanceof PolicyDraft);

                if ($policy->id == $policyId) {
                    try {
                        return $this->roleService->updatePolicyByRoleDraft(
                            $roleDraft,
                            $policy,
                            $updateStruct
                        );
                    } catch (LimitationValidationException $e) {
                        throw new BadRequestException($e->getMessage());
                    }
                }
            }
        } catch (NotFoundException $e) {
            // Then try to treat $roleId as a role ID.
            $roleDraft = $this->roleService->createRoleDraft(
                $this->roleService->loadRole($roleId)
            );
            foreach ($roleDraft->getPolicies() as $policy) {
                assert($policy instanceof PolicyDraft);

                if ($policy->originalId == $policyId) {
                    try {
                        $policyDraft = $this->roleService->updatePolicyByRoleDraft(
                            $roleDraft,
                            $policy,
                            $updateStruct
                        );
                        $this->roleService->publishRoleDraft($roleDraft);
                        $role = $this->roleService->loadRole($roleId);

                        foreach ($role->getPolicies() as $newPolicy) {
                            if ($newPolicy->id == $policyDraft->id) {
                                return $newPolicy;
                            }
                        }
                    } catch (LimitationValidationException $e) {
                        throw new BadRequestException($e->getMessage());
                    }
                }
            }
        }

        throw new Exceptions\NotFoundException("Policy not found: '{$request->getPathInfo()}'.");
    }
}
