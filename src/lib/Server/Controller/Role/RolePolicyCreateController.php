<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\LimitationValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/user/roles/{id}/policies',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Create Policy',
        description: 'Creates a Policy',
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
                name: 'id',
                in: 'path',
                required: true,
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.PolicyCreate+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/PolicyCreate',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/policies/POST/PolicyCreate.xml.example',
                ],
                'application/vnd.ibexa.api.PolicyCreate+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/PolicyCreateWrapper',
                    ],
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => [
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
                'description' => 'Error - the input does not match the input schema definition or validation of limitation in PolicyCreate fails.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create the Policy.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - the Role does not exist.',
            ],
        ],
    ),
)]
class RolePolicyCreateController extends RoleBaseController
{
    /**
     * Adds a policy to role.
     *
     * @param int $roleId ID of a role draft
     *
     * @return \Ibexa\Rest\Server\Values\CreatedPolicy
     */
    public function addPolicy($roleId, Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            // First try to treat $roleId as a role draft ID.
            $role = $this->roleService->addPolicyByRoleDraft(
                $this->roleService->loadRoleDraft($roleId),
                $createStruct
            );
        } catch (NotFoundException $e) {
            // Then try to treat $roleId as a role ID.
            $roleDraft = $this->roleService->createRoleDraft(
                $this->roleService->loadRole($roleId)
            );
            $roleDraft = $this->roleService->addPolicyByRoleDraft(
                $roleDraft,
                $createStruct
            );
            $this->roleService->publishRoleDraft($roleDraft);
            $role = $this->roleService->loadRole($roleId);
        } catch (LimitationValidationException $e) {
            throw new BadRequestException($e->getMessage());
        }

        return new Values\CreatedPolicy(
            [
                'policy' => $this->getLastAddedPolicy($role),
            ]
        );
    }
}
