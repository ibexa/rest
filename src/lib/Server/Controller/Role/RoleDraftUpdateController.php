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

#[Patch(
    uriTemplate: '/user/roles/{id}/draft',
    name: 'Update Role draft',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Updates a Role draft. PATCH or POST with header X-HTTP-Method-Override PATCH.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the updated Role is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The RoleInput schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'If-Match',
                in: 'header',
                required: true,
                description: 'Performs a PATCH only if the specified ETag is the current one. Otherwise a 412 is returned.',
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
                'application/vnd.ibexa.api.RoleInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/RoleInput.xml.example',
                ],
                'application/vnd.ibexa.api.RoleInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/RoleInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - Role draft updated.',
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
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to update the Role.',
            ],
            Response::HTTP_NOT_FOUND => [
                'description' => 'Error - there is no draft or Role with the given ID.',
            ],
            Response::HTTP_PRECONDITION_FAILED => [
                'description' => 'Error - the current ETag does not match with the one provided in the If-Match header.',
            ],
        ],
    ),
)]
class RoleDraftUpdateController extends RoleBaseController
{
    /**
     * Updates a role draft.
     *
     * @param mixed $roleId Original role ID, or ID of the role draft itself
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\RoleDraft
     */
    public function updateRoleDraft($roleId, Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            // First try to load the draft for given role.
            $roleDraft = $this->roleService->loadRoleDraftByRoleId($roleId);
        } catch (NotFoundException $e) {
            // We might want a newly created role, so try to load it by its ID.
            // loadRoleDraft() might throw a NotFoundException (wrong $roleId). If so, let it bubble up.
            $roleDraft = $this->roleService->loadRoleDraft($roleId);
        }

        return $this->roleService->updateRoleDraft($roleDraft, $this->mapToUpdateStruct($createStruct));
    }
}
