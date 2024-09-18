<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Role;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\LimitationValidationException;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Core\Base\Exceptions\ForbiddenException;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Values;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/user/roles',
    name: 'Create Role or Role draft',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates a new Role or Role draft.',
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
            Response::HTTP_CREATED => [
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
                'description' => 'Error - the user is not authorized to create a Role or a Role draft.',
            ],
        ],
    ),
)]
class RoleCreateController extends RoleBaseController
{
    /**
     * Create new role.
     *
     * Defaults to publishing the role, but you can create a draft instead by setting the POST parameter publish=false
     *
     * @return \Ibexa\Rest\Server\Values\CreatedRole
     */
    public function createRole(Request $request)
    {
        $publish = (
            !$request->query->has('publish') ||
            ($request->query->has('publish') && $request->query->get('publish') === 'true')
        );

        try {
            $roleDraft = $this->roleService->createRole(
                $this->inputDispatcher->parse(
                    new Message(
                        [
                            'Content-Type' => $request->headers->get('Content-Type'),
                            // @todo Needs refactoring! Temporary solution so parser has access to get parameters
                            '__publish' => $publish,
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

        if ($publish) {
            @trigger_error(
                "Create and publish role in the same operation is deprecated, and will be removed in the future.\n" .
                'Instead, publish the role draft using Role::publishRoleDraft().',
                E_USER_DEPRECATED
            );

            $this->roleService->publishRoleDraft($roleDraft);

            $role = $this->roleService->loadRole($roleDraft->id);

            return new Values\CreatedRole(['role' => new Values\RestRole($role)]);
        }

        return new Values\CreatedRole(['role' => new Values\RestRole($roleDraft)]);
    }
}
