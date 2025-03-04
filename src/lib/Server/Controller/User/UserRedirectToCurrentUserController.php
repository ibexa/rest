<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller\User;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

#[Get(
    uriTemplate: '/user/current',
    name: 'Load current User',
    openapi: new Model\Operation(
        summary: 'Loads the current user.',
        tags: [
            'User Current',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the User is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'description' => 'OK - the User with the given ID.',
                'content' => [
                    'application/vnd.ibexa.api.User+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/GET/User.xml.example',
                    ],
                    'application/vnd.ibexa.api.User+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/UserListWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/users/user_id/GET/User.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - the user has no permission to read Users. For example, Anonymous user can\'t load oneself.',
            ],
        ],
    ),
)]
final class UserRedirectToCurrentUserController extends UserBaseController
{
    /**
     * @see \Symfony\Component\Security\Http\Controller\UserValueResolver
     */
    public function redirectToCurrentUser(?UserInterface $user): Values\TemporaryRedirect
    {
        if ($user === null) {
            throw new UnauthorizedHttpException('', 'Not logged in.');
        }

        $userReference = $this->permissionResolver->getCurrentUserReference();

        return new Values\TemporaryRedirect(
            $this->router->generate('ibexa.rest.load_user', ['userId' => $userReference->getUserId()])
        );
    }
}
