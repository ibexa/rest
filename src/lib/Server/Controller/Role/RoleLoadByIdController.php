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

#[Get(
    uriTemplate: '/user/roles/{id}',
    name: 'Load Role',
    openapi: new Model\Operation(
        summary: 'Loads a Role for the given ID.',
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
                'description' => 'OK - Role for the given ID.',
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
                'description' => 'Error - the Role does not exist.',
            ],
        ],
    ),
)]
class RoleLoadByIdController extends RoleBaseController
{
    /**
     * Loads role.
     *
     * @param $roleId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Role
     */
    public function loadRole($roleId)
    {
        return $this->roleService->loadRole($roleId);
    }
}
