<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use ArrayObject;
use Ibexa\Contracts\Core\Repository\Exceptions\LimitationValidationException;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Core\Base\Exceptions\ForbiddenException;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\CreatedRole;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/user/roles',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        operationId: 'ibexa.rest.create_role',
        summary: 'Create Role draft',
        description: 'Creates a new Role draft. Publish it with PUBLISH /user/roles/{id}/draft.',
        tags: [
            'User Role',
        ],
        parameters: [
            new Model\Parameter(
                name: 'X-CSRF-Token',
                in: 'header',
                required: true,
                description: 'The CSRF Token needed on all unsafe HTTP methods with session.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            description: 'The RoleInput schema encoded in XML or JSON.',
            content: new ArrayObject([
                'application/vnd.ibexa.api.RoleInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/RoleInput.json.example',
                ],
                'application/vnd.ibexa.api.RoleInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/RoleInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/RoleInput.xml.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_CREATED => new Model\Response(
                description: 'If set, the new user is returned in XML or JSON format.',
                content: new ArrayObject([
                    'application/vnd.ibexa.api.Role+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/RoleWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/Role.json.example',
                    ],
                    'application/vnd.ibexa.api.Role+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Role',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/roles/POST/Role.xml.example',
                    ],
                ]),
            ),
            Response::HTTP_BAD_REQUEST => new Model\Response(description: 'Error - the input does not match the input schema definition.'),
            Response::HTTP_UNAUTHORIZED => new Model\Response(description: 'Error - the user is not authorized to create a Role draft.'),
        ],
    ),
)]
class RoleCreateController extends RoleBaseController
{
    public function createRole(Request $request): CreatedRole
    {
        try {
            $roleDraft = $this->roleService->createRole(
                $this->inputDispatcher->parse(
                    new Message(
                        [
                            'Content-Type' => $request->headers->get('Content-Type'),
                        ],
                        $request->getContent()
                    )
                )
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

        return new CreatedRole(['role' => new Values\RestRole($roleDraft)]);
    }
}
