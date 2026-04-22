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
use Ibexa\Core\Base\Exceptions\ForbiddenException;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/user/roles/{id}',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Create Role Draft',
        description: 'Creates a new Role draft from an existing Role.',
        tags: [
            'User Role',
        ],
        parameters: [
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
                'description' => 'If set, the new user is returned in XML or JSON format.',
                'content' => [
                    'application/vnd.ibexa.api.RoleDraft+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleDraftWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/draft/PATCH/Role.json.example',
                    ],
                    'application/vnd.ibexa.api.RoleDraft+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleDraft',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/id/POST/RoleDraft.xml.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user is not authorized to create a Role or a Role draft',
            ],
        ],
        requestBody: new Model\RequestBody(
            description: 'No payload required',
            content: new \ArrayObject(),
        ),
    ),
)]
class RoleDraftCreateController extends RoleBaseController
{
    /**
     * Creates a new RoleDraft for an existing Role.
     *
     * @throws \Ibexa\Core\Base\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function createRoleDraft(int $roleId): Values\CreatedRole
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
        }

        return new Values\CreatedRole(['role' => new Values\RestRole($roleDraft)]);
    }
}
