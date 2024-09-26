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
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Core\Base\Exceptions\ForbiddenException;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/user/roles/{id}',
    name: 'Create Role Draft',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Role draft from an existing Role.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the new user is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The RoleInput schema encoded in XML or JSON.',
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
            Response::HTTP_CREATED => [
                'content' => [
                    'application/vnd.ibexa.api.RoleDraft+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleDraft',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/POST/RoleDraft.xml.example',
                    ],
                    'application/vnd.ibexa.api.RoleDraft+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleDraftWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create a Role or a Role draft',
            ],
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject(),
        ),
    ),
)]
class RoleDraftCreateController extends RoleBaseController
{
    /**
     * Creates a new RoleDraft for an existing Role.
     *
     * @since 6.2
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ForbiddenException if the Role already has a Role Draft that will need to be removed first,
     *                                                                  or if the authenticated user is not allowed to create a role
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException if a policy limitation in the $roleCreateStruct is not valid
     *
     * @return \Ibexa\Rest\Server\Values\CreatedRole
     */
    public function createRoleDraft($roleId, Request $request)
    {
        try {
            $roleDraft = $this->roleService->createRoleDraft(
                $this->roleService->loadRole($roleId)
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (UnauthorizedException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (LimitationValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (Exceptions\Parser $e) {
            throw new BadRequestException($e->getMessage());
        }

        return new Values\CreatedRole(['role' => new Values\RestRole($roleDraft)]);
    }
}
